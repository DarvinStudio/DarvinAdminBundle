<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple link view widget generator
 */
class SimpleLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, array $options, $property)
    {
        $url = $this->getPropertyValue($entity, isset($options['property']) ? $options['property'] : $property);

        return !empty($url)
            ? $this->render($options, array(
                'url' => $url,
            ))
            : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefined('property')
            ->setAllowedTypes('property', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return array(
            Permission::VIEW,
        );
    }
}
