<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Informations');
        yield TextField::new('name', 'Nom');
        yield SlugField::new('slug', 'Slug (URL / référence)')->setTargetFieldName('name');
        yield AssociationField::new('category', 'Catégorie');
        yield MoneyField::new('priceCents', 'Prix')
            ->setCurrency('EUR')
            ->setStoredAsCents(true);
        yield IntegerField::new('stock', 'Stock');
        yield TextareaField::new('description', 'Description')->hideOnIndex();

        yield FormField::addTab('Caractéristiques');
        yield AssociationField::new('brand', 'Marque')->hideOnIndex();
        yield AssociationField::new('color', 'Couleur')->hideOnIndex();
        yield TextField::new('type', 'Type / matière')->hideOnIndex();
        yield TextField::new('size', 'Taille')->hideOnIndex();

        yield FormField::addTab('Images');
        yield AssociationField::new('images', 'Images')
            ->setHelp('Gère les images dans le menu « Images produits ».')
            ->onlyOnDetail();

        yield FormField::addTab('Options');
        yield BooleanField::new('featured', 'Coup de cœur');
        yield BooleanField::new('active', 'Actif (visible)');

        yield FormField::addTab('Suivi')->onlyOnDetail();
        yield DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail();
        yield AssociationField::new('createdBy', 'Créé par')->onlyOnDetail();
        yield DateTimeField::new('updatedAt', 'Modifié le')->onlyOnDetail();
        yield AssociationField::new('updatedBy', 'Modifié par')->onlyOnDetail();
    }
}
