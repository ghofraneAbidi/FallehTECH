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
import javafx.util.StringConverter;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.CategorieService;
import tn.esprit.services.SousCategorieService;
import tn.esprit.services.ProduitService;

import java.io.File;
import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class SousCategorieController implements Initializable {

    @FXML private ComboBox<Categorie> categorieComboBox;
    @FXML private TextField nomField;
    @FXML private TableView<SousCategorie> tableView;
    @FXML private TableColumn<SousCategorie, Long> idCol;
    @FXML private TableColumn<SousCategorie, String> nomCol;
    @FXML private TableColumn<SousCategorie, String> categorieCol;
    @FXML private TableColumn<SousCategorie, String> imageCol;
    @FXML private TableColumn<SousCategorie, Void> actionCol;
    @FXML private ImageView imagePreview;

    private final CategorieService categorieService = new CategorieService();
    private final SousCategorieService sousCategorieService = new SousCategorieService();
    private final ProduitService produitService = new ProduitService();

    private SousCategorie sousCategorieEnCoursEdition = null;
    private String selectedImagePath;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));

        categorieCol.setCellValueFactory(cellData -> {
            Categorie cat = cellData.getValue().getCategorie();
            return new ReadOnlyObjectWrapper<>(cat != null ? cat.getNom() : "Aucune");
        });

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
                        Image img = new Image(path, 80, 60, true, true);
                        imageView.setImage(img);
                        setGraphic(imageView);
                    } catch (Exception e) {
                        setGraphic(null);
                    }
                }
            }
        });
        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));

        setupActionColumn();
        chargerCategories();
        afficherSousCategories();
    }

    private void chargerCategories() {
        List<Categorie> categories = categorieService.getAll();
        categorieComboBox.getItems().setAll(categories);

        categorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(Categorie categorie) { return categorie != null ? categorie.getNom() : ""; }
            @Override public Categorie fromString(String s) { return null; }
        });
    }

    public void afficherSousCategories() {
        List<SousCategorie> list = sousCategorieService.getAll();
        tableView.getItems().setAll(list);
    }

    @FXML
    public void ajouterSousCategorie() {
        String nom = nomField.getText().trim();
        Categorie selectedCategorie = categorieComboBox.getSelectionModel().getSelectedItem();

        if (!nom.isEmpty() && selectedCategorie != null) {
            if (sousCategorieEnCoursEdition == null) {
                SousCategorie sc = new SousCategorie();
                sc.setNom(nom);
                sc.setCategorie(selectedCategorie);
                sc.setImage(selectedImagePath);
                sousCategorieService.ajouter(sc);
            } else {
                sousCategorieEnCoursEdition.setNom(nom);
                sousCategorieEnCoursEdition.setCategorie(selectedCategorie);
                sousCategorieEnCoursEdition.setImage(selectedImagePath);
                sousCategorieService.modifier(sousCategorieEnCoursEdition);
                sousCategorieEnCoursEdition = null;
            }

            nomField.clear();
            categorieComboBox.getSelectionModel().clearSelection();
            imagePreview.setImage(null);
            selectedImagePath = null;
            afficherSousCategories();
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
        // TODO: intégrer webcam ici, pour l’instant juste afficher une alerte
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Caméra");
        alert.setHeaderText("Fonction 'Prendre Photo'");
        alert.setContentText("Capture via webcam à implémenter ici.");
        alert.showAndWait();
    }


    private void setupActionColumn() {
        actionCol.setCellFactory(param -> new TableCell<>() {
            private final Button btnEdit = new Button("Modifier");
            private final Button btnDelete = new Button("Supprimer");
            private final HBox hbox = new HBox(5, btnEdit, btnDelete);

            {
                btnEdit.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());
                    nomField.setText(selected.getNom());
                    categorieComboBox.setValue(selected.getCategorie());
                    selectedImagePath = selected.getImage();
                    if (selectedImagePath != null && !selectedImagePath.isBlank()) {
                        imagePreview.setImage(new Image(selectedImagePath));
                    }
                    sousCategorieEnCoursEdition = selected;
                });

                btnDelete.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());

                    if (produitService.existsBySousCategorie(selected.getId())) {
                        Alert alert = new Alert(Alert.AlertType.WARNING,
                                "Impossible de supprimer cette sous-catégorie car elle est liée à un ou plusieurs produits.",
                                ButtonType.OK);
                        alert.setHeaderText("Suppression bloquée");
                        alert.showAndWait();
                        return;
                    }

                    Alert confirm = new Alert(Alert.AlertType.CONFIRMATION,
                            "Êtes-vous sûr de vouloir supprimer cette sous-catégorie ?",
                            ButtonType.YES, ButtonType.NO);
                    confirm.setHeaderText("Confirmation");
                    confirm.showAndWait().ifPresent(response -> {
                        if (response == ButtonType.YES) {
                            sousCategorieService.supprimer(selected);
                            afficherSousCategories();
                        }
                    });
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