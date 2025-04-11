package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;

import java.io.File;
import java.net.URL;
import java.util.ResourceBundle;

public class FrontViewController implements Initializable {
    @FXML
    private void goToAccueil() {
        // Charger Accueil.fxml
    }

    @FXML
    private void goToProduits() {
        // Charger Produits.fxml
    }

    @FXML
    private void goToPanier() {
        // Charger Panier.fxml
    }

    @FXML
    private void goToCommandes() {
        // Charger Commandes.fxml
    }

    @FXML
    private void goToOffres() {
        // Charger Offres.fxml
    }

    @FXML
    private void goToBlog() {
        // Charger Blog.fxml
    }

    @FXML
    private void logout() {
        // Implémenter la déconnexion (retour à login par ex.)
    }
    @FXML private ImageView carottesImage;
    @FXML private ImageView laitueImage;
    @FXML private ImageView tomatesImage;
    @FXML private ImageView pommesImage;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        carottesImage.setImage(new Image(new File("photos/carottes.jpg").toURI().toString()));
        laitueImage.setImage(new Image(new File("photos/laitue.jpg").toURI().toString()));
        tomatesImage.setImage(new Image(new File("photos/tomates.jpg").toURI().toString()));
        pommesImage.setImage(new Image(new File("photos/pommes.jpg").toURI().toString()));
    }


}
