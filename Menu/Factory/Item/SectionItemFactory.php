<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu\Factory\Item;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Section menu item factory
 */
class SectionItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface                               $adminRouter          Admin router
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Doctrine\ORM\EntityManager                                                  $em                   Entity manager
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface                   $metadataManager      Metadata manager
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManager $em,
        AdminMetadataManagerInterface $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        foreach ($this->metadataManager->getAllMetadata() as $meta) {
            if (!$meta->hasParent() && !$meta->getConfiguration()['menu']['skip']) {
                $item = $this->createItem($meta);

                if (null !== $item) {
                    yield $item;
                }
            }
        }
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\Menu\Item|null
     */
    private function createItem(Metadata $meta): ?Item
    {
        $entityClass = $meta->getEntityClass();

        if (!$meta->getConfiguration()['single_instance']) {
            if (!$this->authorizationChecker->isGranted(Permission::VIEW, $entityClass)
                || !$this->adminRouter->exists($entityClass, AdminRouterInterface::TYPE_INDEX)
            ) {
                return null;
            }

            return $this->instantiateItem($meta)
                ->setIndexTitle(sprintf('%saction.index.link', $meta->getBaseTranslationPrefix()))
                ->setIndexUrl($this->adminRouter->generate(null, $entityClass, AdminRouterInterface::TYPE_INDEX, [], UrlGeneratorInterface::ABSOLUTE_PATH, false));
        }

        $entity = $this->em->getRepository($entityClass)->findOneBy([]);

        if (null !== $entity) {
            if (!$this->authorizationChecker->isGranted(Permission::EDIT, $entity)
                || !$this->adminRouter->exists($entityClass, AdminRouterInterface::TYPE_EDIT)
            ) {
                return null;
            }

            return $this->instantiateItem($meta)
                ->setIndexTitle(sprintf('%saction.edit.link', $meta->getBaseTranslationPrefix()))
                ->setIndexUrl($this->adminRouter->generate($entity, $entityClass, AdminRouterInterface::TYPE_EDIT, [], UrlGeneratorInterface::ABSOLUTE_PATH, false));
        }
        if (!$this->authorizationChecker->isGranted(Permission::CREATE_DELETE, $entityClass)
            || !$this->adminRouter->exists($entityClass, AdminRouterInterface::TYPE_NEW)
        ) {
            return null;
        }

        return $this->instantiateItem($meta)
            ->setIndexTitle(sprintf('%saction.new.link', $meta->getBaseTranslationPrefix()))
            ->setIndexUrl($this->adminRouter->generate(null, $entityClass, AdminRouterInterface::TYPE_NEW, [], UrlGeneratorInterface::ABSOLUTE_PATH, false));
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\Menu\Item
     */
    private function instantiateItem(Metadata $meta): Item
    {
        $item = new Item($meta->getEntityName());
        $item
            ->setAssociatedObject($meta->getEntityClass())
            ->setParentName($meta->getConfiguration()['menu']['group'])
            ->setPosition($meta->getConfiguration()['menu']['position']);

        return $item;
    }
}
