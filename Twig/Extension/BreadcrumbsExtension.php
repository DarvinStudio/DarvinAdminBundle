<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
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
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $genericRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
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
     * @param \Symfony\Component\Routing\RouterInterface                  $genericRouter      Generic router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor             $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        RouterInterface $genericRouter,
        IdentifierAccessor $identifierAccessor,
        AdminMetadataManagerInterface $metadataManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericRouter = $genericRouter;
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
            $crumbs[] = $this->createCrumb('menu.group.'.$config['menu']['group'].'.title');
        }

        $crumbs[] = $this->createCrumb('homepage.action.homepage.link', $this->genericRouter->generate('darvin_admin_homepage'));

        $crumbs = array_reverse($crumbs);

        if (!empty($heading)) {
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
        $crumbs = [$this->createIndexCrumb($meta, $parentEntity)];

        if (empty($parentEntity)) {
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

        if (!empty($config['breadcrumbs_route'])) {
            $url = $this->adminRouter->generate($entity, $meta->getEntityClass(), $config['breadcrumbs_route']);
        }

        return [
            $this->createCrumb((string)$entity, $url),
            $this->createIndexCrumb($meta, null, $entity),
        ];
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object|null                           $parentEntity Parent entity
     * @param object|null                           $entity       Entity
     *
     * @return array
     */
    private function createIndexCrumb(Metadata $meta, $parentEntity = null, $entity = null): array
    {
        $params = [];

        if (empty($entity) && !empty($parentEntity)) {
            $params[$meta->getParent()->getAssociationParameterName()] = $this->identifierAccessor->getValue($parentEntity);
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
