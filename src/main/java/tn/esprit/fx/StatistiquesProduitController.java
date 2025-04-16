package tn.esprit.fx;

import javafx.fxml.FXML;
import javafx.scene.chart.BarChart;
import javafx.scene.chart.CategoryAxis;
import javafx.scene.chart.NumberAxis;
import javafx.scene.chart.XYChart;
import javafx.scene.control.ComboBox;
import javafx.stage.Stage;
import tn.esprit.entities.Produit;
import tn.esprit.services.ProduitService;

import java.util.Comparator;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

public class StatistiquesProduitController {

    @FXML
    private ComboBox<String> statistiqueComboBox;

    @FXML
    private BarChart<String, Number> statistiqueChart;

    @FXML
    private CategoryAxis xAxis;

    @FXML
    private NumberAxis yAxis;

    private final ProduitService produitService = new ProduitService();

    @FXML
    public void initialize() {
        statistiqueComboBox.getItems().addAll(
                "Nombre de produits par catégorie",
                "Top 5 des produits avec le stock le plus élevé"
        );
    }

    @FXML
    public void afficherStatistiques() {
        String selected = statistiqueComboBox.getValue();
        if (selected == null) return;

        statistiqueChart.getData().clear();
        XYChart.Series<String, Number> series = new XYChart.Series<>();

        List<Produit> produits = produitService.getAll();

        switch (selected) {
            case "Nombre de produits par catégorie" -> {
                Map<String, Long> countByCat = produits.stream()
                        .collect(Collectors.groupingBy(
                                p -> p.getCategorie() != null ? p.getCategorie().getNom() : "Inconnue",
                                Collectors.counting()
                        ));
                countByCat.forEach((cat, count) -> series.getData().add(new XYChart.Data<>(cat, count)));
            }

            case "Top 5 des produits avec le stock le plus élevé" -> {
                produits.stream()
                        .sorted(Comparator.comparingInt(Produit::getStock).reversed())
                        .limit(5)
                        .forEach(p -> series.getData().add(new XYChart.Data<>(p.getNom(), p.getStock())));
            }
        }

        statistiqueChart.getData().add(series);
    }

    @FXML
    public void fermerFenetre() {
        Stage stage = (Stage) statistiqueChart.getScene().getWindow();
        stage.close();
    }
}
