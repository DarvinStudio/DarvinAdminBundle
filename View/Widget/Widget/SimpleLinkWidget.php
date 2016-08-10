<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple link view widget
 */
class SimpleLinkWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        $url = $this->getPropertyValue($entity, isset($options['property']) ? $options['property'] : $property);

        return !empty($url)
            ? $this->render($options, [
                'title' => $url,
                'url'   => $options['add_http_prefix'] && !preg_match('/^https*:\/\//', $url) ? 'http://'.$url : $url,
            ])
            : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('add_http_prefix', false)
            ->setDefined('property')
            ->setAllowedTypes('property', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
