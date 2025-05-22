# Blog Symfony Project

[![forthebadge](https://forthebadge.com/images/badges/made-with-php.svg)](https://forthebadge.com)


Un blog permettant à des utilisateurs de **poser des questions, partager des idées ou publier des articles**, avec un **filtrage  automatique des propos injurieux**. En cas d’erreur de filtrage, un système de **signalement** est disponible et géré par un administrateur.

---

## Pré-requis

- PHP >= 8.1
- Composer
- Symfony CLI
- PostgreSQL
- OpenSSL (pour JWT)
- [Docker](https://www.docker.com/)

---

## Installation

1. **Cloner le dépôt :**
```bash
git clone ...
cd BlogSymfonyProject
```

2.**Installer les dependances**
```bash
composer install
```

3.**Configurer l’environnement**
Crée un fichier .env à la racine du projet en se basant sur le fichier .env.example.

4.**Générer les cles JWT**
```bash
php bin/console lexik:jwt:generate-keypair
```
Ajoute la clé a l'espace dedié (JWT_PASSPHRASE=) dans le fichier .env

5.**Créer le container docker**
Ouvrir docker puis lancez la commande "docker compose up --build"

6.**Création et initialisation de la base de données**
```bash
docker exec -it symfony_app bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

---

## Démarrage

Lancer la commande "docker compose up" et accéder à l’application via "http://localhost:8182/"
