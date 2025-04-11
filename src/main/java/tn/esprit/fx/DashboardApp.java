package tn.esprit.fx;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Tab;
import javafx.scene.control.TabPane;
import javafx.stage.Stage;

public class DashboardApp extends Application {

    @Override
    public void start(Stage stage) throws Exception {
        TabPane tabPane = new TabPane();

        // Onglet Catégories
        FXMLLoader categorieLoader = new FXMLLoader(getClass().getResource("/views/CategorieView.fxml"));
        Parent categorieView = categorieLoader.load();
        Tab categorieTab = new Tab("Catégories", categorieView);
        categorieTab.setClosable(false);

        // Onglet Sous-Catégories
        FXMLLoader sousCategorieLoader = new FXMLLoader(getClass().getResource("/views/SousCategorieView.fxml"));
        Parent sousCategorieView = sousCategorieLoader.load();
        Tab sousCategorieTab = new Tab("Sous-Catégories", sousCategorieView);
        sousCategorieTab.setClosable(false);

        // Onglet Produits
        FXMLLoader produitLoader = new FXMLLoader(getClass().getResource("/views/ProduitView.fxml"));
        Parent produitView = produitLoader.load();
        Tab produitTab = new Tab("Produits", produitView);
        produitTab.setClosable(false);



        // Ajouter tous les onglets au TabPane
        tabPane.getTabs().addAll(categorieTab, sousCategorieTab, produitTab);

        // Création de la scène
        Scene scene = new Scene(tabPane, 1000, 700);
        stage.setTitle("Dashboard - Gestion Agriculture");
        stage.setScene(scene);
        stage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}
