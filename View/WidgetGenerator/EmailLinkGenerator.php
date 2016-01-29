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

use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * Email link view widget generator
 */
class EmailLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, $property, array $options)
    {
        $email = $this->getPropertyValue($entity, isset($options['property']) ? $options['property'] : $property);

        if (empty($email)) {
            return '';
        }

        return $this->render($options, array(
            'email' => $email,
        ));
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
