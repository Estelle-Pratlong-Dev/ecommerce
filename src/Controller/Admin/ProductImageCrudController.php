<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image produit')
            ->setEntityLabelInPlural('Images produits')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('product', 'Produit');
        yield ImageField::new('path', 'Image')
            ->setBasePath('uploads/products')
            ->setUploadDir('public/uploads/products')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired($pageName === Crud::PAGE_NEW);
        yield TextField::new('alt', 'Texte alternatif (SEO/accessibilité)')->hideOnIndex();
        yield IntegerField::new('position', 'Ordre');
        yield BooleanField::new('main', 'Image principale');
    }
}
