<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield EmailField::new('email', 'E-mail');
        yield TextField::new('phone', 'Téléphone')->hideOnIndex();
        yield TextField::new('address', 'Adresse')->hideOnIndex();
        yield TextField::new('postalCode', 'Code postal')->hideOnIndex();
        yield TextField::new('city', 'Ville')->hideOnIndex();
        yield ArrayField::new('roles', 'Rôles')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Inscription')->onlyOnDetail();
    }
}
