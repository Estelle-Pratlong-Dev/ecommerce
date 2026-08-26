# Boutique e-commerce (Symfony 7)

Site e-commerce complet : catalogue, panier, comptes clients, espace d'administration et paiement Stripe.
Entièrement **personnalisable depuis un seul fichier** : [`config/shop.yaml`](config/shop.yaml).

---

## 🎨 Personnaliser la boutique (le truc important !)

**Tout se passe dans un seul fichier : [`config/shop.yaml`](config/shop.yaml)**

Tu peux y changer en quelques secondes :
- le **nom** de la société, le **slogan**, la **description**
- le **logo** (mets une image dans `public/images/` et indique son chemin)
- les **coordonnées** (adresse, e-mail, téléphone)
- les **réseaux sociaux**
- les **couleurs** du site (`primary`, `secondary`, `accent`, fond, texte) et la **police**
- la **devise** et le taux de **TVA**

Après modification, vide le cache si le changement n'apparaît pas :

```bash
php bin/console cache:clear
```

> Les couleurs sont automatiquement transformées en variables CSS (`--color-primary`, etc.)
> et la config est disponible dans tous les templates via `{{ shop.xxx }}`.

---

## 🚀 Démarrer le projet

1. **Démarrer MySQL** (bouton *Start All* dans Laragon).
2. Lancer le serveur :
   ```bash
   symfony server:start
   ```
3. Ouvrir http://127.0.0.1:8000

### (Ré)initialiser la base de données

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de démo (⚠️ vide les tables)
```

---

## 👤 Comptes de démonstration

| Rôle   | E-mail                  | Mot de passe |
|--------|-------------------------|--------------|
| Admin  | admin@maboutique.fr     | admin1234    |
| Client | client@example.com      | client1234   |

- **Espace admin** : http://127.0.0.1:8000/admin (gestion produits, catégories, commandes, clients)
- **Espace client** : http://127.0.0.1:8000/mon-compte (historique des commandes)

---

## 💳 Activer le paiement Stripe

Par défaut, sans clés Stripe, le paiement est **simulé** (la commande passe directement en « payée »).

Pour activer le vrai paiement :
1. Crée un compte sur https://dashboard.stripe.com et récupère tes clés de **test**.
2. Renseigne-les dans `.env.local` :
   ```
   STRIPE_PUBLIC_KEY=pk_test_xxx
   STRIPE_SECRET_KEY=sk_test_xxx
   ```
3. Le bouton « Passer la commande » redirige alors vers la page de paiement Stripe.

> Numéro de carte de test Stripe : `4242 4242 4242 4242`, date future, CVC au hasard.

---

## 🗂️ Structure du projet

```
config/shop.yaml          ← LA configuration de la boutique (à personnaliser)
src/Entity/               ← Modèle de données (User, Category, Product, Order, OrderItem)
src/Controller/           ← Pages publiques (accueil, catalogue, panier, compte, checkout)
src/Controller/Admin/     ← Espace d'administration (EasyAdmin)
src/Service/CartService   ← Gestion du panier (en session)
src/Twig/AppExtension     ← Filtres Twig (prix, devise) + cart_count()
templates/                ← Vues (Twig)
assets/styles/app.css     ← Styles (utilise les variables de couleur de shop.yaml)
```
