<?php

namespace Oro\Bundle\NavigationBundle\EventListener\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;

class MenuUpdateListener
{
    use ContainerAwareTrait;

    const MENU_CACHE_SERVICE_ID = 'oro_navigation.menu_update.cache';

    /**
     * @param MenuUpdateInterface $update
     * @param LifecycleEventArgs $args
     */
    public function postPersist(MenuUpdateInterface $update, LifecycleEventArgs $args)
    {
        $this->resetAndWarmupResultCache($args->getEntityManager(), $update);
    }

    /**
     * @param MenuUpdateInterface $update
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(MenuUpdateInterface $update, LifecycleEventArgs $args)
    {
        $this->resetAndWarmupResultCache($args->getEntityManager(), $update);
    }

    /**
     * @param MenuUpdateInterface $update
     * @param LifecycleEventArgs $args
     */
    public function postRemove(MenuUpdateInterface $update, LifecycleEventArgs $args)
    {
        $this->resetAndWarmupResultCache($args->getEntityManager(), $update);
    }

    /**
     * @param EntityManagerInterface $em
     * @param MenuUpdateInterface $update
     */
    private function resetAndWarmupResultCache(EntityManagerInterface $em, MenuUpdateInterface $update)
    {
        $this->container->get(self::MENU_CACHE_SERVICE_ID)->delete(
            MenuUpdateUtils::generateKey($update->getMenu(), $update->getScope())
        );

        /** @var MenuUpdateRepository $repository */
        $repository = $em->getRepository($em->getClassMetadata(get_class($update))->getName());
        $repository->findMenuUpdatesByScope($update->getMenu(), $update->getScope());
    }
}
