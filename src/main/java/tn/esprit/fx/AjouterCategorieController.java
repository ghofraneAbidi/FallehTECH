package tn.esprit.fx;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import tn.esprit.entities.Categorie;
import tn.esprit.services.CategorieService;
import tn.esprit.utils.ImageUtils;

import java.io.File;

public class AjouterCategorieController {

    @FXML private TextField nomField;
    @FXML private ImageView imagePreview;
    @FXML private Label notifLabel;

    private final CategorieService categorieService = new CategorieService();
    private String selectedImageFilename;

    @FXML
    private void ajouterCategorie() {
        String nom = nomField.getText().trim();

        // ⚠ Vérification du nom
        if (nom.isEmpty()) {
            showNotification("⚠ Le nom est requis.", true);
            nomField.requestFocus();
            return;
        }

        if (nom.length() < 3 || nom.matches("\\d+")) {
            showNotification("⚠ Le nom doit contenir au moins 3 lettres et ne peut pas être seulement des chiffres.", true);
            nomField.requestFocus();
            return;
        }

        if (selectedImageFilename == null || selectedImageFilename.isBlank()) {
            showNotification("⚠ Veuillez choisir une image ou prendre une photo.", true);
            return;
        }

        // ❌ Vérifier si une catégorie avec le même nom existe déjà
        boolean existe = categorieService.getAll().stream()
                .anyMatch(c -> c.getNom().equalsIgnoreCase(nom));
        if (existe) {
            showNotification("⚠ Une catégorie avec ce nom existe déjà !", true);
            nomField.requestFocus();
            return;
        }

        // ✅ Ajout
        Categorie nouvelle = new Categorie(nom, selectedImageFilename);
        categorieService.ajouter(nouvelle);
        showNotification("✅ Catégorie ajoutée avec succès !", false);

        // ❌ Fermer la fenêtre après 1 seconde
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

    private void showNotification(String message, boolean isWarning) {
        notifLabel.setText(message);
        notifLabel.getStyleClass().removeAll("notif-label", "warning");
        notifLabel.getStyleClass().add("notif-label");
        if (isWarning) notifLabel.getStyleClass().add("warning");

        notifLabel.setVisible(true);

        new Thread(() -> {
            try {
                Thread.sleep(3000);
                Platform.runLater(() -> notifLabel.setVisible(false));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }
}
