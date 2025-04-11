package tn.esprit.fx;

import com.github.sarxos.webcam.Webcam;
import com.github.sarxos.webcam.WebcamResolution;
import javafx.beans.property.ReadOnlyObjectWrapper;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.stage.FileChooser;
import javafx.util.StringConverter;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.Produit;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.CategorieService;
import tn.esprit.services.ProduitService;
import tn.esprit.services.SousCategorieService;
import javafx.scene.chart.CategoryAxis;
import javafx.scene.chart.BarChart;
import javafx.scene.chart.XYChart;
import jakarta.mail.*;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;
import java.util.Properties;
import java.math.BigDecimal;
import java.util.*;
import java.util.stream.Collectors;
import javafx.scene.chart.CategoryAxis;
import javafx.scene.chart.BarChart;
import javafx.scene.chart.XYChart;
import javafx.scene.chart.NumberAxis;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.math.BigDecimal;
import java.net.URL;
import java.time.LocalDateTime;
import java.util.List;
import java.util.ResourceBundle;

public class ProduitController implements Initializable {

    @FXML private ComboBox<Categorie> categorieComboBox;
    @FXML private ComboBox<SousCategorie> sousCategorieComboBox;
    @FXML private TextField nomField, prixField, descriptionField, stockField;
    @FXML private TableView<Produit> tableView;
    @FXML private TableColumn<Produit, Long> idCol;
    @FXML private TableColumn<Produit, String> nomCol, descCol, categorieCol, sousCategorieCol;
    @FXML private TableColumn<Produit, BigDecimal> prixCol;
    @FXML private TableColumn<Produit, Integer> stockCol;
    @FXML private TableColumn<Produit, Void> actionCol;
    @FXML private TableColumn<Produit, String> imageCol;
    @FXML private ImageView imagePreview;
    @FXML
    private ComboBox<String> statistiqueComboBox;

    @FXML
    private BarChart<String, Number> statistiqueChart;

    @FXML
    private CategoryAxis xAxis;

    @FXML
    private NumberAxis yAxis;


    @FXML private ComboBox<String> filtreTypeComboBox;
    @FXML private TextField rechercheField;
    @FXML private ComboBox<Categorie> filtreCategorieComboBox;
    @FXML private ComboBox<SousCategorie> filtreSousCategorieComboBox;
    @FXML private ComboBox<String> filtreStockComboBox;

    private String selectedImagePath;
    private final ProduitService produitService = new ProduitService();
    private final CategorieService categorieService = new CategorieService();
    private final SousCategorieService sousCategorieService = new SousCategorieService();
    private Produit produitEnCoursEdition = null;


    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));
        descCol.setCellValueFactory(new PropertyValueFactory<>("description"));
        prixCol.setCellValueFactory(new PropertyValueFactory<>("prix"));
        stockCol.setCellValueFactory(new PropertyValueFactory<>("stock"));
        statistiqueComboBox.getItems().addAll(
                "Nombre de produits par catégorie",
                "Top 5 des produits avec le stock le plus élevé"
        );

        stockCol.setCellFactory(col -> new TableCell<>() {
            private final Label stockLabel = new Label();
            private final Button mailButton = new Button("📧");
            private final HBox hbox = new HBox(5, stockLabel, mailButton);

            {
                mailButton.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white;");
                mailButton.setOnAction(event -> {
                    Produit produit = getTableView().getItems().get(getIndex());
                    envoyerMailStockFaible(produit);
                });
            }


            @Override
            protected void updateItem(Integer stock, boolean empty) {
                super.updateItem(stock, empty);
                if (empty || stock == null) {
                    setGraphic(null);
                } else {
                    stockLabel.setText(String.valueOf(stock));

                    if (stock < 5) {
                        stockLabel.setStyle("-fx-text-fill: red;");
                        mailButton.setVisible(true);
                        setGraphic(hbox);
                    } else {
                        stockLabel.setStyle("-fx-text-fill: black;");
                        mailButton.setVisible(false);
                        setGraphic(stockLabel);
                    }
                }
            }
        });


        categorieCol.setCellValueFactory(cellData -> {
            Categorie c = cellData.getValue().getCategorie();
            return new ReadOnlyObjectWrapper<>(c != null ? c.getNom() : "");
        });

        sousCategorieCol.setCellValueFactory(cellData -> {
            SousCategorie sc = cellData.getValue().getSousCategorie();
            return new ReadOnlyObjectWrapper<>(sc != null ? sc.getNom() : "");
        });

        imageCol.setCellFactory(param -> new TableCell<>() {
            private final ImageView imageView = new ImageView();
            {
                imageView.setFitWidth(80);
                imageView.setFitHeight(60);
                imageView.setPreserveRatio(true);
            }

            @Override
            protected void updateItem(String path, boolean empty) {
                super.updateItem(path, empty);
                if (empty || path == null || path.isBlank()) {
                    setGraphic(null);
                } else {
                    try {
                        Image img = new Image(path, 80, 60, true, true);
                        imageView.setImage(img);
                        setGraphic(imageView);
                    } catch (Exception e) {
                        setGraphic(null);
                    }
                }
            }
        });
        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));

        chargerCategories();
        chargerFiltres();
        setupActionColumn();
        afficherProduits();

        filtreTypeComboBox.setOnAction(e -> {
            String selected = filtreTypeComboBox.getValue();
            rechercheField.setDisable(true);
            filtreCategorieComboBox.setDisable(true);
            filtreSousCategorieComboBox.setDisable(true);
            filtreStockComboBox.setDisable(true);

            switch (selected) {
                case "Nom" -> {
                    rechercheField.setDisable(false);
                    rechercheField.textProperty().addListener((obs, oldVal, newVal) -> appliquerFiltres());
                }
                case "Catégorie" -> filtreCategorieComboBox.setDisable(false);
                case "Sous-Catégorie" -> filtreSousCategorieComboBox.setDisable(false);
                case "Stock" -> filtreStockComboBox.setDisable(false);
            }
        });
    }

    private void chargerCategories() {
        List<Categorie> categories = categorieService.getAll();
        categorieComboBox.setItems(FXCollections.observableArrayList(categories));
        categorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(Categorie c) { return c != null ? c.getNom() : ""; }
            @Override public Categorie fromString(String s) { return null; }
        });

        categorieComboBox.setOnAction(e -> {
            Categorie selected = categorieComboBox.getSelectionModel().getSelectedItem();
            if (selected != null) {
                List<SousCategorie> sousCategories = sousCategorieService.getAll();
                sousCategorieComboBox.getItems().setAll(
                        sousCategories.stream()
                                .filter(sc -> sc.getCategorie().getId().equals(selected.getId()))
                                .toList()
                );
            }
        });

        sousCategorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(SousCategorie sc) { return sc != null ? sc.getNom() : ""; }
            @Override public SousCategorie fromString(String s) { return null; }
        });
    }

    private void chargerFiltres() {
        filtreTypeComboBox.setItems(FXCollections.observableArrayList("Nom", "Catégorie", "Sous-Catégorie", "Stock"));
        filtreCategorieComboBox.setItems(FXCollections.observableArrayList(categorieService.getAll()));
        filtreSousCategorieComboBox.setItems(FXCollections.observableArrayList(sousCategorieService.getAll()));
        filtreStockComboBox.setItems(FXCollections.observableArrayList("Stock faible", "Stock suffisant"));

        filtreCategorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(Categorie c) { return c != null ? c.getNom() : ""; }
            @Override public Categorie fromString(String s) { return null; }
        });

        filtreSousCategorieComboBox.setConverter(new StringConverter<>() {
            @Override public String toString(SousCategorie sc) { return sc != null ? sc.getNom() : ""; }
            @Override public SousCategorie fromString(String s) { return null; }
        });
    }

    @FXML
    public void appliquerFiltres() {
        String type = filtreTypeComboBox.getValue();
        List<Produit> produits = produitService.getAll();

        if (type == null) {
            tableView.getItems().setAll(produits);
            return;
        }

        switch (type) {
            case "Nom" -> {
                String query = rechercheField.getText().toLowerCase().trim();
                produits = produits.stream()
                        .filter(p -> p.getNom() != null && p.getNom().toLowerCase().contains(query))
                        .toList();
            }
            case "Catégorie" -> {
                Categorie selected = filtreCategorieComboBox.getValue();
                if (selected != null) {
                    produits = produits.stream()
                            .filter(p -> p.getCategorie() != null && p.getCategorie().getId().equals(selected.getId()))
                            .toList();
                }
            }
            case "Sous-Catégorie" -> {
                SousCategorie selected = filtreSousCategorieComboBox.getValue();
                if (selected != null) {
                    produits = produits.stream()
                            .filter(p -> p.getSousCategorie() != null && p.getSousCategorie().getId().equals(selected.getId()))
                            .toList();
                }
            }
            case "Stock" -> {
                String stockType = filtreStockComboBox.getValue();
                if ("Stock faible".equals(stockType)) {
                    produits = produits.stream().filter(p -> p.getStock() < 5).toList();
                } else if ("Stock suffisant".equals(stockType)) {
                    produits = produits.stream().filter(p -> p.getStock() >= 5).toList();
                }
            }
        }

        tableView.getItems().setAll(produits);
    }

    @FXML

    public void ajouterProduit() {
        try {
            String nom = nomField.getText().trim();
            BigDecimal prix = new BigDecimal(prixField.getText().trim());
            String desc = descriptionField.getText().trim();
            int stock = Integer.parseInt(stockField.getText().trim());
            Categorie cat = categorieComboBox.getValue();
            SousCategorie sc = sousCategorieComboBox.getValue();

            if (nom.isEmpty() || cat == null || sc == null) return;

            if (produitEnCoursEdition == null) {
                Produit p = new Produit();
                p.setNom(nom);
                p.setPrix(prix);
                p.setDescription(desc);
                p.setCategorie(cat);
                p.setSousCategorie(sc);
                p.setStock(stock);
                p.setUpdatedAt(LocalDateTime.now());
                p.setImage(selectedImagePath);
                produitService.ajouter(p);
            } else {
                produitEnCoursEdition.setNom(nom);
                produitEnCoursEdition.setPrix(prix);
                produitEnCoursEdition.setDescription(desc);
                produitEnCoursEdition.setCategorie(cat);
                produitEnCoursEdition.setSousCategorie(sc);
                produitEnCoursEdition.setStock(stock);
                produitEnCoursEdition.setUpdatedAt(LocalDateTime.now());
                produitEnCoursEdition.setImage(selectedImagePath);
                produitService.modifier(produitEnCoursEdition);

                produitEnCoursEdition = null;
            }

            clearFields();
            afficherProduits();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


    @FXML
    public void choisirImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");
        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg", "*.gif")
        );

        File selectedFile = fileChooser.showOpenDialog(null);
        if (selectedFile != null) {
            try {
                // Définir le dossier cible
                File targetDir = new File("photos"); // ou "src/main/resources/uploads" si tu veux
                if (!targetDir.exists()) {
                    targetDir.mkdirs();
                }

                // Créer un nom unique basé sur timestamp
                String extension = selectedFile.getName().substring(selectedFile.getName().lastIndexOf("."));
                String fileName = "img_" + System.currentTimeMillis() + extension;
                File targetFile = new File(targetDir, fileName);

                // Copier le fichier
                java.nio.file.Files.copy(selectedFile.toPath(), targetFile.toPath());

                // Enregistrer le chemin relatif pour affichage
                selectedImagePath = targetFile.toURI().toString();
                imagePreview.setImage(new Image(selectedImagePath));

            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }
    private void envoyerMailStockFaible(Produit produit) {
        String destinataire = "destinataire@email.com"; // à modifier
        String sujet = "Alerte Stock Faible: " + produit.getNom();
        String corps = "Le stock du produit \"" + produit.getNom() + "\" est faible : " + produit.getStock();

        try {
            Properties props = new Properties();
            props.put("mail.smtp.auth", "true");
            props.put("mail.smtp.starttls.enable", "true");
            props.put("mail.smtp.host", "smtp.gmail.com");
            props.put("mail.smtp.port", "587");

            String username = "sarafaleh76@gmail.com";
            String password = "eaktxbfbmqpnedtw"; // mot de passe application

            Session session = Session.getInstance(props, new Authenticator() {
                protected PasswordAuthentication getPasswordAuthentication() {
                    return new PasswordAuthentication("sarafaleh76@gmail.com", "eaktxbfbmqpnedtw");
                }
            });

            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress("sarafaleh76@gmail.com"));
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse("sinda.ouri@esprit.tn"));

            // Sujet dynamique avec nom du produit
            String subject = "Alerte : Stock faible pour le produit " + produit.getNom();
            message.setSubject(subject);

            // Corps de l’e-mail avec les infos détaillées du produit
            String body = "Bonjour,\n\n" +
                    "Le produit suivant présente un stock faible :\n\n" +
                    "🔹 Nom : " + produit.getNom() + "\n" +
                    "🔹 Stock actuel : " + produit.getStock() + "\n" +
                    "🔹 Catégorie : " + (produit.getCategorie() != null ? produit.getCategorie().getNom() : "N/A") + "\n" +
                    "🔹 Sous-catégorie : " + (produit.getSousCategorie() != null ? produit.getSousCategorie().getNom() : "N/A") + "\n\n" +
                    "Merci de réapprovisionner ce produit rapidement.\n\n" +
                    "Cordialement,\n" +
                    "L’équipe de gestion d’inventaire.";

            message.setText(body);

            Transport.send(message);

            Alert alert = new Alert(Alert.AlertType.INFORMATION, "Email envoyé avec succès !");
            alert.setHeaderText("Succès");
            alert.showAndWait();


        } catch (Exception e) {
            e.printStackTrace();
            Alert alert = new Alert(Alert.AlertType.ERROR, "Échec de l'envoi de l'e-mail.");
            alert.setHeaderText("Erreur");
            alert.showAndWait();
        }
    }


    @FXML
    public void prendrePhoto() {
        try {
            Webcam webcam = Webcam.getDefault();
            if (webcam != null) {
                webcam.setViewSize(WebcamResolution.VGA.getSize());
                webcam.open();

                BufferedImage image = webcam.getImage();
                if (image != null) {
                    String imageName = "photo_" + System.currentTimeMillis() + ".png";
                    String imagePath = "photos/" + imageName;
                    File outputFile = new File(imagePath);
                    outputFile.getParentFile().mkdirs();
                    ImageIO.write(image, "PNG", outputFile);

                    selectedImagePath = outputFile.toURI().toString();
                    imagePreview.setImage(new Image(selectedImagePath));
                }
                webcam.close();
            } else {
                System.out.println("Webcam non détectée");
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public void afficherProduits() {
        tableView.getItems().setAll(produitService.getAll());
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
                Map<String, Long> produitsParCategorie = produits.stream()
                        .collect(Collectors.groupingBy(
                                p -> p.getCategorie() != null ? p.getCategorie().getNom() : "Inconnue",
                                Collectors.counting()));
                produitsParCategorie.forEach((categorie, count) ->
                        series.getData().add(new XYChart.Data<>(categorie, count)));
            }

            case "Top 5 des produits avec le stock le plus élevé" -> {
                produits.stream()
                        .sorted(Comparator.comparingInt(Produit::getStock).reversed())
                        .limit(5)
                        .forEach(p -> series.getData().add(
                                new XYChart.Data<>(p.getNom(), p.getStock())));
            }
        }

        statistiqueChart.getData().add(series);
    }



    private void clearFields() {
        nomField.clear();
        prixField.clear();
        descriptionField.clear();
        stockField.clear();
        imagePreview.setImage(null);
        selectedImagePath = null;
        categorieComboBox.getSelectionModel().clearSelection();
        sousCategorieComboBox.getItems().clear();
    }

    private void setupActionColumn() {
        actionCol.setCellFactory(param -> new TableCell<>() {
            private final Button btnEdit = new Button("Modifier");
            private final Button btnDelete = new Button("Supprimer");
            private final HBox hbox = new HBox(5, btnEdit, btnDelete);

            {
                btnEdit.setOnAction(event -> {
                    Produit p = getTableView().getItems().get(getIndex());
                    produitEnCoursEdition = p;
                    nomField.setText(p.getNom());
                    prixField.setText(p.getPrix().toString());
                    descriptionField.setText(p.getDescription());
                    stockField.setText(String.valueOf(p.getStock()));
                    categorieComboBox.setValue(p.getCategorie());
                    sousCategorieComboBox.setValue(p.getSousCategorie());
                    selectedImagePath = p.getImage();
                    if (selectedImagePath != null && !selectedImagePath.isBlank()) {
                        imagePreview.setImage(new Image(selectedImagePath));
                    }
                });

                btnDelete.setOnAction(event -> {
                    Produit p = getTableView().getItems().get(getIndex());
                    produitService.supprimer(p);
                    afficherProduits();
                });
            }

            @Override protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : hbox);
            }
        });

        actionCol.setCellValueFactory(features -> new ReadOnlyObjectWrapper<>(null));
    }
}
