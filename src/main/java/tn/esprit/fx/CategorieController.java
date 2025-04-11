package tn.esprit.fx;

import javafx.beans.property.ReadOnlyObjectWrapper;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.stage.FileChooser;
import tn.esprit.entities.Categorie;
import tn.esprit.services.CategorieService;

import com.github.sarxos.webcam.Webcam;
import com.github.sarxos.webcam.WebcamResolution;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class CategorieController implements Initializable {

    @FXML private TableView<Categorie> tableView;
    @FXML private TableColumn<Categorie, Long> idCol;
    @FXML private TableColumn<Categorie, String> nomCol;
    @FXML private TableColumn<Categorie, Void> actionCol;
    @FXML private TableColumn<Categorie, String> imageCol;

    @FXML private TextField nomField;
    @FXML private ImageView imagePreview;

    private String selectedImagePath;

    private final CategorieService service = new CategorieService();
    private Categorie categorieEnCoursEdition = null;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));

        imageCol.setCellFactory(param -> new TableCell<>() {
            private final ImageView imageView = new ImageView();
            {
                imageView.setFitWidth(80);
                imageView.setFitHeight(60);
                imageView.setPreserveRatio(true);
            }

            @Override
            protected void updateItem(String path, boolean empty) {
                super.updateItem(path, empty);
                if (empty || path == null || path.isBlank()) {
                    setGraphic(null);
                } else {
                    try {
                        imageView.setImage(new Image(path, 80, 60, true, true));
                        setGraphic(imageView);
                    } catch (Exception e) {
                        System.err.println("Erreur image catégorie: " + path);
                        setGraphic(null);
                    }
                }
            }
        });
        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));

        setupActionColumn();
        afficherCategories();
    }

    public void afficherCategories() {
        List<Categorie> list = service.getAll();
        tableView.getItems().setAll(list);
    }

    @FXML
    public void ajouterCategorie() {
        String nom = nomField.getText().trim();
        if (!nom.isEmpty()) {
            if (categorieEnCoursEdition == null) {
                Categorie c = new Categorie();
                c.setNom(nom);
                c.setImage(selectedImagePath);
                service.ajouter(c);
            } else {
                categorieEnCoursEdition.setNom(nom);
                categorieEnCoursEdition.setImage(selectedImagePath);
                service.modifier(categorieEnCoursEdition);
                categorieEnCoursEdition = null;
            }
            clearFields();
            afficherCategories();
        }
    }

    @FXML
    public void choisirImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");
        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg", "*.gif")
        );
        File file = fileChooser.showOpenDialog(null);
        if (file != null) {
            selectedImagePath = file.toURI().toString();
            imagePreview.setImage(new Image(selectedImagePath));
        }
    }

    @FXML
    public void prendrePhoto() {
        try {
            Webcam webcam = Webcam.getDefault();
            webcam.setViewSize(WebcamResolution.VGA.getSize());
            webcam.open();

            BufferedImage image = webcam.getImage();
            File output = new File("captured/" + System.currentTimeMillis() + ".png");
            output.getParentFile().mkdirs();
            ImageIO.write(image, "PNG", output);

            selectedImagePath = output.toURI().toString();
            imagePreview.setImage(new Image(selectedImagePath));
            webcam.close();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void clearFields() {
        nomField.clear();
        imagePreview.setImage(null);
        selectedImagePath = null;
    }

    private void setupActionColumn() {
        actionCol.setCellFactory(param -> new TableCell<>() {
            private final Button btnEdit = new Button("Modifier");
            private final Button btnDelete = new Button("Supprimer");
            private final HBox hbox = new HBox(5, btnEdit, btnDelete);

            {
                btnEdit.setOnAction(event -> {
                    Categorie selected = getTableView().getItems().get(getIndex());
                    nomField.setText(selected.getNom());
                    selectedImagePath = selected.getImage();
                    if (selectedImagePath != null && !selectedImagePath.isBlank()) {
                        imagePreview.setImage(new Image(selectedImagePath));
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
