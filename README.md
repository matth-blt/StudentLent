# StudentLent - Plateforme de location entre étudiants

## 📋 Description

StudentLent est une application web PHP permettant aux étudiants de louer et proposer du matériel entre eux (outils, électronique, équipements sportifs, etc.).

### Fonctionnalités principales

- 🔐 **Authentification** : Inscription, connexion et gestion de compte
- 📦 **Catalogue produits** : Parcourir, filtrer par catégorie et rechercher des produits
- ➕ **Ajout de produits** : Les utilisateurs peuvent proposer leurs objets à la location
- 📅 **Système de location** : Réservation avec calendrier interactif et dates indisponibles en rouge
- 💰 **Tarification** : Prix journalier avec réduction automatique de 10% pour les locations de 7 jours ou plus
- 👤 **Espace personnel** : Historique des locations (en tant que locataire et propriétaire)

## 🛠️ Prérequis

- **PHP 8.0+**
- **MySQL** (via WAMP, XAMPP, ou autre)
- Un navigateur web moderne

## 🚀 Installation

### 1. Vérifier PHP

```bash
php -v
```

Si la commande `php` est introuvable :
- **Windows** : Utilisez WAMP ou XAMPP, ou téléchargez PHP depuis [php.net](https://www.php.net/)
- **macOS** : `brew install php`
- **Linux** : `apt install php-cli php-mysql`

### 2. Configurer la base de données

1. Créez une base de données MySQL nommée `studentlent`
2. Importez le fichier `assets/bdd.sql` dans phpMyAdmin ou via terminal :
   ```bash
   mysql -u root -p studentlent < assets/bdd.sql
   ```
3. Copiez `config.dist.php` en `config.php` et modifiez les identifiants de connexion :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'studentlent');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### 3. Lancer le serveur

**Option A - Avec WAMP/XAMPP :**
- Placez le projet dans `www/` (WAMP) ou `htdocs/` (XAMPP)
- Accédez à : http://localhost/StudentLent-Module-3-start

**Option B - Serveur PHP intégré :**
```bash
php -S localhost:8000
```
Puis ouvrez : http://localhost:8000

## 📁 Structure du projet

```
├── assets/
│   ├── bdd.sql
│   └── styles.css
├── lib/
│   ├── database.php
│   ├── auth_functions.php
│   ├── functions.php
│   ├── users.php
│   ├── products.php
│   └── rents.php
├── tests/
│   └── test-connexion.php
├── templates/
│   ├── benefits.php
│   ├── cta.php
│   ├── footer.php
│   ├── header.php
│   ├── login.php
│   ├── logout.php
│   ├── nav.php
│   ├── search.php
│   ├── signup.php
│   ├── site-footer.php
│   ├── stats.php
│   └── steps.php
├── home.php
├── catalogue.php
├── product.php
├── add_prod.php
├── account.php
├── rent_summary.php
├── process_rent.php
├── README.md
└── config.php
```

## 🗄️ Base de données

### Tables

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs (id, username, email, password) |
| `products` | Produits à louer (id, title, price, category, user_id, etc.) |
| `categories` | Catégories de produits |
| `rents` | Locations (id, product_id, user_id, dates, status, prix) |

### Statuts de location

- `pending` : En attente de confirmation
- `confirmed` : Confirmée
- `completed` : Terminée
- `cancelled` : Annulée

## 💡 Notes

- Le calendrier de réservation utilise **Flatpickr** pour afficher les dates indisponibles en rouge
- Les mots de passe sont hashés avec `password_hash()` (bcrypt)
- L'application utilise les sessions PHP pour l'authentification
- Tailwind CSS est chargé via CDN pour le styling