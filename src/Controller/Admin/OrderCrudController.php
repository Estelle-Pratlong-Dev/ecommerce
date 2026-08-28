<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\OrderMailer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public function __construct(private readonly OrderMailer $orderMailer)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    /**
     * Envoie l'e-mail d'expédition quand le statut passe à « Expédiée ».
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $justShipped = false;

        if ($entityInstance instanceof Order) {
            $original = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            $previousStatus = $original['status'] ?? null;
            $justShipped = $previousStatus !== Order::STATUS_SHIPPED
                && $entityInstance->getStatus() === Order::STATUS_SHIPPED;
        }

        parent::updateEntity($entityManager, $entityInstance);

        if ($justShipped) {
            $this->orderMailer->sendOrderShipped($entityInstance);
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Référence')->setDisabled();
        yield AssociationField::new('customer', 'Client');
        yield DateTimeField::new('createdAt', 'Date')->setDisabled();
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(array_flip(Order::STATUS_LABELS));
        yield MoneyField::new('totalCents', 'Total')
            ->setCurrency('EUR')
            ->setStoredAsCents(true)
            ->onlyOnIndex();
        yield AssociationField::new('items', 'Articles')->onlyOnDetail();
    }
}
