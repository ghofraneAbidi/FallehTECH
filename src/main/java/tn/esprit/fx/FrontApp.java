package tn.esprit.fx;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.Parent;
import javafx.stage.Stage;
import javafx.scene.Node;

import java.util.prefs.Preferences;

public class FrontApp extends Application {

    Preferences prefs = Preferences.userNodeForPackage(getClass());

    @Override
    public void start(Stage stage) throws Exception {
        Parent root = FXMLLoader.load(getClass().getResource("/views/FrontView.fxml"));
        Scene scene = new Scene(root);
        stage.setTitle("AGRIMANAGER - Front Office");
        stage.setScene(scene);
        stage.sizeToScene();
        stage.show();
    }

    // ✅ Now declared outside the start method
    public void restoreWidgetPosition(Node node, String widgetKey, double defaultX, double defaultY) {
        double x = prefs.getDouble(widgetKey + "X", defaultX);
        double y = prefs.getDouble(widgetKey + "Y", defaultY);
        node.setLayoutX(x);
        node.setLayoutY(y);
    }

    public static void main(String[] args) {
        launch(args);
    }
}
