package tn.esprit.fx;

import javafx.beans.property.ReadOnlyObjectWrapper;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.util.StringConverter;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.CategorieService;
import tn.esprit.services.ProduitService;
import tn.esprit.services.SousCategorieService;
import tn.esprit.utils.ImageUtils;

import java.io.File;
import java.io.IOException;
import java.net.URL;
import java.util.List;
import java.util.ResourceBundle;

public class SousCategorieController implements Initializable {


    @FXML private TextField nomField;
    @FXML private TableView<SousCategorie> tableView;
    @FXML private TableColumn<SousCategorie, Long> idCol;
    @FXML private TableColumn<SousCategorie, String> nomCol;
    @FXML private TableColumn<SousCategorie, String> categorieCol;
    @FXML private TableColumn<SousCategorie, String> imageCol;
    @FXML private TableColumn<SousCategorie, Void> actionCol;
    @FXML private ImageView imagePreview;
    @FXML private Label notifLabel;

    private final CategorieService categorieService = new CategorieService();
    private final SousCategorieService sousCategorieService = new SousCategorieService();
    private final ProduitService produitService = new ProduitService();

    private SousCategorie sousCategorieEnCoursEdition = null;
    private String selectedImageName;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        idCol.setCellValueFactory(new PropertyValueFactory<>("id"));
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));

        categorieCol.setCellValueFactory(cellData -> {
            Categorie cat = cellData.getValue().getCategorie();
            return new ReadOnlyObjectWrapper<>(cat != null ? cat.getNom() : "Aucune");
        });

        imageCol.setCellValueFactory(new PropertyValueFactory<>("image"));
        imageCol.setCellFactory(param -> new TableCell<>() {
            private final ImageView imageView = new ImageView();
            {
                imageView.setFitWidth(80);
                imageView.setFitHeight(60);
                imageView.setPreserveRatio(true);
            }
            @Override
            protected void updateItem(String imageName, boolean empty) {
                super.updateItem(imageName, empty);
                if (empty || imageName == null || imageName.isBlank()) {
                    setGraphic(null);
                } else {
                    imageView.setImage(ImageUtils.chargerDepuisNom(imageName));
                    setGraphic(imageView);
                }
            }
        });

        setupActionColumn();

        afficherSousCategories();
        ajusterColonnes();
    }



    public void afficherSousCategories() {
        tableView.getItems().setAll(sousCategorieService.getAll());
    }

    @FXML
    public void ajouterSousCategorie() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/ajouterSousCategorie.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Ajouter une Sous-Catégorie");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.showAndWait();

            afficherSousCategories();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void setupActionColumn() {
        actionCol.setCellFactory(col -> new TableCell<>() {
            private final Button btnEdit = new Button();
            private final Button btnDelete = new Button();
            private final HBox hbox = new HBox(10, btnEdit, btnDelete);
            private final ImageView editIcon;
            private final ImageView deleteIcon;

            {
                URL editUrl = getClass().getResource("/icons/modifier.png");
                URL deleteUrl = getClass().getResource("/icons/delete.png");

                editIcon = (editUrl != null) ? new ImageView(new Image(editUrl.toExternalForm())) : new ImageView();
                deleteIcon = (deleteUrl != null) ? new ImageView(new Image(deleteUrl.toExternalForm())) : new ImageView();

                editIcon.setFitWidth(18);
                editIcon.setFitHeight(18);
                deleteIcon.setFitWidth(18);
                deleteIcon.setFitHeight(18);

                btnEdit.setGraphic(editIcon);
                btnDelete.setGraphic(deleteIcon);
                btnEdit.getStyleClass().add("action-button");
                btnDelete.getStyleClass().add("action-button");

                // Action de modification
                btnEdit.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());
                    ouvrirPopupModification(selected);
                });

                // Action de suppression
                btnDelete.setOnAction(event -> {
                    SousCategorie selected = getTableView().getItems().get(getIndex());

                    if (produitService.existsBySousCategorie(selected.getId())) {
                        Alert alert = new Alert(Alert.AlertType.WARNING,
                                "Impossible de supprimer cette sous-catégorie car elle est liée à des produits.",
                                ButtonType.OK);
                        alert.setHeaderText("Suppression bloquée");
                        alert.showAndWait();
                        return;
                    }

                    Alert confirm = new Alert(Alert.AlertType.CONFIRMATION,
                            "Supprimer cette sous-catégorie ?", ButtonType.YES, ButtonType.NO);
                    confirm.setHeaderText("Confirmation");
                    confirm.showAndWait().ifPresent(response -> {
                        if (response == ButtonType.YES) {
                            sousCategorieService.supprimer(selected);
                            afficherSousCategories();
                        }
                    });
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : hbox);
            }
        });

        actionCol.setCellValueFactory(param -> new ReadOnlyObjectWrapper<>(null));
    }

    private void showNotification(String message, boolean isWarning) {
        notifLabel.setText(message);
        notifLabel.getStyleClass().removeAll("notif-label", "warning");
        notifLabel.getStyleClass().add("notif-label");
        if (isWarning) notifLabel.getStyleClass().add("warning");
        notifLabel.setVisible(true);

        new Thread(() -> {
            try {
                Thread.sleep(3000);
                javafx.application.Platform.runLater(() -> notifLabel.setVisible(false));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }

    private void clearFields() {
        nomField.clear();

        imagePreview.setImage(null);
        selectedImageName = null;
        sousCategorieEnCoursEdition = null;
    }

    private void ajusterColonnes() {
        tableView.setColumnResizePolicy(TableView.CONSTRAINED_RESIZE_POLICY);
        idCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.10);
        nomCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.25);
        categorieCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.25);
        imageCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.20);
        actionCol.setMaxWidth(1f * Integer.MAX_VALUE * 0.20);
    }
    @FXML
    private void ouvrirPopupAjout() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/ajoutersouscat.fxml"));
            Parent root = loader.load();

            Stage popup = new Stage();
            popup.setTitle("Ajouter une Sous-Catégorie");
            popup.initModality(Modality.APPLICATION_MODAL);
            popup.setScene(new Scene(root));
            popup.showAndWait();

            afficherSousCategories(); // rafraîchit la table après fermeture
        } catch (IOException e) {
            e.printStackTrace();
        }


    }
    public void ouvrirPopupModification(SousCategorie sousCategorie) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/views/ajoutersouscat.fxml"));
            Parent root = loader.load();

            AjouterSousCategorieController controller = loader.getController();
            controller.setSousCategorie(sousCategorie); // On remplit les champs ici

            Stage stage = new Stage();
            stage.setTitle("Modifier une Sous-Catégorie");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.showAndWait();

            afficherSousCategories(); // Actualiser la liste après fermeture
        } catch (IOException e) {
            e.printStackTrace();
        }
    }


}