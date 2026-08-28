<?php

namespace App\Controller\Admin;

use App\Entity\Promo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PromoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Promo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('code', 'Code')
            ->setHelp('Saisi en majuscules par le client (ex: BIENVENUE10).');
        yield IntegerField::new('percent', 'Réduction (%)')
            ->setHelp('Pourcentage de remise. Laisse vide si tu utilises un montant fixe.');
        yield MoneyField::new('amountCents', 'Réduction (montant fixe)')
            ->setCurrency('EUR')->setStoredAsCents(true)
            ->setHelp('Montant fixe de remise. Ignoré si un pourcentage est renseigné.');
        yield MoneyField::new('minCents', 'Montant minimum de commande')
            ->setCurrency('EUR')->setStoredAsCents(true)
            ->setHelp('Le code ne s\'applique qu\'au-dessus de ce montant (0 = pas de minimum).');
        yield DateTimeField::new('expiresAt', 'Expire le')->hideOnIndex()
            ->setHelp('Laisse vide pour un code sans expiration.');
        yield BooleanField::new('active', 'Actif');
    }
}
