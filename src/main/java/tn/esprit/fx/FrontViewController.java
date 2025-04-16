package tn.esprit.fx;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.Node;
import javafx.scene.control.*;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.scene.shape.Circle;
import javafx.stage.Stage;
import tn.esprit.services.CategorieService;
import tn.esprit.utils.ImageUtils;

import java.io.IOException;
import java.net.URL;
import java.util.ResourceBundle;

public class FrontViewController implements Initializable {

    @FXML private ImageView logoImage;
    @FXML private ImageView menuAvatar;
    @FXML private Label profileNameLabel;
    @FXML private Label activePageLabel;
    @FXML private StackPane contentPane;
    @FXML private BorderPane mainPane;

    @FXML private Button accueilButton;
    @FXML private Button produitsButton;
    @FXML private Button panierButton;
    @FXML private Button commandesButton;
    @FXML private Button offresButton;
    @FXML private Button blogButton;
    @FXML private Button btnFavoris;
    @FXML private Button btnPanier;
    @FXML private Button prodagriculteur;
    @FXML private VBox categorieTreeContainer;
    @FXML private TreeView<String> categorieTree;

    private Button currentActiveButton;
    private final CategorieService categorieService = new CategorieService();

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        logoImage.setImage(ImageUtils.chargerDepuisNom("logo.png"));
        menuAvatar.setImage(ImageUtils.chargerDepuisNom("avatar.jpg"));
        setupClip(menuAvatar);
        profileNameLabel.setText("Sarah"); // TODO: rendre dynamique si besoin

        hideCategorieTree();
        goToAccueil(); // affiche la page d'accueil par défaut
    }

    private void setupClip(ImageView imageView) {
        Circle clip = new Circle();
        clip.radiusProperty().bind(imageView.fitWidthProperty().divide(2));
        clip.centerXProperty().bind(imageView.fitWidthProperty().divide(2));
        clip.centerYProperty().bind(imageView.fitHeightProperty().divide(2));
        imageView.setClip(clip);
    }

    private void hideCategorieTree() {
        categorieTreeContainer.setVisible(false);
        categorieTreeContainer.setManaged(false);
    }

    private void setActiveButton(Button newActiveButton) {
        if (currentActiveButton != null) {
            currentActiveButton.getStyleClass().remove("active-button");
        }
        if (newActiveButton != null && !newActiveButton.getStyleClass().contains("active-button")) {
            newActiveButton.getStyleClass().add("active-button");
        }
        currentActiveButton = newActiveButton;
    }

    private void loadView(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/" + fxmlPath));
            Parent view = loader.load();
            contentPane.getChildren().setAll(view); // injecte la vue dans la zone centrale
        } catch (IOException e) {
            e.printStackTrace();
            System.out.println("❌ Erreur chargement vue : " + fxmlPath);
        }
    }

    // ============ Navigation ============

    @FXML private void goToAccueil() {
        setActiveButton(accueilButton);
        activePageLabel.setText("Accueil");
        hideCategorieTree();
        loadView("AccueilView.fxml");
    }

    @FXML private void goToProduits() {
        setActiveButton(produitsButton);
        activePageLabel.setText("Produits");
        hideCategorieTree();
        loadView("ProduitFrontView.fxml");
    }

    @FXML private void goToPanier() {
        setActiveButton(panierButton);
        activePageLabel.setText("🛒 Mon Panier");
        hideCategorieTree();
        loadView("panier.fxml");
    }

    @FXML private void goToCommandes() {
        setActiveButton(commandesButton);
        activePageLabel.setText("Commandes");
        hideCategorieTree();
        loadView("CommandesView.fxml");
    }

    @FXML private void goToOffres() {
        setActiveButton(offresButton);
        activePageLabel.setText("Offres de travail");
        hideCategorieTree();
        loadView("OffresView.fxml");
    }

    @FXML private void goToBlog() {
        setActiveButton(blogButton);
        activePageLabel.setText("Blog");
        hideCategorieTree();
        loadView("BlogView.fxml");
    }

    @FXML private void goToFavoris() {
        setActiveButton(btnFavoris);
        activePageLabel.setText("Favoris ❤️");
        hideCategorieTree();
        loadView("FavorisView.fxml");
    }

    @FXML private void openProfile() {
        activePageLabel.setText("👤 Mon Profil");
        loadView("ProfileView.fxml");
    }

    @FXML private void logout() {
        System.out.println("🔓 Déconnexion...");
        // TODO: Implémenter logout + redirection vers LoginView.fxml
    }

    @FXML private void goToProdagru(ActionEvent event) {
        setActiveButton(prodagriculteur);
        activePageLabel.setText("Vos Produits");
        hideCategorieTree();
        loadView("Agriculteur.fxml"); // ✅ NE PAS changer de scène !
    }
}
