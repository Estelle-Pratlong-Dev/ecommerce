<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Repository\SettingRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class SettingCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Setting::class;
    }

    /** Ligne unique : on va directement sur le formulaire d'édition. */
    public function index(AdminContext $context): KeyValueStore|Response
    {
        $setting = $this->settingRepository->getSettings();

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($setting->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Livraison')
            ->setEntityLabelInPlural('Livraison');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Ligne unique : pas de création ni de suppression, uniquement l'édition.
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield MoneyField::new('shippingFlatCents', 'Forfait de livraison')
            ->setCurrency('EUR')->setStoredAsCents(true)
            ->setHelp('Frais de port appliqués à une commande.');
        yield MoneyField::new('shippingFreeFromCents', 'Livraison offerte à partir de')
            ->setCurrency('EUR')->setStoredAsCents(true)
            ->setHelp('Montant d\'achat au-delà duquel la livraison est gratuite (0 = jamais).');
    }
}
