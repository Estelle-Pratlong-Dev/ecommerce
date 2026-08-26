<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Remplit automatiquement les champs d'audit (createdAt/updatedAt + createdBy/updatedBy)
 * sur toute entité qui utilise les traits TimestampableTrait et/ou BlameableTrait.
 *
 * On agit dans onFlush (et on recalcule le changeset) : c'est la manière fiable de
 * modifier des champs juste avant l'écriture en base, y compris pour les mises à jour.
 */
#[AsDoctrineListener(event: Events::onFlush)]
class AuditListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $now = new \DateTimeImmutable();

        $user = $this->security->getUser();
        $user = $user instanceof User ? $user : null;

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
        );

        foreach ($entities as $entity) {
            $isNew = $uow->getEntityState($entity) === \Doctrine\ORM\UnitOfWork::STATE_MANAGED
                && in_array($entity, $uow->getScheduledEntityInsertions(), true);

            $changed = $this->applyTimestamps($entity, $now, $isNew);
            $changed = $this->applyBlame($entity, $user, $isNew) || $changed;

            if ($changed) {
                $metadata = $em->getClassMetadata($entity::class);
                $uow->recomputeSingleEntityChangeSet($metadata, $entity);
            }
        }
    }

    private function applyTimestamps(object $entity, \DateTimeImmutable $now, bool $isNew): bool
    {
        if (!method_exists($entity, 'setUpdatedAt')) {
            return false;
        }

        if ($isNew && $entity->getCreatedAt() === null) {
            $entity->setCreatedAt($now);
        }
        $entity->setUpdatedAt($now);

        return true;
    }

    private function applyBlame(object $entity, ?User $user, bool $isNew): bool
    {
        if ($user === null || !method_exists($entity, 'setUpdatedBy')) {
            return false;
        }

        if ($isNew && $entity->getCreatedBy() === null) {
            $entity->setCreatedBy($user);
        }
        $entity->setUpdatedBy($user);

        return true;
    }
}
