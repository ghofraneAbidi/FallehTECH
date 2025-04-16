package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.Pane;
import tn.esprit.entities.Produit;
import tn.esprit.services.ProduitService;
import javafx.scene.control.Dialog;
import javafx.scene.control.DialogPane;
import javafx.scene.control.ButtonType;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;
import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class AgriculteurController implements Initializable {

    @FXML
    private FlowPane productContainer;



    private final ProduitService produitService = new ProduitService();

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        // ✅ Assure que le CSS est chargé


        List<Produit> produits = produitService.getAll();

        for (Produit produit : produits) {
            try {
                URL fxmlUrl = getClass().getResource("/views/ProduitCard.fxml");
                if (fxmlUrl == null) {
                    System.err.println("❌ Fichier FXML introuvable : /views/ProduitCard.fxml");
                    continue;
                }

                FXMLLoader loader = new FXMLLoader(fxmlUrl);
                Pane produitCard = loader.load();

                ProduitCardController controller = loader.getController();
                controller.setProduit(produit);

                productContainer.getChildren().add(produitCard);

            } catch (IOException e) {
                System.err.println("❌ Erreur lors du chargement d’une carte produit : " + e.getMessage());
                e.printStackTrace();
            }
        }
    }
    @FXML
    private void ajouterProduitPopup() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/AjouterProduit.fxml"));
            Parent root = loader.load();

            AjouterProduitController controller = loader.getController();

            Stage stage = new Stage();
            stage.setTitle("Ajouter un Produit");
            stage.setScene(new Scene(root));
            stage.setResizable(false);

            // 🔄 Lors de la fermeture, on rafraîchit les cartes
            stage.setOnHidden(event -> rafraichirProduits());

            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public void rafraichirProduits() {
        productContainer.getChildren().clear();
        List<Produit> produits = produitService.getAll();
        for (Produit produit : produits) {
            try {
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/ProduitCard.fxml"));
                Pane produitCard = loader.load();
                ProduitCardController controller = loader.getController();
                controller.setProduit(produit);
                productContainer.getChildren().add(produitCard);
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }



}
