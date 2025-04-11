package tn.esprit.fx;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.Parent;
import javafx.stage.Stage;

public class FrontApp extends Application {

    @Override
    public void start(Stage stage) throws Exception {
        // Charge la vue depuis le fichier FXML
        Parent root = FXMLLoader.load(getClass().getResource("/views/FrontView.fxml"));

        // Crée la scène et l'attache au stage
        Scene scene = new Scene(root);
        stage.setTitle("AGRIMANAGER - Front Office");
        stage.setScene(scene);

        // 👉 Adapter la taille automatiquement au contenu
        stage.sizeToScene();

        // Affiche la fenêtre
        stage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}
