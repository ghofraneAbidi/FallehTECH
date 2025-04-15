package tn.esprit.fx;

import javafx.beans.property.ReadOnlyObjectWrapper;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.util.StringConverter;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.CategorieService;
import tn.esprit.services.ProduitService;
import tn.esprit.services.SousCategorieService;
import tn.esprit.utils.ImageUtils;

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
    @FXML private Label notifLabel;

    private final CategorieService categorieService = new CategorieService();
    private final SousCategorieService sousCategorieService = new SousCategorieService();
    private final ProduitService produitService = new ProduitService();

    private SousCategorie sousCategorieEnCoursEdition = null;
    private String selectedImageName;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));

        categorieCol.setCellValueFactory(cellData -> {
            Categorie cat = cellData.getValue().getCategorie();
            return new ReadOnlyObjectWrapper<>(cat != null ? cat.getNom() : "Aucune");
        });

        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));
        imageCol.setCellFactory(param -> new TableCell<>() {
            private final ImageView imageView = new ImageView();
            {
                imageView.setFitWidth(80);
                imageView.setFitHeight(60);
                imageView.setPreserveRatio(true);
            }
            @Override
            protected void updateItem(String imageName, boolean empty) {
                super.updateItem(imageName, empty);
                if (empty || imageName == null || imageName.isBlank()) {
                    setGraphic(null);
                } else {
                    imageView.setImage(ImageUtils.chargerDepuisNom(imageName));
                    setGraphic(imageView);
                }
            }
        });

        setupActionColumn();
        chargerCategories();
        afficherSousCategories();
        ajusterColonnes();
    }

    private void chargerCategories() {
        List<Categorie> categories = categorieService.getAll();
        categorieComboBox.getItems().setAll(categories);
        categorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(Categorie c) { return c != null ? c.getNom() : ""; }
            @Override public Categorie fromString(String s) { return null; }
        });
    }

    public void afficherSousCategories() {
        tableView.getItems().setAll(sousCategorieService.getAll());
    }

    @FXML
    public void ajouterSousCategorie() {
        String nom = nomField.getText().trim();
        Categorie selectedCategorie = categorieComboBox.getSelectionModel().getSelectedItem();

        // ⚠️ Contrôle du champ "nom"
        if (nom.isEmpty()) {
            showNotification("⚠ Le nom est requis.", true);
            nomField.requestFocus();
            return;
        }

        if (nom.length() < 3 || nom.matches("\\d+")) {
            showNotification("⚠ Le nom doit contenir au moins 3 lettres et ne peut pas être uniquement des chiffres.", true);
            nomField.requestFocus();
            return;
        }

        // ⚠️ Contrôle de la catégorie sélectionnée
        if (selectedCategorie == null) {
            showNotification("⚠ Veuillez sélectionner une catégorie.", true);
            categorieComboBox.requestFocus();
            return;
        }

        // ⚠️ Contrôle de l’image
        if (selectedImageName == null || selectedImageName.isBlank()) {
            showNotification("⚠ Veuillez choisir une image ou prendre une photo.", true);
            return;
        }

        // ✅ Création ou modification
        if (sousCategorieEnCoursEdition == null) {
            SousCategorie sc = new SousCategorie(nom, selectedImageName, selectedCategorie);
            sousCategorieService.ajouter(sc);
            showNotification("✅ Sous-catégorie ajoutée avec succès !", false);
        } else {
            sousCategorieEnCoursEdition.setNom(nom);
            sousCategorieEnCoursEdition.setCategorie(selectedCategorie);
            sousCategorieEnCoursEdition.setImage(selectedImageName);
            sousCategorieService.modifier(sousCategorieEnCoursEdition);
            sousCategorieEnCoursEdition = null;
            showNotification("✏ Sous-catégorie modifiée avec succès !", false);
        }

        clearFields();
        afficherSousCategories();
    }


    @FXML
    public void choisirImage() {
        File imageFile = ImageUtils.ouvrirEtCopierImage();
        if (imageFile != null) {
            selectedImageName = imageFile.getName();
            imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageName));
        }
    }

    @FXML
    public void prendrePhoto() {
        File photo = ImageUtils.prendrePhotoDepuisWebcam();
        if (photo != null) {
            selectedImageName = photo.getName();
            imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageName));
        }
    }

    private void setupActionColumn() {
        actionCol.setCellFactory(param -> new TableCell<>() {
            private final Button btnEdit = new Button("Modifier");
            private final Button btnDelete = new Button("Supprimer");
            private final HBox hbox = new HBox(5, btnEdit, btnDelete);

            {
                btnEdit.getStyleClass().add("action-button");
                btnDelete.getStyleClass().add("action-button");

                btnEdit.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());
                    nomField.setText(selected.getNom());
                    categorieComboBox.setValue(selected.getCategorie());
                    selectedImageName = selected.getImage();
                    imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageName));
                    sousCategorieEnCoursEdition = selected;
                });

                btnDelete.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());
                    if (produitService.existsBySousCategorie(selected.getId())) {
                        Alert alert = new Alert(Alert.AlertType.WARNING,
                                "Impossible de supprimer cette sous-catégorie car elle est liée à des produits.",
                                ButtonType.OK);
                        alert.setHeaderText("Suppression bloquée");
                        alert.showAndWait();
                        return;
                    }

                    Alert confirm = new Alert(Alert.AlertType.CONFIRMATION,
                            "Supprimer cette sous-catégorie ?", ButtonType.YES, ButtonType.NO);
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

    private void showNotification(String message, boolean isWarning) {
        notifLabel.setText(message);
        notifLabel.getStyleClass().removeAll("notif-label", "warning");
        notifLabel.getStyleClass().add("notif-label");
        if (isWarning) notifLabel.getStyleClass().add("warning");
        notifLabel.setVisible(true);

        new Thread(() -> {
            try {
                Thread.sleep(3000);
                javafx.application.Platform.runLater(() -> notifLabel.setVisible(false));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }



    private void clearFields() {
        nomField.clear();
        categorieComboBox.getSelectionModel().clearSelection();
        imagePreview.setImage(null);
        selectedImageName = null;
        sousCategorieEnCoursEdition = null;
    }

    private void ajusterColonnes() {
        tableView.setColumnResizePolicy(TableView.CONSTRAINED_RESIZE_POLICY);
        idCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.10);
        nomCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.25);
        categorieCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.25);
        imageCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.20);
        actionCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.20);
    }
    private void showAlert(String message) {
        Alert alert = new Alert(Alert.AlertType.WARNING);
        alert.setTitle("Validation");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showInfo(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

}