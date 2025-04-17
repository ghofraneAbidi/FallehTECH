package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import tn.esprit.utils.SessionUtilisateur;
import java.io.IOException;
import javafx.event.ActionEvent;

public class LandingController {
    @FXML
    private Button btnBackOffice;

    @FXML
    private VBox roleBox;

    @FXML
    private Button btnFrontOffice;

    @FXML
    private void toggleRoleSelection() {
        boolean currentlyVisible = roleBox.isVisible();
        roleBox.setVisible(!currentlyVisible);
        roleBox.setManaged(!currentlyVisible);
    }

    @FXML
    private void goToFrontOfficeClient() {
        SessionUtilisateur.setRole("Client");
        loadScene("/views/FrontView.fxml"); // ⚠️ Le rôle sera utilisé dans applyRoleRestrictions()
    }


    @FXML
    public void goToFrontOfficeAgriculteur(ActionEvent event) {
        SessionUtilisateur.setRole("Agriculteur");
        loadScene("/views/FrontView.fxml");
    }



    @FXML
    private void goToBackOffice() {
        loadScene("/views/main_layout.fxml"); // Ton dashboard (admin)
    }

    private void loadScene(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();

            Scene newScene = new Scene(root);

            Stage currentStage = (Stage) btnBackOffice.getScene().getWindow(); // ou n'importe quel autre composant pour getScene()
            currentStage.setScene(newScene);

            // ✅ Définir les dimensions fixes pour tous les espaces
            currentStage.setWidth(1200);
            currentStage.setHeight(800);
            currentStage.setMinWidth(1200);
            currentStage.setMinHeight(800);
            currentStage.centerOnScreen(); // facultatif mais propre

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

}
