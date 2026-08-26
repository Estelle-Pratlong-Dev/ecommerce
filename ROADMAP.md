# 🗺️ Roadmap — Boutique e-commerce Symfony

Suivi de l'avancement du projet. Coche les cases au fur et à mesure (`- [ ]` → `- [x]`).

---

## ✅ Fait

### Socle & configuration
- [x] Projet Symfony 7 (Twig, Doctrine, Security, Forms, Mailer, AssetMapper)
- [x] Base MySQL (Laragon), migrations, données de démo (fixtures)
- [x] **Fichier de configuration centrale** `config/shop.yaml` (nom, slogan, coordonnées, réseaux sociaux, couleurs → variables CSS, devise, TVA, filtres)

### Modèle de données
- [x] Entités : User, Category, Product, ProductImage, Order, OrderItem, Brand, Color
- [x] Champs d'audit automatiques sur toutes les tables (`createdAt`, `updatedAt`, `createdBy`, `updatedBy`)
- [x] Marques et couleurs en **entités** (menus déroulants, valeurs normalisées, pas de saisie libre)

### Boutique (front)
- [x] Accueil (produits phares, catégories)
- [x] Catalogue + **panneau de filtres repliable** (catégorie, marque, couleur avec pastilles, matière, taille, coup de cœur, prix)
- [x] Recherche par mot-clé
- [x] Fiche produit avec galerie multi-images
- [x] Panier (ajout / modification / suppression)

### Comptes & commandes
- [x] Inscription, connexion, déconnexion
- [x] Espace « Mon compte » : historique des commandes + informations personnelles éditables
- [x] Blocage de la commande si adresse / téléphone manquants

### Administration (EasyAdmin)
- [x] Gestion produits, images produits (upload), catégories, marques, couleurs
- [x] Gestion commandes et clients

### Commerce
- [x] Paiement Stripe (+ mode démo sans clés)
- [x] Import de 60 sneakers d'un ancien projet (`php bin/console app:import-sneakers`)

### Divers
- [x] Versionné sur GitHub

---

## 🚧 À faire

### 1. Paiement (avant de vendre pour de vrai)
- [ ] Renseigner les vraies clés **Stripe** dans `.env.local`
- [ ] Mettre en place les **webhooks Stripe** (validation fiable du paiement, pas seulement au retour navigateur)
- [ ] Gérer les **échecs de paiement** et les remboursements

### 2. E-mails (Mailer + Messenger déjà installés)
- [ ] Brancher le **formulaire de contact** (envoi d'e-mail)
- [ ] **E-mail de confirmation** de commande au client
- [ ] E-mail de notification de nouvelle commande à l'administrateur
- [ ] Configurer un vrai transport mail (Mailgun, Brevo, SMTP…) au lieu de `null://null`

### 3. Comptes clients
- [ ] Changement de **mot de passe**
- [ ] **Mot de passe oublié** (réinitialisation par e-mail)
- [ ] (Optionnel) Vérification de l'e-mail à l'inscription

### 4. Design & expérience
- [ ] **Refonte visuelle** (identité plus « e-commerce moderne »)
- [ ] Version mobile soignée
- [ ] Page « À propos », amélioration de la page Contact
- [ ] Fil d'Ariane, tri des produits (prix, nouveautés…)

### 5. Contenu légal (obligatoire avant mise en ligne)
- [ ] **Mentions légales**
- [ ] **Conditions générales de vente (CGV)**
- [ ] Politique de **confidentialité** (RGPD)
- [ ] Bandeau **cookies**

### 6. Mise en production
- [ ] Choix de l'**hébergement** (ex. Platform.sh, o2switch, VPS…)
- [ ] Base de données de **production**
- [ ] **HTTPS** + nom de domaine
- [ ] Variables d'environnement de prod (`APP_ENV=prod`, secrets)
- [ ] Sauvegardes automatiques de la base

### 7. Qualité & robustesse
- [ ] **Tests automatisés** (PHPUnit) sur les parcours clés (panier, commande, sécurité)
- [ ] Gestion fine des stocks (réservation au paiement, alertes rupture)
- [ ] Pagination du catalogue (quand beaucoup de produits)
- [ ] SEO : URLs, balises meta, plan de site (sitemap)

---

## 💡 Idées / plus tard
- [ ] Codes promo / réductions
- [ ] Liste de souhaits (favoris)
- [ ] Avis clients
- [ ] Frais de port selon le pays / le poids
- [ ] Facture PDF téléchargeable
- [ ] Tableau de bord admin avec statistiques de ventes
- [ ] Multi-langue

---

## ℹ️ Rappels techniques
- Démarrer le serveur : `symfony server:start` → http://127.0.0.1:8000
- Recharger les données de démo : `php bin/console doctrine:fixtures:load`
- Réimporter les sneakers : `php bin/console app:import-sneakers`
- Vider le cache : `php bin/console cache:clear`
- Comptes de démo : `admin@maboutique.fr` / `admin1234` — `client@example.com` / `client1234`
