package tn.esprit.fx;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.CategorieService;
import tn.esprit.services.SousCategorieService;
import tn.esprit.utils.ImageUtils;

import java.io.File;
import java.util.List;

public class AjouterSousCategorieController {

    @FXML private TextField nomField;
    @FXML private ComboBox<Categorie> categorieComboBox;
    @FXML private ImageView imagePreview;
    @FXML private Label nomError;
    @FXML private Label categorieError;
    @FXML private Label imageError;

    private SousCategorieService sousCategorieService = new SousCategorieService();
    private final CategorieService categorieService = new CategorieService();
    private String selectedImageFilename;
    private SousCategorie sousCategorieEnCoursEdition;

    @FXML
    public void initialize() {
        List<Categorie> categories = categorieService.getAll();
        categorieComboBox.getItems().setAll(categories);
    }

    public void setSousCategorie(SousCategorie s) {
        this.sousCategorieEnCoursEdition = s;
        nomField.setText(s.getNom());
        categorieComboBox.setValue(s.getCategorie());
        selectedImageFilename = s.getImage();
        if (selectedImageFilename != null && !selectedImageFilename.isBlank()) {
            imagePreview.setImage(ImageUtils.chargerDepuisNom(selectedImageFilename));
        }
    }

    @FXML
    private void enregistrerSousCategorie() {
        // Réinitialiser messages
        nomError.setVisible(false);
        categorieError.setVisible(false);
        imageError.setVisible(false);

        String nom = nomField.getText().trim();
        Categorie selectedCategorie = categorieComboBox.getSelectionModel().getSelectedItem();

        if (nom.isEmpty()) {
            nomError.setText("⚠ Le nom est requis.");
            nomError.setVisible(true);
            return;
        }

        if (nom.length() < 3 || nom.matches("\\d+")) {
            nomError.setText("⚠ Le nom doit contenir au moins 3 lettres.");
            nomError.setVisible(true);
            return;
        }

        if (selectedCategorie == null) {
            categorieError.setText("⚠ Veuillez sélectionner une catégorie.");
            categorieError.setVisible(true);
            return;
        }

        if (selectedImageFilename == null || selectedImageFilename.isBlank()) {
            imageError.setText("⚠ Veuillez sélectionner une image.");
            imageError.setVisible(true);
            return;
        }

        if (sousCategorieEnCoursEdition == null) {
            SousCategorie nouvelle = new SousCategorie(nom, selectedImageFilename, selectedCategorie);
            sousCategorieService.ajouter(nouvelle);
        } else {
            sousCategorieEnCoursEdition.setNom(nom);
            sousCategorieEnCoursEdition.setCategorie(selectedCategorie);
            sousCategorieEnCoursEdition.setImage(selectedImageFilename);
            sousCategorieService.modifier(sousCategorieEnCoursEdition);
        }

        new Thread(() -> {
            try {
                Thread.sleep(1000);
                Platform.runLater(() -> ((Stage) nomField.getScene().getWindow()).close());
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
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

    @FXML
    public void fermerFenetre() {
        Stage stage = (Stage) nomField.getScene().getWindow();
        stage.close();
    }
}