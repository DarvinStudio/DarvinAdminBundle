<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Child links view widget
 */
class ChildLinksWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface
     */
    private $identifierAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface           $adminRouter        Admin router
     * @param \Doctrine\ORM\EntityManager                              $em                 Entity manager
     * @param \Darvin\Utils\ORM\EntityResolverInterface                $entityResolver     Entity resolver
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface $identifierAccessor Identifier accessor
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        EntityManager $em,
        EntityResolverInterface $entityResolver,
        IdentifierAccessorInterface $identifierAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->em = $em;
        $this->entityResolver = $entityResolver;
        $this->identifierAccessor = $identifierAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $child    = $this->entityResolver->resolve($options['child']);
        $property = $options['property'];

        $showIndexLink = $this->isGranted(Permission::VIEW, $child)
            && $this->adminRouter->exists($child, AdminRouterInterface::TYPE_INDEX);
        $showNewLink = $this->isGranted(Permission::CREATE_DELETE, $child)
            && $this->adminRouter->exists($child, AdminRouterInterface::TYPE_NEW);

        if (!$showIndexLink && !$showNewLink) {
            return null;
        }

        $parentMeta = $this->metadataManager->getMetadata($entity);

        if ($parentMeta->hasChild($child)) {
            $childMeta = $parentMeta->getChild($child);

            $association      = $childMeta->getAssociation();
            $associationParam = $childMeta->getAssociationParameterName();

            $childMeta = $childMeta->getMetadata();
        } else {
            $childMeta = $this->metadataManager->getMetadata($child);
            $mappings  = $parentMeta->getMappings();

            if (!isset($mappings[$property]['mappedBy'])) {
                throw new \InvalidArgumentException(
                    sprintf('Entity "%s" is not child of entity "%s".', $child, $parentMeta->getEntityClass())
                );
            }

            $association = $mappings[$property]['mappedBy'];

            $associationParam = sprintf('%s[%s]', $childMeta->getFilterFormTypeName(), $association);
        }

        $count    = null;
        $parentId = $this->identifierAccessor->getId($entity);

        if ($showIndexLink && $options['show_count']) {
            $count = (int)$this->em->getRepository($child)->createQueryBuilder('o')
                ->select('COUNT(o)')
                ->where(sprintf('o.%s = :%1$s', $association))
                ->setParameter($association, $parentId)
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->render([
            'association'        => $association,
            'association_param'  => $associationParam,
            'child'              => $child,
            'count'              => $count,
            'parent_id'          => $parentId,
            'show_index_link'    => $showIndexLink,
            'show_new_link'      => $showNewLink,
            'translation_prefix' => $childMeta->getBaseTranslationPrefix(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('child')
            ->setDefault('show_count', false)
            ->setAllowedTypes('child', 'string')
            ->setAllowedTypes('show_count', 'boolean');
    }
}
