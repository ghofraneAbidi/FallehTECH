package tn.esprit.fx;

import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Region;
import tn.esprit.entities.Favoris;
import tn.esprit.entities.Produit;
import tn.esprit.services.FavorisService;
import tn.esprit.utils.ImageUtils;

import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class FavorisController implements Initializable {

    @FXML private TableView<Favoris> tableFavoris;
    @FXML private TableColumn<Favoris, ImageView> colImage;
    @FXML private TableColumn<Favoris, String> colNom;
    @FXML private TableColumn<Favoris, String> colPrix;
    @FXML private TableColumn<Favoris, Integer> colStock;
    @FXML private TableColumn<Favoris, Void> colActions;

    private final FavorisService favorisService = new FavorisService();

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        tableFavoris.setColumnResizePolicy(TableView.CONSTRAINED_RESIZE_POLICY);
        colActions.setMaxWidth(120); // Fixe la largeur pour éviter le débordement

        loadFavoris();
    }

    private void loadFavoris() {
        tableFavoris.getItems().clear();
        List<Favoris> favorisList = favorisService.getFavorisParUser(1);

        colImage.setCellValueFactory(data -> {
            Produit produit = data.getValue().getProduit();
            ImageView imageView = new ImageView(ImageUtils.chargerDepuisNom(produit.getImage()));
            imageView.setFitHeight(50);
            imageView.setFitWidth(50);
            imageView.setPreserveRatio(true);
            return new SimpleObjectProperty<>(imageView);
        });

        colNom.setCellValueFactory(data ->
                new SimpleStringProperty(data.getValue().getProduit().getNom()));

        colPrix.setCellValueFactory(data ->
                new SimpleStringProperty(data.getValue().getProduit().getPrix() + " DT"));

        colStock.setCellValueFactory(data ->
                new SimpleObjectProperty<>(data.getValue().getProduit().getStock()));

        colActions.setCellFactory(param -> new TableCell<>() {
            private final Button btnAddCart = new Button();
            private final Button btnDelete = new Button();
            private final HBox actionBox = new HBox(5, btnAddCart, btnDelete);

            {
                actionBox.setAlignment(Pos.CENTER_LEFT);

                try {
                    ImageView cartIcon = new ImageView(getClass().getResource("/icons/cart.png").toExternalForm());
                    ImageView deleteIcon = new ImageView(getClass().getResource("/icons/delete.png").toExternalForm());

                    cartIcon.setFitWidth(16);
                    cartIcon.setFitHeight(16);
                    deleteIcon.setFitWidth(16);
                    deleteIcon.setFitHeight(16);

                    btnAddCart.setGraphic(cartIcon);
                    btnDelete.setGraphic(deleteIcon);

                    btnAddCart.setPrefSize(30, 30);
                    btnDelete.setPrefSize(30, 30);

                    btnAddCart.getStyleClass().add("action-button");
                    btnDelete.getStyleClass().add("action-button");

                } catch (Exception e) {
                    System.out.println("❌ Erreur chargement icônes : " + e.getMessage());
                }

                btnAddCart.setOnAction(event -> {
                    Favoris favoris = getTableView().getItems().get(getIndex());
                    System.out.println("🛒 Ajout au panier : " + favoris.getProduit().getNom());
                });

                btnDelete.setOnAction(event -> {
                    Favoris favoris = getTableView().getItems().get(getIndex());
                    favorisService.supprimer(favoris.getId());
                    getTableView().getItems().remove(favoris);
                    System.out.println("🗑️ Supprimé des favoris : " + favoris.getProduit().getNom());
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : actionBox);
            }
        });

        tableFavoris.getItems().addAll(favorisList);
    }
}
