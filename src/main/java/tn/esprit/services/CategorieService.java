package tn.esprit.services;

import tn.esprit.entities.Categorie;
import tn.esprit.tools.my_db;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CategorieService {

    private final Connection cnx;

    public CategorieService() {
        cnx = my_db.getInstance().getConnection();
    }

    public void ajouter(Categorie c) {
        String sql = "INSERT INTO categorie (nom, image) VALUES (?, ?)";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, c.getNom());
            if (c.getImage() != null && !c.getImage().isEmpty()) {
                ps.setString(2, c.getImage());
            } else {
                ps.setNull(2, Types.VARCHAR);
            }
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void modifier(Categorie c) {
        String sql = "UPDATE categorie SET nom=?, image=? WHERE id=?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, c.getNom());
            if (c.getImage() != null && !c.getImage().isEmpty()) {
                ps.setString(2, c.getImage());
            } else {
                ps.setNull(2, Types.VARCHAR);
            }
            ps.setLong(3, c.getId());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void supprimer(Categorie c) {
        String sql = "DELETE FROM categorie WHERE id=?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setLong(1, c.getId());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public List<Categorie> getAll() {
        List<Categorie> list = new ArrayList<>();
        String sql = "SELECT * FROM categorie";
        try (Statement st = cnx.createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) {
                Categorie c = new Categorie();
                c.setId(rs.getLong("id"));
                c.setNom(rs.getString("nom"));
                c.setImage(rs.getString("image")); // Peut être null
                list.add(c);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return list;
    }
}
