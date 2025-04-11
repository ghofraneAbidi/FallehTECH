package org.example;
import tn.esprit.entities.Categorie;
import tn.esprit.entities.SousCategorie;
import tn.esprit.services.SousCategorieService;
import tn.esprit.entities.OffreEmploi;
import tn.esprit.entities.Produit;
import tn.esprit.services.ProduitService;
import tn.esprit.services.CategorieService;
import tn.esprit.services.ServiceOffreEmploi;

import java.util.List;
import java.util.Scanner;

public class Main {

    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        System.out.println("=== Menu Principal ===");
        System.out.println("1. Gérer les Offres d'emploi");
        System.out.println("2. Gérer les Produits");
        System.out.println("4. Gérer les Sous-Catégories");
        System.out.println("5. Quitter");
        System.out.print("Choix : ");


        int choix = getValidInteger(scanner);

        switch (choix) {
            case 1 -> runOffreMenu();
            case 2 -> runProduitMenu();
            case 3 -> runCategorieMenu();
            case 4 -> runSousCategorieMenu();
            case 5 -> System.out.println("👋 Fin du programme.");
            default -> System.out.println("❌ Choix invalide !");
        }


    }

    // ===================== GESTION OFFRES =====================
    private static void runOffreMenu() {
        Scanner scanner = new Scanner(System.in);
        ServiceOffreEmploi service = new ServiceOffreEmploi();

        while (true) {
            System.out.println("\n=== Gestion des Offres ===");
            System.out.println("1. Ajouter une offre");
            System.out.println("2. Modifier une offre");
            System.out.println("3. Supprimer une offre");
            System.out.println("4. Afficher toutes les offres");
            System.out.println("5. Retour");
            System.out.print("Choix : ");

            int choix = getValidInteger(scanner);
            switch (choix) {
                case 1 -> ajouterOffre(service, scanner);
                case 2 -> modifierOffre(service, scanner);
                case 3 -> supprimerOffre(service, scanner);
                case 4 -> afficherOffres(service);
                case 5 -> { return; }
                default -> System.out.println("❌ Choix invalide !");
            }
        }
    }

    private static void ajouterOffre(ServiceOffreEmploi service, Scanner scanner) {
        System.out.print("Titre: ");
        String titre = scanner.nextLine();
        System.out.print("Description: ");
        String description = scanner.nextLine();
        System.out.print("Salaire (DT): ");
        float salaire = Float.parseFloat(scanner.nextLine());
        System.out.print("Lieu: ");
        String lieu = scanner.nextLine();

        OffreEmploi offre = new OffreEmploi();
        offre.setTitre(titre);
        offre.setDescription(description);
        offre.setSalaire(salaire);
        offre.setLieu(lieu);

        service.ajouter(offre);
        System.out.println("✅ Offre ajoutée !");
    }

    private static void modifierOffre(ServiceOffreEmploi service, Scanner scanner) {
        System.out.print("ID: ");
        int id = getValidInteger(scanner);
        System.out.print("Nouveau titre: ");
        String titre = scanner.nextLine();
        System.out.print("Nouvelle description: ");
        String description = scanner.nextLine();
        System.out.print("Nouveau salaire (DT): ");
        float salaire = Float.parseFloat(scanner.nextLine());
        System.out.print("Nouveau lieu: ");
        String lieu = scanner.nextLine();

        OffreEmploi offre = new OffreEmploi();
        offre.setId(id);
        offre.setTitre(titre);
        offre.setDescription(description);
        offre.setSalaire(salaire);
        offre.setLieu(lieu);

        service.modifier(offre);
        System.out.println("✏️ Offre modifiée !");
    }

    private static void supprimerOffre(ServiceOffreEmploi service, Scanner scanner) {
        System.out.print("ID: ");
        int id = getValidInteger(scanner);
        OffreEmploi offre = new OffreEmploi();
        offre.setId(id);
        service.supprimer(offre);
        System.out.println("🗑️ Offre supprimée !");
    }

    private static void afficherOffres(ServiceOffreEmploi service) {
        List<OffreEmploi> offres = service.getAll();
        if (offres.isEmpty()) {
            System.out.println("Aucune offre trouvée.");
        } else {
            System.out.println("\n=== Offres disponibles ===");
            for (OffreEmploi o : offres) {
                System.out.println("ID: " + o.getId());
                System.out.println("Titre: " + o.getTitre());
                System.out.println("Description: " + o.getDescription());
                System.out.println("Salaire: " + o.getSalaire() + " DT");
                System.out.println("Lieu: " + o.getLieu());
                System.out.println("----------");
            }
        }
    }

    // ===================== GESTION PRODUITS =====================
    private static void runProduitMenu() {
        Scanner scanner = new Scanner(System.in);
        ProduitService service = new ProduitService();

        while (true) {
            System.out.println("\n=== Gestion des Produits ===");
            System.out.println("1. Ajouter un produit");
            System.out.println("2. Modifier un produit");
            System.out.println("3. Supprimer un produit");
            System.out.println("4. Afficher tous les produits");
            System.out.println("5. Retour");
            System.out.print("Choix : ");

            int choix = getValidInteger(scanner);

            switch (choix) {
                case 1 -> ajouterProduit(service, scanner);
                case 2 -> modifierProduit(service, scanner);
                case 3 -> supprimerProduit(service, scanner);
                case 4 -> afficherProduits(service);
                case 5 -> { return; }
                default -> System.out.println("❌ Choix invalide !");
            }
        }
    }

    private static void ajouterProduit(ProduitService service, Scanner scanner) {
        System.out.print("Nom : ");
        String nom = scanner.nextLine();

        System.out.print("Prix (DT) : ");
        String prixStr = scanner.nextLine();

        System.out.print("Description : ");
        String desc = scanner.nextLine();

        System.out.print("Stock : ");
        int stock = Integer.parseInt(scanner.nextLine());

        System.out.print("ID Catégorie : ");
        Long categorieId = Long.valueOf(scanner.nextLine());

        System.out.print("ID Sous-Catégorie : ");
        Long sousCategorieId = Long.valueOf(scanner.nextLine());

        Produit p = new Produit();
        p.setNom(nom);
        p.setPrix(new java.math.BigDecimal(prixStr));
        p.setDescription(desc);
        p.setStock(stock);

        // ✅ Simuler les objets Catégorie et SousCategorie
        Categorie categorie = new Categorie();
        categorie.setId(categorieId);
        p.setCategorie(categorie);
        SousCategorie sousCategorie = new SousCategorie();
        sousCategorie.setId(sousCategorieId);

        p.setSousCategorie(sousCategorie);

        service.ajouter(p);
        System.out.println("✅ Produit ajouté !");
    }


    private static void modifierProduit(ProduitService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());
        System.out.print("Nom : ");
        String nom = scanner.nextLine();
        System.out.print("Prix : ");
        String prixStr = scanner.nextLine();
        System.out.print("Description : ");
        String desc = scanner.nextLine();
        System.out.print("Stock : ");
        int stock = Integer.parseInt(scanner.nextLine());

        Produit p = new Produit();
        p.setId(id);
        p.setNom(nom);
        p.setPrix(new java.math.BigDecimal(prixStr));
        p.setDescription(desc);
        p.setStock(stock);

        service.modifier(p);
        System.out.println("✏️ Produit modifié !");
    }

    private static void supprimerProduit(ProduitService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());
        Produit p = new Produit();
        p.setId(id);
        service.supprimer(p);
        System.out.println("🗑️ Produit supprimé !");
    }

    private static void afficherProduits(ProduitService service) {
        List<Produit> produits = service.getAll();
        if (produits.isEmpty()) {
            System.out.println("Aucun produit trouvé.");
        } else {
            System.out.println("\n=== Liste des produits ===");
            for (Produit p : produits) {
                System.out.println("ID: " + p.getId());
                System.out.println("Nom: " + p.getNom());
                System.out.println("Prix: " + p.getPrix() + " DT");
                System.out.println("Description: " + p.getDescription());
                System.out.println("Stock: " + p.getStock());
                System.out.println("--------------------------");
            }
        }
    }
    // ===================== GESTION CATÉGORIES =====================
    private static void runCategorieMenu() {
        Scanner scanner = new Scanner(System.in);
        CategorieService service = new CategorieService();

        while (true) {
            System.out.println("\n=== Gestion des Catégories ===");
            System.out.println("1. Ajouter une catégorie");
            System.out.println("2. Modifier une catégorie");
            System.out.println("3. Supprimer une catégorie");
            System.out.println("4. Afficher toutes les catégories");
            System.out.println("5. Retour");
            System.out.print("Choix : ");

            int choix = getValidInteger(scanner);

            switch (choix) {
                case 1 -> ajouterCategorie(service, scanner);
                case 2 -> modifierCategorie(service, scanner);
                case 3 -> supprimerCategorie(service, scanner);
                case 4 -> afficherCategories(service);
                case 5 -> { return; }
                default -> System.out.println("❌ Choix invalide !");
            }
        }
    }

    private static void ajouterCategorie(CategorieService service, Scanner scanner) {
        System.out.print("Nom : ");
        String nom = scanner.nextLine();

        System.out.print("Image (chemin ou nom du fichier, facultatif) : ");
        String image = scanner.nextLine();

        Categorie c = new Categorie();
        c.setNom(nom);
        c.setImage(image.isBlank() ? null : image);

        service.ajouter(c);
        System.out.println("✅ Catégorie ajoutée !");
    }

    private static void modifierCategorie(CategorieService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());

        System.out.print("Nouveau nom : ");
        String nom = scanner.nextLine();

        System.out.print("Nouvelle image (laisser vide si inchangé) : ");
        String image = scanner.nextLine();

        Categorie c = new Categorie();
        c.setId(id);
        c.setNom(nom);
        c.setImage(image.isBlank() ? null : image);

        service.modifier(c);
        System.out.println("✏️ Catégorie modifiée !");
    }

    private static void supprimerCategorie(CategorieService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());

        Categorie c = new Categorie();
        c.setId(id);

        service.supprimer(c);
        System.out.println("🗑️ Catégorie supprimée !");
    }

    private static void afficherCategories(CategorieService service) {
        List<Categorie> categories = service.getAll();
        if (categories.isEmpty()) {
            System.out.println("Aucune catégorie trouvée.");
        } else {
            System.out.println("\n=== Liste des catégories ===");
            for (Categorie c : categories) {
                System.out.println("ID: " + c.getId());
                System.out.println("Nom: " + c.getNom());
                System.out.println("Image: " + (c.getImage() != null ? c.getImage() : "Aucune"));
                System.out.println("----------------------------");
            }
        }
    }


    // ===================== UTIL =====================
    private static int getValidInteger(Scanner scanner) {
        while (true) {
            try {
                return Integer.parseInt(scanner.nextLine().trim());
            } catch (NumberFormatException e) {
                System.out.print("❗ Veuillez entrer un nombre valide : ");
            }
        }
    }
    // ===================== GESTION SOUS-CATÉGORIES =====================
    private static void runSousCategorieMenu() {
        Scanner scanner = new Scanner(System.in);
        SousCategorieService service = new SousCategorieService();

        while (true) {
            System.out.println("\n=== Gestion des Sous-Catégories ===");
            System.out.println("1. Ajouter une sous-catégorie");
            System.out.println("2. Modifier une sous-catégorie");
            System.out.println("3. Supprimer une sous-catégorie");
            System.out.println("4. Afficher toutes les sous-catégories");
            System.out.println("5. Retour");
            System.out.print("Choix : ");

            int choix = getValidInteger(scanner);

            switch (choix) {
                case 1 -> ajouterSousCategorie(service, scanner);
                case 2 -> modifierSousCategorie(service, scanner);
                case 3 -> supprimerSousCategorie(service, scanner);
                case 4 -> afficherSousCategories(service);
                case 5 -> { return; }
                default -> System.out.println("❌ Choix invalide !");
            }
        }
    }

    private static void ajouterSousCategorie(SousCategorieService service, Scanner scanner) {
        System.out.print("Nom : ");
        String nom = scanner.nextLine();

        System.out.print("Image (chemin ou nom du fichier, facultatif) : ");
        String image = scanner.nextLine();

        System.out.print("ID de la catégorie parente : ");
        Long catId = Long.valueOf(scanner.nextLine());

        Categorie c = new Categorie();
        c.setId(catId);

        SousCategorie s = new SousCategorie();
        s.setNom(nom);
        s.setImage(image.isBlank() ? null : image);
        s.setCategorie(c);

        service.ajouter(s);
        System.out.println("✅ Sous-catégorie ajoutée !");
    }

    private static void modifierSousCategorie(SousCategorieService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());

        System.out.print("Nouveau nom : ");
        String nom = scanner.nextLine();

        System.out.print("Nouvelle image : ");
        String image = scanner.nextLine();

        System.out.print("Nouvelle ID Catégorie : ");
        Long catId = Long.valueOf(scanner.nextLine());

        SousCategorie s = new SousCategorie();
        s.setId(id);
        s.setNom(nom);
        s.setImage(image.isBlank() ? null : image);

        Categorie c = new Categorie();
        c.setId(catId);
        s.setCategorie(c);

        service.modifier(s);
        System.out.println("✏️ Sous-catégorie modifiée !");
    }

    private static void supprimerSousCategorie(SousCategorieService service, Scanner scanner) {
        System.out.print("ID : ");
        Long id = Long.valueOf(scanner.nextLine());

        SousCategorie s = new SousCategorie();
        s.setId(id);
        service.supprimer(s);

        System.out.println("🗑️ Sous-catégorie supprimée !");
    }

    private static void afficherSousCategories(SousCategorieService service) {
        List<SousCategorie> list = service.getAll();
        if (list.isEmpty()) {
            System.out.println("Aucune sous-catégorie trouvée.");
        } else {
            System.out.println("\n=== Liste des sous-catégories ===");
            for (SousCategorie s : list) {
                System.out.println("ID: " + s.getId());
                System.out.println("Nom: " + s.getNom());
                System.out.println("Image: " + (s.getImage() != null ? s.getImage() : "Aucune"));
                System.out.println("Catégorie ID: " + (s.getCategorie() != null ? s.getCategorie().getId() : "N/A"));
                System.out.println("----------------------------");
            }
        }
    }

}
