<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Show link view widget generator
 */
class ShowLinkGenerator extends AbstractWidgetGenerator
{
    const ALIAS = 'show_link';

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, array $options, $property)
    {
        if (isset($options['property'])) {
            $entity = $this->getPropertyValue($entity, $options['property']);

            if (empty($entity) || !$this->metadataManager->hasMetadata($entity)) {
                return '';
            }
        }

        return $this->adminRouter->isRouteExists($entity, AdminRouter::TYPE_SHOW) && $this->isGranted(Permission::VIEW, $entity)
            ? $this->render($options, [
                'entity'             => $entity,
                'text_link'          => $options['text_link'],
                'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
            ])
            : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'entity_class' => null,
                'text_link'    => false,
            ])
            ->setDefined('property')
            ->setAllowedTypes('property', 'string')
            ->setAllowedTypes('text_link', 'boolean');
    }
}
