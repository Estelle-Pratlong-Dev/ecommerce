<?php

namespace App\DataFixtures;

use App\Catalog\BrandProvider;
use App\Catalog\ColorProvider;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Promo;
use App\Entity\Setting;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Données de démonstration.
 * Lance :  php bin/console doctrine:fixtures:load
 * (⚠️ vide les tables avant de recharger)
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger,
        private readonly ColorProvider $colorProvider,
        private readonly BrandProvider $brandProvider,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Couleurs et marques de référence — réutilisées par les produits
        $this->colorProvider->seedCanonical();
        $this->brandProvider->seedCanonical();

        // --- Compte administrateur ---
        $admin = new User();
        $admin->setEmail('admin@maboutique.fr');
        $admin->setFirstName('Estelle');
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin1234'));
        $manager->persist($admin);

        // --- Compte client de test ---
        $client = new User();
        $client->setEmail('client@example.com');
        $client->setFirstName('Jean');
        $client->setLastName('Dupont');
        $client->setAddress('12 rue des Lilas');
        $client->setPostalCode('30160');
        $client->setCity('Gagnières');
        $client->setPhone('+33 6 12 34 56 78');
        $client->setPassword($this->hasher->hashPassword($client, 'client1234'));
        $manager->persist($client);

        // --- Catégories ---
        $categoriesData = ['Vêtements', 'Accessoires', 'Maison', 'High-Tech'];
        $categories = [];
        foreach ($categoriesData as $name) {
            $cat = new Category();
            $cat->setName($name);
            $cat->setSlug(strtolower($this->slugger->slug($name)));
            $cat->setDescription('Notre sélection : ' . $name);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // --- Produits : [nom, prix centimes, stock, index catégorie, coup de cœur, marque, couleur, type, taille] ---
        $productsData = [
            ['T-shirt en coton bio', 2490, 50, 0, true,  'EcoWear',  'Blanc', 'Coton bio', 'M'],
            ['Sweat à capuche', 4990, 30, 0, true,  'EcoWear',  'Noir',  'Coton molletonné', 'L'],
            ['Casquette brodée', 1990, 100, 1, false, 'CapCo',    'Beige', 'Coton', 'Unique'],
            ['Tote bag', 1290, 80, 1, true,  'EcoWear',  'Écru',  'Toile de jute', 'Unique'],
            ['Mug céramique', 1490, 60, 2, false, 'MaisonPlus', 'Blanc', 'Céramique', '35 cl'],
            ['Bougie parfumée', 1990, 40, 2, true,  'MaisonPlus', 'Ambre', 'Cire végétale', '200 g'],
            ['Écouteurs sans fil', 5990, 25, 3, true,  'SonicTech', 'Noir',  'Plastique', 'Unique'],
            ['Chargeur rapide USB-C', 2290, 70, 3, false, 'SonicTech', 'Blanc', 'Plastique', 'Unique'],
        ];

        foreach ($productsData as [$name, $priceCents, $stock, $catIndex, $featured, $brand, $color, $type, $size]) {
            $product = new Product();
            $product->setName($name);
            $product->setSlug(strtolower($this->slugger->slug($name)));
            $product->setDescription('Description du produit « ' . $name . ' ». Un excellent choix pour la qualité et le prix.');
            $product->setPriceCents($priceCents);
            $product->setStock($stock);
            $product->setCategory($categories[$catIndex]);
            $product->setFeatured($featured);
            $product->setActive(true);
            $product->setBrand($this->brandProvider->resolve($brand));
            $product->setColor($this->colorProvider->resolve($color));
            $product->setType($type);
            $product->setSize($size);
            $manager->persist($product);
        }

        // --- Paramètres livraison (forfait 4,90 € / offerte dès 60 €) ---
        $settings = new Setting();
        $settings->setShippingFlatCents(490);
        $settings->setShippingFreeFromCents(6000);
        $manager->persist($settings);

        // --- Codes promo de démo ---
        $promo1 = new Promo();
        $promo1->setCode('BIENVENUE10');
        $promo1->setPercent(10);
        $manager->persist($promo1);

        $promo2 = new Promo();
        $promo2->setCode('MOINS5');
        $promo2->setAmountCents(500);   // -5 €
        $promo2->setMinCents(3000);     // dès 30 € d'achat
        $manager->persist($promo2);

        $manager->flush();
    }
}
