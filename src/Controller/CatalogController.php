<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    /** @param array<string,mixed> $shop */
    public function __construct(private readonly array $shop)
    {
    }

    #[Route('/boutique', name: 'app_catalog')]
    public function index(Request $request, ProductRepository $products, CategoryRepository $categories): Response
    {
        // Quels filtres sont activés dans config/shop.yaml ?
        $enabled = $this->shop['filters'] ?? [];

        // Filtres reçus depuis l'URL (cases cochées dans le panneau)
        $filters = [
            'search'     => trim((string) $request->query->get('q', '')),
            'categories' => (array) $request->query->all('categorie'),
            'brands'     => (array) $request->query->all('marque'),
            'colors'     => (array) $request->query->all('couleur'),
            'types'      => (array) $request->query->all('type'),
            'sizes'      => (array) $request->query->all('taille'),
            'featured'   => (bool) $request->query->get('coupdecoeur', false),
            'priceMin'   => $request->query->get('prix_min'),
            'priceMax'   => $request->query->get('prix_max'),
        ];

        // Tri et pagination
        $allowedSorts = ['recent', 'price_asc', 'price_desc', 'name'];
        $sort = $request->query->get('tri', 'recent');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'recent';
        }
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 12;

        $paginator = $products->findByFilters($filters, $sort, $page, $perPage);
        $total = count($paginator);
        $totalPages = (int) ceil($total / $perPage);

        // Valeurs disponibles pour alimenter les boutons (seulement si le filtre est actif)
        $facets = [
            'categories' => ($enabled['category'] ?? false) ? $categories->findAll() : [],
            'brands'     => ($enabled['brand'] ?? false) ? $products->findUsedBrands() : [],
            'colors'     => ($enabled['color'] ?? false) ? $products->findUsedColors() : [],
            'types'      => ($enabled['type'] ?? false) ? $products->findDistinctValues('type') : [],
            'sizes'      => ($enabled['size'] ?? false) ? $products->findDistinctValues('size') : [],
        ];

        return $this->render('catalog/index.html.twig', [
            'products'    => $paginator,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'sort'        => $sort,
            'facets'      => $facets,
            'enabled'     => $enabled,
            'selected'    => $filters,
        ]);
    }

    #[Route('/produit/{slug}', name: 'app_product')]
    public function show(string $slug, ProductRepository $products): Response
    {
        $product = $products->findOneBy(['slug' => $slug, 'active' => true]);

        if (!$product) {
            throw $this->createNotFoundException('Ce produit n\'existe pas ou n\'est plus disponible.');
        }

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
        ]);
    }
}
