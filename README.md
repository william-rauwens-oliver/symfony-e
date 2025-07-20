# 🐦 Symfony Social Network - Twitter-like Platform

Un réseau social moderne inspiré de Twitter, développé avec Symfony 7.3, offrant une expérience utilisateur complète avec authentification, publications, commentaires, likes, hashtags, et un système de suggestions personnalisées.

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Technologies utilisées](#-technologies-utilisées)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Architecture](#-architecture)
- [Tests](#-tests)
- [API](#-api)
- [Conformité GDPR](#-conformité-gdpr)
- [Structure du projet](#-structure-du-projet)
- [Contribution](#-contribution)
- [Licence](#-licence)

## ✨ Fonctionnalités

### 🔐 Authentification & Sécurité
- **Inscription/Connexion** avec email et mot de passe
- **Authentification personnalisée** avec Symfony Security
- **Protection CSRF** sur tous les formulaires
- **Sessions sécurisées** avec remember me
- **Gestion des rôles** (ROLE_USER, ROLE_ADMIN)

### 📱 Interface utilisateur
- **Design Twitter-like** avec layout 3 colonnes
- **Interface responsive** adaptée mobile/desktop
- **Thème moderne** avec couleurs et typographie Twitter
- **Animations fluides** et transitions CSS
- **Notifications flash** pour le feedback utilisateur

### 📝 Publications
- **Création de publications** avec texte, images et vidéos
- **Upload de médias** (images JPG/PNG, vidéos MP4)
- **Détection automatique des hashtags** (#hashtag)
- **Pages dédiées aux hashtags** avec publications liées
- **Système de likes** avec compteurs en temps réel
- **Commentaires et réponses** avec interface intuitive

### 👥 Profils utilisateurs
- **Profils personnalisables** avec avatar et bio
- **Statistiques utilisateur** (publications, followers, following)
- **Historique des publications** avec pagination
- **Édition de profil** avec validation des données
- **Système de follow/unfollow**

### 🔍 Recherche et découverte
- **Recherche globale** (utilisateurs, hashtags, publications)
- **Suggestions personnalisées** basées sur l'activité
- **Algorithme de scoring** intelligent
- **Feed personnalisé** selon les interactions

### 🛡️ Conformité légale
- **Mentions légales** complètes
- **Politique de confidentialité** GDPR
- **Gestion des données personnelles**
- **Droit à l'oubli** (suppression de compte)

## 🛠️ Technologies utilisées

### Backend
- **Symfony 7.3** - Framework PHP moderne
- **Doctrine ORM 3.5** - Gestion de la base de données
- **API Platform** - API REST automatique
- **Symfony Security** - Authentification et autorisation
- **Twig** - Moteur de templates
- **Symfony Forms** - Gestion des formulaires
- **Symfony Validator** - Validation des données

### Frontend
- **Twig Templates** - Templates côté serveur
- **CSS3** - Styles personnalisés Twitter-like
- **JavaScript ES6+** - Interactions dynamiques
- **Stimulus.js** - Contrôleurs JavaScript
- **Asset Mapper** - Gestion des assets

### Base de données
- **MySQL/PostgreSQL** - Base de données relationnelle
- **Doctrine Migrations** - Versioning de la base de données

### Tests
- **PHPUnit** - Tests unitaires et fonctionnels
- **Symfony Browser Kit** - Tests d'intégration

## 📋 Prérequis

- **PHP 8.2+**
- **Composer 2.0+**
- **MySQL 8.0+** ou **PostgreSQL 13+**
- **Node.js 18+** (pour les assets)
- **Symfony CLI** (optionnel mais recommandé)

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone <repository-url>
cd symfony-e
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration de l'environnement
```bash
cp .env .env.local
```

Éditer `.env.local` avec vos paramètres de base de données :
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/symfony_social?serverVersion=8.0"
```

### 4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les fixtures (optionnel)
```bash
php bin/console doctrine:fixtures:load
```

### 6. Compiler les assets
```bash
npm run build
```

### 7. Démarrer le serveur
```bash
symfony server:start
```

L'application est accessible sur `http://localhost:8000`

## ⚙️ Configuration

### Configuration de sécurité
Le fichier `config/packages/security.yaml` contient :
- Configuration des password hashers
- Providers d'authentification
- Firewalls et access control
- Remember me functionality

### Configuration de la base de données
- Migrations dans `migrations/`
- Entités dans `src/Entity/`
- Repositories dans `src/Repository/`

### Configuration des assets
- Assets dans `assets/`
- Configuration dans `importmap.php`
- Styles dans `assets/styles/app.css`

## 🎯 Utilisation

### Inscription et connexion
1. Accéder à `/register` pour créer un compte
2. Se connecter via `/login`
3. Profiter de toutes les fonctionnalités

### Créer une publication
1. Cliquer sur "Nouvelle publication"
2. Rédiger votre contenu avec hashtags
3. Ajouter des images/vidéos (optionnel)
4. Publier

### Interagir avec le contenu
- **Liker** : Cliquer sur le cœur
- **Commenter** : Cliquer sur l'icône commentaire
- **Suivre** : Cliquer sur "Suivre" sur un profil
- **Rechercher** : Utiliser la barre de recherche

### Gérer son profil
1. Aller sur son profil
2. Cliquer sur "Modifier le profil"
3. Mettre à jour les informations
4. Sauvegarder

## 🏗️ Architecture

### Entités principales
- **User** : Utilisateurs avec authentification
- **Publication** : Posts avec médias et hashtags
- **Commentaire** : Commentaires sur les publications
- **Like** : Système de likes
- **Follow** : Relations de suivi entre utilisateurs

### Services
- **SuggestionService** : Algorithme de suggestions personnalisées
- **AppCustomAuthenticator** : Authentification personnalisée

### Contrôleurs
- **HomeController** : Page d'accueil et feed
- **SecurityController** : Authentification
- **RegistrationController** : Inscription
- **ProfileController** : Gestion des profils
- **LikeController** : Gestion des likes
- **LegalController** : Pages légales

### Système de scoring
Le `SuggestionService` calcule un score pour chaque publication basé sur :
- **+5 points** par like reçu
- **+3 points** par commentaire reçu
- **+10 points** si liké par un utilisateur suivi
- **+7 points** par hashtag en commun
- **+15 points** pour interactions récentes

## 🧪 Tests

### Exécuter tous les tests
```bash
php bin/phpunit
```

### Tests unitaires
```bash
php bin/phpunit --testsuite=Unit
```

### Tests fonctionnels
```bash
php bin/phpunit --testsuite=Functional
```

### Tests avec couverture
```bash
php bin/phpunit --coverage-html var/coverage
```

### Structure des tests
- **Unit** : Tests des entités et services
- **Functional** : Tests des contrôleurs et formulaires
- **Fixtures** : Données de test

## 🔌 API

L'API REST est automatiquement générée par API Platform :

### Endpoints disponibles
- `GET /api/users` - Liste des utilisateurs
- `GET /api/publications` - Liste des publications
- `GET /api/commentaires` - Liste des commentaires
- `GET /api/likes` - Liste des likes

### Documentation API
- **Swagger UI** : `/api/docs`
- **JSON-LD** : `/api/contexts/`

### Authentification API
- **JWT** (à configurer)
- **Session** (par défaut)

## 🛡️ Conformité GDPR

### Mesures implémentées
- **Mentions légales** complètes
- **Politique de confidentialité** détaillée
- **Droit à l'oubli** via suppression de compte
- **Consentement explicite** lors de l'inscription
- **Chiffrement des mots de passe**
- **Protection CSRF** sur tous les formulaires

### Pages légales
- `/mentions-legales` - Mentions légales
- `/privacy-policy` - Politique de confidentialité

## 📁 Structure du projet

```
symfony-e/
├── assets/                 # Assets frontend
│   ├── controllers/       # Contrôleurs Stimulus
│   └── styles/           # CSS personnalisé
├── bin/                   # Exécutables Symfony
├── config/               # Configuration
│   ├── packages/         # Configuration des bundles
│   └── routes/           # Configuration des routes
├── migrations/           # Migrations de base de données
├── public/              # Fichiers publics
├── src/                 # Code source
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine
│   ├── Form/            # Formulaires
│   ├── Repository/      # Repositories
│   ├── Security/        # Sécurité
│   └── Service/         # Services métier
├── templates/           # Templates Twig
├── tests/              # Tests
└── var/                # Fichiers temporaires
```

## 🤝 Contribution

### Prérequis pour contribuer
1. Fork du projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit des changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

### Standards de code
- **PSR-12** pour le PHP
- **Symfony Coding Standards**
- **Tests obligatoires** pour les nouvelles fonctionnalités
- **Documentation** des nouvelles APIs

### Workflow de développement
1. **Tests** : Tous les tests doivent passer
2. **Linting** : Code conforme aux standards
3. **Documentation** : Mise à jour du README si nécessaire
4. **Review** : Code review obligatoire


Pour toute question ou problème :
- **Issues GitHub** : Ouvrir une issue sur le repository
- **Documentation** : Consulter la documentation Symfony
- **Tests** : Exécuter les tests pour diagnostiquer les problèmes

---

**Developed by William and Chaima** 