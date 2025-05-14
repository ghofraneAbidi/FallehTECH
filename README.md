 # FallehTech – Version Web (Symfony 6.4)

FallehTech est une application web développée dans le cadre du module **Projet Intégré : Développement Web Java** à **Esprit School of Engineering**.  
Elle a pour but de moderniser le secteur agricole tunisien en facilitant les échanges entre agriculteurs, clients et ouvriers.

## Description du projet

- **Objectif** : Fournir une plateforme numérique pour moderniser le secteur agricole en Tunisie.
- **Problème résolu** : Les agriculteurs, ouvriers et clients agricoles manquent de canaux directs pour vendre, acheter, postuler et échanger. FallehTech centralise ces besoins dans une seule interface.
- **Fonctionnalités principales** :
  - Vente de produits agricoles (matériel, fruits, légumes…)
  - Publication d’articles de blog et interaction communautaire
  - Consultation des produits et blogs par les clients
  - Postulation à des offres d'emploi pour les ouvriers

> ⚙️ Ce projet constitue la première version de l'application, avant le développement de la version desktop avec JavaFX.

---

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/ton-utilisateur/fallehtech-symfony.git
   cd fallehtech-symfony
2. **Installer les dépendances PHP avec Composer**
   ```bash
   composer install
4. **Installer les dépendances frontend avec npm**
   ```bash
   npm install
   npm run build
6. **Configurer l’environnement**
   ```bash
   cp .env .env.local
8. **Créer la base de données**
   ```bash
   php bin/console doctrine:database:create
10. **Exécuter les migrations**
    ```bash
    php bin/console doctrine:migrations:migrate
12. **Lancer le serveur Symfony**
    ```bash
    symfony server:start

---

## Utilisation

- **Framework** : Symfony 6.4 (PHP)
- **Base de données** : MySQL
- **Frontend** : Twig, Bootstrap
- **Services tiers** : Différents APIs pour les fonctionnalités avancées

---

## Contribution
Nous remercions tous ceux qui ont contribué à ce projet !
### Contributeurs
- [Sarah FALEH](https://github.com/SarahFaleh) - Gestion PRODUITS
- [Ghofrane ABIDI](https://github.com/GhofraneAbidi) - Gestion BLOGS
- [Abderrazek CHAMEKH](https://github.com/AbderrazekChamekh) - Gestion E-COMMERCE
- [Chaima AJAILIA](https://github.com/ChaimaAjailia) - Gestion UTILISATEURS
- [Zied ALIMI](https://github.com/ZiedAlimi) - Gestion OFFRES
### Vous voulez contribuer aussi?
Vous êtes les bienvenues !
Veuillez créer une issue ou un fork, et soumettre une Pull Request après vos modifications.

---

## Topics
#Symfony #web-development #agriculture #esprit #marketplace #blog #weather #job-platform #odd

