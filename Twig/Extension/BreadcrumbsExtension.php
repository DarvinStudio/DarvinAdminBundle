<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Route\RouteException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Breadcrumbs Twig extension
 */
class BreadcrumbsExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $genericRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter                       $adminRouter      Admin router
     * @param \Symfony\Component\Routing\RouterInterface                  $genericRouter    Generic router
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager  Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(
        AdminRouter $adminRouter,
        RouterInterface $genericRouter,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericRouter = $genericRouter;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'admin_breadcrumbs',
                array($this, 'renderBreadcrumbs'),
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true,
                )
            ),
        );
    }

    /**
     * @param \Twig_Environment                     $environment  Environment
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object                                $parentEntity Parent entity
     * @param object                                $entity       Entity
     * @param bool                                  $renderLast   Whether to render last crumb
     * @param string                                $template     Template
     *
     * @return string
     */
    public function renderBreadcrumbs(
        \Twig_Environment $environment,
        Metadata $meta,
        $parentEntity = null,
        $entity = null,
        $renderLast = false,
        $template = 'DarvinAdminBundle::breadcrumbs.html.twig'
    ) {
        $crumbs = $this->getEntityCrumbs($meta, $parentEntity, $entity);

        $config = $meta->getConfiguration();

        if ($config['menu']['group']) {
            $crumbs[] = array(
                'title' => 'menu.group.'.$config['menu']['group'].'.title',
                'url'   => null,
            );
        }

        $crumbs[] = array(
            'title' => 'homepage.action.homepage.link',
            'url'   => $this->genericRouter->generate('darvin_admin_homepage'),
        );
        $crumbs = array_reverse($crumbs);

        if (!$renderLast) {
            array_pop($crumbs);
        }

        return $environment->render($template, array(
            'crumbs' => $crumbs,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_breadcrumbs_extension';
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta         Metadata
     * @param object                                $parentEntity Parent entity
     * @param object                                $entity       Entity
     *
     * @return array
     */
    private function getEntityCrumbs(Metadata $meta, $parentEntity, $entity)
    {
        $crumbs = array();

        if (empty($entity)) {
            $this->addEntityIndexCrumb($crumbs, $meta);

            if (empty($parentEntity)) {
                return $crumbs;
            }

            $meta = $this->metadataManager->getMetadata($parentEntity);
            $entity = $parentEntity;
        }

        $this->addEntityCrumbs($crumbs, $meta, $entity);

        $childEntity = $entity;
        $childMeta = $meta;

        /** @var \Darvin\AdminBundle\Metadata\AssociatedMetadata $parent */
        while ($parent = $childMeta->getParent()) {
            $parentEntity = $this->propertyAccessor->getValue($childEntity, $parent->getAssociation());
            $parentMeta = $parent->getMetadata();

            $this->addEntityCrumbs($crumbs, $parentMeta, $parentEntity);

            $childEntity = $parentEntity;
            $childMeta = $parentMeta;
        }

        return $crumbs;
    }

    /**
     * @param array                                 $crumbs Breadcrumbs
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     * @param object                                $entity Entity
     */
    private function addEntityCrumbs(array &$crumbs, Metadata $meta, $entity)
    {
        $config = $meta->getConfiguration();

        $crumbs[] = array(
            'title' => (string) $entity,
            'url'   => $this->adminRouter->generate($entity, $meta->getEntityClass(), $config['breadcrumbs_entity_route']),
        );

        $this->addEntityIndexCrumb($crumbs, $meta, $entity);
    }

    /**
     * @param array                                 $crumbs Breadcrumbs
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     * @param object                                $entity Entity
     */
    private function addEntityIndexCrumb(array &$crumbs, Metadata $meta, $entity = null)
    {
        try {
            $url = $this->adminRouter->generate($entity, $meta->getEntityClass(), AdminRouter::TYPE_INDEX);
        } catch (RouteException $ex) {
            $url = null;
        }

        $crumbs[] = array(
            'title' => $meta->getBaseTranslationPrefix().'action.index.link',
            'url'   => $url,
        );
    }
}
