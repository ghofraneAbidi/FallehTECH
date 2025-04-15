package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.Node;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.Pane;
import tn.esprit.utils.ImageUtils;

import java.net.URL;
import java.util.ResourceBundle;
import java.util.prefs.Preferences;

public class AccueilController implements Initializable {

    private final Preferences prefs = Preferences.userNodeForPackage(getClass());

    @FXML private ImageView carottesImage;
    @FXML private ImageView laitueImage;
    @FXML private ImageView tomatesImage;
    @FXML private ImageView pommesImage;
    @FXML private ImageView stockImage;

    @FXML private Pane statsPane;
    @FXML private Pane blogPane;
    @FXML private Pane offresPane;
    @FXML private Pane productsPane;


    @Override
    public void initialize(URL url, ResourceBundle rb) {
        loadImages();
        setupDraggableWidgets();
    }

    private void loadImages() {
        carottesImage.setImage(loadImage("carottes.jpg"));
        laitueImage.setImage(loadImage("laitue.jpg"));
        tomatesImage.setImage(loadImage("tomates.jpg"));
        pommesImage.setImage(loadImage("pommes.jpg"));
        stockImage.setImage(loadImage("stock_pie.png")); // même s’il n’est pas uploadé depuis Symfony
    }

    private Image loadImage(String filename) {
        return ImageUtils.chargerDepuisNom(filename);

    }


    private void setupDraggableWidgets() {
        setupWidget(offresPane, "offresPane", 50, 250);
        setupWidget(blogPane, "blogPane", 500, 250);
        setupWidget(statsPane, "statsPane", 275, 450);
        setupWidget(productsPane, "productsPane", 50, 50);
    }

    private void setupWidget(Pane pane, String key, double defaultX, double defaultY) {
        if (pane != null) {
            restoreWidgetPosition(pane, key, defaultX, defaultY);
            makeDraggable(pane, key);
        }
    }

    private void makeDraggable(Node node, String key) {
        final double[] offsetX = new double[1];
        final double[] offsetY = new double[1];

        node.setOnMousePressed(event -> {
            offsetX[0] = event.getSceneX() - node.getLayoutX();
            offsetY[0] = event.getSceneY() - node.getLayoutY();
        });

        node.setOnMouseDragged(event -> {
            node.setLayoutX(event.getSceneX() - offsetX[0]);
            node.setLayoutY(event.getSceneY() - offsetY[0]);
            saveWidgetPosition(node, key);
        });
    }

    private void saveWidgetPosition(Node node, String key) {
        prefs.putDouble(key + "X", node.getLayoutX());
        prefs.putDouble(key + "Y", node.getLayoutY());
    }

    private void restoreWidgetPosition(Node node, String key, double defaultX, double defaultY) {
        double x = prefs.getDouble(key + "X", defaultX);
        double y = prefs.getDouble(key + "Y", defaultY);
        node.setLayoutX(x);
        node.setLayoutY(y);
    }

    @FXML private void goToOffres() {}
    @FXML private void goToBlog() {}
}
