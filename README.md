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
   composer install
3. **Installer les dépendances frontend avec npm**
   npm install
   npm run build
4. **Configurer l’environnement**
   cp .env .env.local
5. **Créer la base de données**
   php bin/console doctrine:database:create
6. **Exécuter les migrations**
   php bin/console doctrine:migrations:migrate
7. **Lancer le serveur Symfony**
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

