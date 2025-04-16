package tn.esprit.fx;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.stage.Stage;

public class AgriculteurApp extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
        FXMLLoader fxmlLoader = new FXMLLoader(getClass().getResource("/views/Agriculteur.fxml"));
        Scene scene = new Scene(fxmlLoader.load());

        // ✅ Définir une taille maximum raisonnable
        primaryStage.setTitle("Interface Agriculteur");
        primaryStage.setScene(scene);
        primaryStage.setWidth(850);     // 👈 Largeur max
        primaryStage.setHeight(700);    // 👈 Hauteur max
        primaryStage.setResizable(false); // 👈 Optionnel : désactiver le redimensionnement
        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}
