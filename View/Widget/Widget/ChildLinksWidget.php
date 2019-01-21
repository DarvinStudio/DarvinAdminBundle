<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\WidgetException;
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
        $childClass = $this->entityResolver->resolve($options['child']);
        $property   = $options['property'];

        $showIndexLink = $this->isGranted(Permission::VIEW, $childClass)
            && $this->adminRouter->exists($childClass, AdminRouterInterface::TYPE_INDEX);
        $showNewLink = $this->isGranted(Permission::CREATE_DELETE, $childClass)
            && $this->adminRouter->exists($childClass, AdminRouterInterface::TYPE_NEW);

        if (!$showIndexLink && !$showNewLink) {
            return null;
        }

        $parentMeta = $this->metadataManager->getMetadata($entity);

        if ($parentMeta->hasChild($childClass)) {
            $childMeta = $parentMeta->getChild($childClass);

            $association      = $childMeta->getAssociation();
            $associationParam = $childMeta->getAssociationParameterName();

            $childMeta = $childMeta->getMetadata();
        } else {
            $childMeta = $this->metadataManager->getMetadata($childClass);
            $mappings  = $parentMeta->getMappings();

            if (!isset($mappings[$property]['mappedBy'])) {
                throw new WidgetException(
                    sprintf('Entity "%s" is not child of entity "%s".', $childClass, $parentMeta->getEntityClass())
                );
            }

            $association = $mappings[$property]['mappedBy'];

            $associationParam = sprintf('%s[%s]', $childMeta->getFilterFormTypeName(), $association);
        }

        $parentId = $this->identifierAccessor->getId($entity);

        $childrenCount = (int)$this->em->getRepository($childClass)->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where(sprintf('o.%s = :%1$s', $association))
            ->setParameter($association, $parentId)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render($options, [
            'association'        => $association,
            'association_param'  => $associationParam,
            'child_class'        => $childClass,
            'children_count'     => $childrenCount,
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
            ->setAllowedTypes('child', 'string');
    }
}
