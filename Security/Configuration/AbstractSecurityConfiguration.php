<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

use Darvin\AdminBundle\Form\Type\Security\Permissions\ObjectPermissionsType;
use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;
use Darvin\UserBundle\Entity\BaseUser;

/**
 * Security configuration abstract implementation
 */
abstract class AbstractSecurityConfiguration extends AbstractConfiguration implements SecurityConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        $defaultValue = array();

        foreach ($this->getSecurableObjectClasses() as $name => $class) {
            $defaultValue[$name] = new ObjectPermissions($class);
        }

        return array(
            new ParameterModel('permissions', ParameterModel::TYPE_ARRAY, $defaultValue, array(
                'form' => array(
                    'options' => array(
                        'type' => ObjectPermissionsType::OBJECT_PERMISSIONS_TYPE_CLASS,
                    ),
                ),
            )),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        return $this->__call(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedRoles()
    {
        return array(
            BaseUser::ROLE_SUPERADMIN,
        );
    }

    /**
     * @return array
     */
    abstract protected function getSecurableObjectClasses();
}
