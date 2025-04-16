package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;
import tn.esprit.entities.Produit;
import tn.esprit.services.ProduitService;
import javafx.geometry.Insets;
import tn.esprit.utils.ImageUtils;

import java.io.IOException;
import java.util.List;

public class AgriculteurController {

    @FXML private FlowPane produitsContainer;
    @FXML private TextField searchField;
    @FXML private ComboBox<String> categorieFilter;
    @FXML private ComboBox<String> sousCategorieFilter;

    private final ProduitService produitService = new ProduitService();

    @FXML
    public void initialize() {
        chargerProduits();
        setupRecherche();
    }

    private void chargerProduits() {
        produitsContainer.getChildren().clear();
        List<Produit> produits = produitService.getAll();
        for (Produit produit : produits) {
            produitsContainer.getChildren().add(creerCarteProduit(produit));
        }
    }

    private VBox creerCarteProduit(Produit produit) {
        VBox card = new VBox(10);
        card.setPadding(new Insets(10));
        card.setPrefWidth(200);
        card.setStyle("-fx-border-color: #ddd; -fx-background-color: white; -fx-border-radius: 8; -fx-background-radius: 8; -fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.1), 10, 0, 0, 4);");

        // === Image Produit ===
        ImageView img = new ImageView();
        if (produit.getImage() != null && !produit.getImage().isBlank()) {
            try {
                img.setImage(ImageUtils.chargerDepuisNom(produit.getImage())); // 💡 on utilise ta méthode
            } catch (Exception e) {
                System.out.println("Erreur chargement image : " + e.getMessage());
            }
        } else {
            // fallback ou image par défaut si tu veux
            img.setImage(new Image("/images/default-product.png"));
        }
        img.setFitWidth(150);
        img.setFitHeight(100);
        img.setPreserveRatio(true);

        Label nom = new Label(produit.getNom());
        nom.setStyle("-fx-font-weight: bold; -fx-font-size: 16;");

        Label prix = new Label(produit.getPrix() + " DT");

        Label stock = new Label("Stock: " + produit.getStock());
        if (produit.getStock() < 10) {
            stock.setStyle("-fx-text-fill: red;");
        } else {
            stock.setStyle("-fx-text-fill: green;");
        }

        Button modifierBtn = new Button("Modifier");
        modifierBtn.setStyle("-fx-background-color: #FFA726; -fx-text-fill: white;");
        modifierBtn.setOnAction(e -> modifierProduitPopup(produit));

        card.getChildren().addAll(img, nom, prix, stock, modifierBtn);
        return card;
    }


    @FXML
    private void ajouterProduitPopup() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/AjouterProduit.fxml"));
            VBox popup = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Ajouter Produit");
            stage.setScene(new javafx.scene.Scene(popup));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void modifierProduitPopup(Produit produit) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/AjouterProduit.fxml"));
            VBox popup = loader.load();

            // Injecter le produit dans le contrôleur cible
            AjouterProduitController controller = loader.getController();
            controller.setProduit(produit);

            Stage stage = new Stage();
            stage.setTitle("Modifier Produit");
            stage.setScene(new javafx.scene.Scene(popup));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void setupRecherche() {
        searchField.textProperty().addListener((obs, oldVal, newVal) -> {
            produitsContainer.getChildren().clear();
            List<Produit> produits = produitService.getAll().stream()
                    .filter(p -> p.getNom().toLowerCase().contains(newVal.toLowerCase()))
                    .toList();
            for (Produit p : produits) {
                produitsContainer.getChildren().add(creerCarteProduit(p));
            }
        });
    }
}
