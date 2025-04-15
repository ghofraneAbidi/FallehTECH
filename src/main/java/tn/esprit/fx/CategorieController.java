package tn.esprit.fx;

import javafx.beans.property.ReadOnlyObjectWrapper;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import tn.esprit.entities.Categorie;
import tn.esprit.services.CategorieService;
import tn.esprit.utils.ImageUtils;

import java.io.File;
import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class CategorieController implements Initializable {

    @FXML private TableView<Categorie> tableView;
    @FXML private TableColumn<Categorie, Long> idCol;
    @FXML private TableColumn<Categorie, String> nomCol;
    @FXML private TableColumn<Categorie, String> imageCol;
    @FXML private TableColumn<Categorie, Void> actionCol;

    @FXML private TextField nomField;
    @FXML private ImageView imagePreview;

    private final CategorieService service = new CategorieService();
    private Categorie categorieEnCoursEdition = null;
    private String selectedImageFilename;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));
        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));

        imageCol.setCellFactory(param -> new TableCell<>() {
            private final ImageView imageView = new ImageView();
            {
                imageView.setFitWidth(80);
                imageView.setFitHeight(60);
                imageView.setPreserveRatio(true);
            }

            @Override
            protected void updateItem(String filename, boolean empty) {
                super.updateItem(filename, empty);
                if (empty || filename == null || filename.isBlank()) {
                    setGraphic(null);
                } else {
                    imageView.setImage(ImageUtils.chargerDepuisNom(filename));
                    setGraphic(imageView);
                }
            }
        });

        setupActionColumn();
        afficherCategories();
    }

    public void afficherCategories() {
        tableView.getItems().setAll(service.getAll());
    }

    @FXML
    public void ajouterCategorie() {
        String nom = nomField.getText().trim();
        if (!nom.isEmpty()) {
            if (categorieEnCoursEdition == null) {
                Categorie c = new Categorie(nom, selectedImageFilename);
                service.ajouter(c);
            } else {
                categorieEnCoursEdition.setNom(nom);
                categorieEnCoursEdition.setImage(selectedImageFilename);
                service.modifier(categorieEnCoursEdition);
                categorieEnCoursEdition = null;
            }
            clearFields();
            afficherCategories();
        }
    }

    @FXML
    public void choisirImage() {
        File file = ImageUtils.ouvrirEtCopierImage();
        if (file != null) {
            selectedImageFilename = file.getName();
            imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageFilename));
        }
    }

    @FXML
    public void prendrePhoto() {
        File file = ImageUtils.prendrePhotoDepuisWebcam();
        if (file != null) {
            selectedImageFilename = file.getName();
            imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageFilename));
        }
    }

    private void clearFields() {
        nomField.clear();
        imagePreview.setImage(null);
        selectedImageFilename = null;
        categorieEnCoursEdition = null;
    }

    private void setupActionColumn() {
        actionCol.setCellFactory(param -> new TableCell<>() {
            private final Button btnEdit = new Button("✏️");
            private final Button btnDelete = new Button("🗑️");
            private final HBox hbox = new HBox(10, btnEdit, btnDelete);

            {
                btnEdit.setOnAction(event -> {
                    Categorie selected = getTableView().getItems().get(getIndex());
                    nomField.setText(selected.getNom());
                    selectedImageFilename = selected.getImage();
                    if (selectedImageFilename != null && !selectedImageFilename.isBlank()) {
                        imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageFilename));
                    }
                    categorieEnCoursEdition = selected;
                });

                btnDelete.setOnAction(event -> {
                    Categorie selected = getTableView().getItems().get(getIndex());
                    service.supprimer(selected);
                    afficherCategories();
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : hbox);
            }
        });

        actionCol.setCellValueFactory(features -> new ReadOnlyObjectWrapper<>(null));
    }
}
