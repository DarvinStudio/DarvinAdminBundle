<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface
     */
    private $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface              $adminRouter        Admin router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface    $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        IdentifierAccessorInterface $identifierAccessor,
        AdminMetadataManagerInterface $metadataManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_breadcrumbs', [$this, 'renderCrumbs'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * @param \Twig\Environment                     $environment  Environment
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object|null                           $parentEntity Parent entity
     * @param string|null                           $heading      Page heading
     *
     * @return string
     */
    public function renderCrumbs(Environment $environment, Metadata $meta, $parentEntity = null, ?string $heading = null): string
    {
        $crumbs = $this->createCrumbs($meta, $parentEntity);
        $config = $meta->getConfiguration();

        if ($config['menu']['group']) {
            $crumbs[] = $this->createCrumb(sprintf('menu.group.%s.title', $config['menu']['group']));
        }

        $crumbs = array_reverse($crumbs);

        if (null !== $heading) {
            $crumbs[] = $this->createCrumb($heading);
        }

        return $environment->render('@DarvinAdmin/breadcrumbs.html.twig', [
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object|null                           $parentEntity Parent entity
     *
     * @return array
     */
    private function createCrumbs(Metadata $meta, $parentEntity = null): array
    {
        $crumbs     = [];
        $indexCrumb = $this->createIndexCrumb($meta, $parentEntity);

        if (null !== $indexCrumb) {
            $crumbs[] = $indexCrumb;
        }
        if (null === $parentEntity) {
            return $crumbs;
        }

        $parentMeta = $this->metadataManager->getMetadata($parentEntity);

        $crumbs = array_merge($crumbs, $this->createEntityCrumbs($parentMeta, $parentEntity));

        $childEntity = $parentEntity;
        $childMeta   = $parentMeta;

        /** @var \Darvin\AdminBundle\Metadata\AssociatedMetadata $parent */
        while ($parent = $childMeta->getParent()) {
            $parentEntity = $this->propertyAccessor->getValue($childEntity, $parent->getAssociation());
            $parentMeta   = $parent->getMetadata();

            $crumbs = array_merge($crumbs, $this->createEntityCrumbs($parentMeta, $parentEntity));

            $childEntity = $parentEntity;
            $childMeta   = $parentMeta;
        }

        return $crumbs;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     * @param object                                $entity Entity
     *
     * @return array
     */
    private function createEntityCrumbs(Metadata $meta, $entity): array
    {
        $url    = null;
        $config = $meta->getConfiguration();

        if (null !== $config['breadcrumbs_route'] && $this->adminRouter->exists($meta->getEntityClass(), $config['breadcrumbs_route'])) {
            $url = $this->adminRouter->generate($entity, $meta->getEntityClass(), $config['breadcrumbs_route']);
        }

        $crumbs     = [$this->createCrumb((string)$entity, $url)];
        $indexCrumb = $this->createIndexCrumb($meta, null, $entity);

        if (null !== $indexCrumb) {
            $crumbs[] = $indexCrumb;
        }

        return $crumbs;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object|null                           $parentEntity Parent entity
     * @param object|null                           $entity       Entity
     *
     * @return array|null
     */
    private function createIndexCrumb(Metadata $meta, $parentEntity = null, $entity = null): ?array
    {
        if (!$this->adminRouter->exists($meta->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            return null;
        }

        $params = [];

        if (null === $entity && null !== $parentEntity) {
            $params[$meta->getParent()->getAssociationParameterName()] = $this->identifierAccessor->getId($parentEntity);
        }

        return $this->createCrumb(
            $meta->getBaseTranslationPrefix().'action.index.link',
            $this->adminRouter->generate($entity, $meta->getEntityClass(), AdminRouterInterface::TYPE_INDEX, $params)
        );
    }

    /**
     * @param string      $title Title
     * @param string|null $url   URL
     *
     * @return array
     */
    private function createCrumb(string $title, ?string $url = null): array
    {
        return [
            'title' => $title,
            'url'   => $url,
        ];
    }
}
