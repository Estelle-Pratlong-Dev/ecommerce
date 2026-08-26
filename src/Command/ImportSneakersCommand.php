<?php

namespace App\Command;

use App\Catalog\BrandProvider;
use App\Catalog\ColorProvider;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Importe les sneakers d'un ancien projet dans notre schéma.
 * Idempotent : un produit dont le slug existe déjà est ignoré.
 *
 * Usage :  php bin/console app:import-sneakers
 */
#[AsCommand(name: 'app:import-sneakers', description: 'Importe les sneakers de l\'ancien projet')]
class ImportSneakersCommand extends Command
{
    /** Correspondance ancien id de marque -> nom de marque. */
    private const BRANDS = [
        1 => 'Adidas',
        2 => 'Converse',
        3 => 'Jordan',
        4 => 'Nike',
        5 => 'Reebok',
        6 => 'Yeezy',
    ];

    /** Données de l'ancienne table : [brandId, model, sku, taille, couleur, prix]. */
    private const DATA = [
        [1, 'Pw Human race Nmd tr Pharrell', 'ac7359', 8.5, 'noire', 795.00],
        [1, 'Nmd r1 Bape', 'ba7326', 10, 'verte', 1095.00],
        [2, 'All-star 70s hi Play', '150204c', 7, 'noire', 195.00],
        [2, 'Fastbreak hi No Easy Buckets', '161327c', 12, 'verte', 145.00],
        [3, 'Air Jordan 12 retro Gym red', '130690 601', 9.5, 'rouge', 295.00],
        [3, 'Air Jordan 4 retro Bred 2019 release', '308497 060', 7.5, 'noire', 245.00],
        [4, 'SB Dunk Low Nasty Boys', '304292 610', 10.5, 'rouge', 645.00],
        [4, 'Air Max 1 premium Cherrywood', '394805 600', 11, 'violette', 9450.00],
        [5, 'Question mid White Pearlized Red', '79757', 6.5, 'blanche', 235.00],
        [5, 'Kendrick Lamar x Classic Leather Lux Olive', 'bs7465', 5.5, 'verte', 265.00],
        [6, 'Yeezy Boost 350 Oxford tan', 'aq2661', 6, 'beige', 1195.00],
        [6, 'Yeezy Boost 350 Turtle dove', 'aq4832', 9, 'grise', 2295.00],
        [1, 'Naked x UltraBoost 1.0 Waves', 'bb1141', 8, 'bleu', 225.00],
        [1, 'Nmd r1 pk Tri color', 'bb2887', 11.5, 'noire', 250.00],
        [2, 'Chuck 70 OX Think 16', '161408c', 12.5, 'noire', 140.00],
        [2, 'One star Golf ox Golf le fleur', '162126c', 6, 'bleu', 285.00],
        [3, 'Air Jordan 11 retro Concord 2018 release', '378037 100', 8, 'blanche', 335.00],
        [3, 'Air Jordan 6 retro Infrared 2019 release', '384664 060', 9.5, 'noire', 230.00],
        [4, 'Air Max 1 prm Atmos', '512033 003', 11, 'verte', 895.00],
        [4, 'Air Force 1 low NYC hs', '722241 844', 5.5, 'blanche', 445.00],
        [5, 'CL Nylon YG X 4 Hunnid', 'cn2664', 8.5, 'rouge', 210.00],
        [5, 'Question mid CURRENSY Jet Life', 'cn3671', 9, 'blanche', 160.00],
        [6, 'Yeezy Boost 700 Wave runner', 'b75571', 10, 'grise', 410.00],
        [6, 'Yeezy Boost 350 Pirate black', 'bb5350', 11.5, 'noire', 945.00],
        [1, 'UltraBoost ltd Triple black', 'bb4677', 10.5, 'noire', 450.00],
        [1, 'Eqt running guidance King Push', 'd69875', 7.5, 'beige', 650.00],
        [2, 'Chuck 70 hi OFF-WHITE', '162204c', 7, 'blanche', 1545.00],
        [2, 'Comme des garçons Play Chuck 70 high top Multi heart', '162973c', 6.5, 'marron', 345.00],
        [3, 'Air Jordan 1 retro high og Turbo green', '555088 311', 12.5, 'bleu', 205.00],
        [3, 'Air Jordan 3 retro og True blue 2016 release', '854262 106', 12, 'blanche', 290.00],
        [4, 'Kobe 10 elite low prm Htm', '805937 900', 11, 'verte', 500.00],
        [4, 'Nike air vapormax flyknit OG', '849558 006', 8.5, 'grise', 495.00],
        [5, 'Pyer Moss Dmx fusion 1 Experiment', 'cn7586', 11.5, 'noire', 215.00],
        [5, 'Instapump fury og Colette x Lamjc', 'm48252', 10.5, 'grise', 315.00],
        [6, 'Yeezy Boost 350 v2', 'cp9652', 7, 'noire', 1295.00],
        [6, 'Yeezy 500 Blush', 'db2908', 12.5, 'beige', 375.00],
        [1, 'Zx 500 Restomod Goku', 'd97046', 7.5, 'orange', 280.00],
        [1, 'Y-3 Futurecraft Runner 4D Red', 'f99805', 9.5, 'rouge', 880.00],
        [2, 'Chuck 70 low Multi heart', '162976c', 5.5, 'marron', 150.00],
        [2, 'Converse Chuck Taylor All-Star 70s Hi Kith x Coca Cola China', '162985c', 9, 'jaune', 350.00],
        [3, 'Air Jordan 1 retro high og nrg Not for resale', '861428 106', 10, 'noire', 1210.00],
        [3, 'Air Jordan 1 x Off-white nrg UNC', 'aq0818 148', 6.5, 'bleu', 1895.00],
        [4, 'The 10: Nike air presto Off-White', 'aa3830 001', 8, 'noire', 2800.00],
        [4, 'The 10: Nike air max 97 og Off-white', 'aj4585 001', 6, 'noire', 1595.00],
        [5, 'Question Mid White Black', 'm48511', 12, 'blanche', 175.00],
        [5, 'Club C Concepts', 'm48829', 9, 'grise', 170.00],
        [6, 'Yeezy Boost 700 Mauve', 'ee9614', 10.5, 'grise', 310.00],
        [6, 'Teezy Boost 700 v2 Static Wave runner', 'ef2829', 8, 'grise', 630.00],
        [1, 'Superstar 80s luker Neighborhood', 'g17201', 11.5, 'noire', 220.00],
        [1, 'Nmd r1 pk Og 2017 release', 's79168', 8.5, 'noire', 320.00],
        [2, 'Kith x Coca-Cola x Chuck 70 High France', '162988c', 7.5, 'bleu', 475.00],
        [2, 'Chuck 70 hi Golf le fleur Burlap', '163168c', 10, 'beige', 155.00],
        [3, 'Air Jordan 1 high og ts sp Travis Scott', 'cd4487 100', 7.5, 'marron', 1995.00],
        [3, 'Air Jordan 1 high og defiant LA to Chicago', 'cd6578 507', 12.5, 'violette', 845.00],
        [4, 'Nike Air Max 97 CR7 Portugal Patchwork', 'aq0655 600', 6.5, 'rouge', 305.00],
        [4, 'Nike React Element 87', 'aq1090 003', 9.5, 'grise', 260.00],
        [5, 'Court victory Pump felt Alife Ball out', 'm49793', 5.5, 'jaune', 130.00],
        [5, 'BAPE x Ventilator affiliates Camo', 'v63541', 7, 'verte', 240.00],
        [6, 'Yeezy 500 Utility Black', 'f36640', 11, 'noire', 430.00],
        [6, 'Yeezy Boost 350 v2', 'fu9006', 9, 'noire', 460.00],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SluggerInterface $slugger,
        private readonly ColorProvider $colorProvider,
        private readonly BrandProvider $brandProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Catégorie « Sneakers » (réutilisée si elle existe déjà)
        $category = $this->categoryRepository->findOneBy(['slug' => 'sneakers']);
        if (!$category) {
            $category = new Category();
            $category->setName('Sneakers');
            $category->setSlug('sneakers');
            $category->setDescription('Sneakers et éditions limitées importées.');
            $this->em->persist($category);
        }

        $created = 0;
        $skipped = 0;

        foreach (self::DATA as [$brandId, $model, $sku, $size, $color, $price]) {
            $slug = strtolower((string) $this->slugger->slug($model . '-' . $sku));

            if ($this->productRepository->findOneBy(['slug' => $slug])) {
                ++$skipped;
                continue;
            }

            $product = new Product();
            $product->setName($model);
            $product->setSlug($slug);
            $product->setBrand($this->brandProvider->resolve(self::BRANDS[$brandId] ?? null));
            $product->setColor($this->colorProvider->resolve($color));
            $product->setSize($this->formatSize((float) $size));
            $product->setPriceCents((int) round($price * 100));
            $product->setStock(10);
            $product->setActive(true);
            $product->setFeatured(false);
            $product->setCategory($category);

            // 3 photos : img/product/{sku}_1.jpg ... _3.jpg (la 1re est l'image principale)
            for ($n = 1; $n <= 3; ++$n) {
                $image = new ProductImage();
                $image->setPath('img/product/' . $sku . '_' . $n . '.jpg');
                $image->setAlt($model);
                $image->setPosition($n - 1);
                $image->setMain($n === 1);
                $product->addImage($image);
            }

            $this->em->persist($product);
            ++$created;
        }

        $this->em->flush();

        $io->success(sprintf('%d produit(s) importé(s), %d ignoré(s) (déjà présents).', $created, $skipped));
        $io->note('Vérifie que les images sont bien dans public/img/product/ (ex: public/img/product/ac7359_1.jpg).');

        return Command::SUCCESS;
    }

    /** 8.5 -> "8.5", 10.0 -> "10". */
    private function formatSize(float $size): string
    {
        return rtrim(rtrim(number_format($size, 1, '.', ''), '0'), '.');
    }
}
