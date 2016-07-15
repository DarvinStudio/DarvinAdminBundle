<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Menu item factory
 */
class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter                                        $adminRouter          Admin router
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                                 $metadataManager      Metadata manager
     */
    public function __construct(
        AdminRouter $adminRouter,
        AuthorizationCheckerInterface $authorizationChecker,
        MetadataManager $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        foreach ($this->metadataManager->getAllMetadata() as $meta) {
            $config = $meta->getConfiguration();

            if (!$meta->hasParent() && !$config['menu']['skip']) {
                $items[] = $this->createItemFromMetadata($meta);
            }
        }

        return $items;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function createItemFromMetadata(Metadata $meta)
    {
        $config = $meta->getConfiguration();
        $entityClass = $meta->getEntityClass();

        $item = (new Item($meta->getEntityName(), $meta->getBaseTranslationPrefix().'action.index.link'))
            ->setNewTitle($meta->getBaseTranslationPrefix().'action.new.link')
            ->setPosition($config['menu']['position']);

        if ($this->authorizationChecker->isGranted(Permission::VIEW, $entityClass)
            && $this->adminRouter->isRouteExists($entityClass, AdminRouter::TYPE_INDEX)
        ) {
            $item->setIndexUrl($this->adminRouter->generate(null, $entityClass, AdminRouter::TYPE_INDEX));
        }
        if ($this->authorizationChecker->isGranted(Permission::CREATE_DELETE, $entityClass)
            && $this->adminRouter->isRouteExists($entityClass, AdminRouter::TYPE_NEW)
        ) {
            $item->setNewUrl($this->adminRouter->generate(null, $entityClass, AdminRouter::TYPE_NEW));
        }

        return $item;
    }
}
