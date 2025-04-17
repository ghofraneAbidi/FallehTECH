package tn.esprit.fx;

import javafx.animation.KeyFrame;
import javafx.animation.KeyValue;
import javafx.animation.Timeline;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.VBox;
import javafx.util.Duration;

import java.io.IOException;

public class MainController {

    @FXML private BorderPane rootLayout;
    @FXML private VBox sidebar;
    @FXML private AnchorPane mainContent;
    @FXML private ImageView profileImage;
    @FXML private Label navTitle;

    @FXML private Button btnProduits, btnSousCategories, btnCategories, toggleSidebarBtn;

    private boolean sidebarVisible = true;
    private Button activeButton = null;

    public void initialize() {
        loadInitialPage();
        loadProfileImage();
        sidebar.setTranslateX(0);
    }

    private void loadInitialPage() {
        goToCategories();
    }

    private void loadProfileImage() {
        try {
            Image img = new Image(getClass().getResource("/icons/icons8_james_bond_32px.png").toExternalForm());
            profileImage.setImage(img);
        } catch (Exception e) {
            System.err.println("\u274C Image de profil introuvable.");
        }
    }

    private void loadPage(String path) {
        try {
            var resource = getClass().getResource(path);
            if (resource == null) {
                System.err.println("\u26A0\uFE0F Fichier FXML introuvable : " + path);
                Label placeholder = new Label("\uD83D\uDCC4 Page en construction : " + path);
                placeholder.setStyle("-fx-font-size: 16px; -fx-text-fill: gray;");
                mainContent.getChildren().setAll(placeholder);
                AnchorPane.setTopAnchor(placeholder, 0.0);
                AnchorPane.setBottomAnchor(placeholder, 0.0);
                AnchorPane.setLeftAnchor(placeholder, 0.0);
                AnchorPane.setRightAnchor(placeholder, 0.0);
                return;
            }

            Parent newPage = FXMLLoader.load(resource);
            mainContent.getChildren().setAll(newPage);
            AnchorPane.setTopAnchor(newPage, 0.0);
            AnchorPane.setBottomAnchor(newPage, 0.0);
            AnchorPane.setLeftAnchor(newPage, 0.0);
            AnchorPane.setRightAnchor(newPage, 0.0);

        } catch (IOException e) {
            System.err.println("\u274C Erreur lors du chargement de : " + path);
            e.printStackTrace();
        }
    }

    @FXML
    private void toggleSidebar() {
        double targetWidth = sidebarVisible ? 0 : 240;
        double targetOpacity = sidebarVisible ? 0 : 1;

        Timeline timeline = new Timeline(
                new KeyFrame(Duration.ZERO,
                        new KeyValue(sidebar.prefWidthProperty(), sidebar.getWidth()),
                        new KeyValue(sidebar.opacityProperty(), sidebar.getOpacity())
                ),
                new KeyFrame(Duration.millis(300),
                        new KeyValue(sidebar.prefWidthProperty(), targetWidth),
                        new KeyValue(sidebar.opacityProperty(), targetOpacity)
                )
        );

        if (sidebarVisible) {
            timeline.setOnFinished(e -> rootLayout.setLeft(null));
        } else {
            rootLayout.setLeft(sidebar);
        }

        timeline.play();
        sidebarVisible = !sidebarVisible;
    }

    private void setActive(Button newActive) {
        if (activeButton != null) {
            activeButton.getStyleClass().remove("active");
        }
        activeButton = newActive;
        if (!activeButton.getStyleClass().contains("active")) {
            activeButton.getStyleClass().add("active");
        }
    }

    private void setNavTitle(String title) {
        navTitle.setText(title);
    }

    @FXML
    private void goToCategories() {
        setNavTitle("\uD83D\uDCE6 Catégories");
        loadPage("/views/CategorieView.fxml");
        setActive(btnCategories);
    }

    @FXML
    private void goToSousCategories() {
        setNavTitle("\uD83D\uDCC2 Sous-Catégories");
        loadPage("/views/SousCategorieView.fxml");
        setActive(btnSousCategories);
    }

    @FXML
    private void goToProduits() {
        setNavTitle("\uD83D\uDED2 Produits");
        loadPage("/views/ProduitView.fxml");
        setActive(btnProduits);
    }

    public AnchorPane getMainContent() {
        return mainContent;
    }
}
