package tn.esprit.services;

import tn.esprit.entities.Favoris;
import tn.esprit.entities.Produit;
import tn.esprit.tools.my_db;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class FavorisService {

    private final Connection cnx;

    public FavorisService() {
        this.cnx = my_db.getInstance().getConnection();
    }

    // ✅ Ajouter un produit aux favoris
    public boolean ajouterFavoris(Long produitId, int userId) {
        // vérification doublon
        String checkSql = "SELECT COUNT(*) FROM favoris WHERE produit_id = ? AND user_id = ?";
        try (PreparedStatement check = cnx.prepareStatement(checkSql)) {
            check.setLong(1, produitId);
            check.setInt(2, userId);
            ResultSet rs = check.executeQuery();
            if (rs.next() && rs.getInt(1) > 0) {
                return true; // déjà dans favoris
            }
        } catch (SQLException e) {
            e.printStackTrace();
            return true;
        }

        // ajouter
        String sql = "INSERT INTO favoris (produit_id, user_id) VALUES (?, ?)";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setLong(1, produitId);
            ps.setInt(2, userId);
            ps.executeUpdate();
            return false;
        } catch (SQLException e) {
            e.printStackTrace();
            return true;
        }
    }


    // ✅ Supprimer un favori
    public void supprimer(Long id) {
        if (id == null) {
            System.err.println("❌ ID de favori non fourni.");
            return;
        }

        String sql = "DELETE FROM favoris WHERE id = ?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setLong(1, id);
            ps.executeUpdate();
            System.out.println("🗑️ Favori supprimé (ID = " + id + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de la suppression du favori : " + e.getMessage());
        }
    }

    // ✅ Récupérer tous les favoris d’un utilisateur
    public List<Favoris> getFavorisParUser(int userId) {
        List<Favoris> favorisList = new ArrayList<>();

        String sql = """
            SELECT f.id AS fav_id,
                   p.id AS produit_id, p.nom, p.prix, p.description, p.stock, p.image
            FROM favoris f
            JOIN produit p ON f.produit_id = p.id
            WHERE f.user_id = ?
        """;

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setInt(1, userId);

            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Produit produit = new Produit();
                    produit.setId(rs.getLong("produit_id"));
                    produit.setNom(rs.getString("nom"));
                    produit.setPrix(rs.getBigDecimal("prix"));
                    produit.setDescription(rs.getString("description"));
                    produit.setStock(rs.getInt("stock"));

                    // ✅ Ne stocker que le nom de fichier, pas un chemin complet
                    String imageName = rs.getString("image");
                    produit.setImage(imageName != null ? imageName.trim() : null);

                    Favoris favoris = new Favoris();
                    favoris.setId(rs.getLong("fav_id"));
                    favoris.setProduit(produit);
                    favoris.setUserId(userId);

                    favorisList.add(favoris);
                }
            }

        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de la récupération des favoris : " + e.getMessage());
        }

        return favorisList;
    }
    public boolean existeDansFavoris(Long produitId, int userId) {
        String sql = "SELECT COUNT(*) FROM favoris WHERE produit_id = ? AND user_id = ?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setLong(1, produitId);
            ps.setInt(2, userId);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                return rs.getInt(1) > 0;
            }
        } catch (SQLException e) {
            System.err.println("❌ Erreur vérification favoris : " + e.getMessage());
        }
        return false;
    }
    public void supprimerParProduitEtUser(Long produitId, int userId) {
        String sql = "DELETE FROM favoris WHERE produit_id = ? AND user_id = ?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setLong(1, produitId);
            ps.setInt(2, userId);
            ps.executeUpdate();
            System.out.println("🗑️ Favori supprimé (produit_id = " + produitId + ", user_id = " + userId + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de la suppression du favori : " + e.getMessage());
        }
    }

}
