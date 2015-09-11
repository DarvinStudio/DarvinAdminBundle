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

use Darvin\AdminBundle\Entity\Administrator;
use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;

/**
 * Security configuration
 */
class SecurityConfiguration extends AbstractConfiguration implements SecurityConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return array(
            new ParameterModel(
                'permissions',
                ParameterModel::TYPE_ARRAY,
                array(
                    'administrator' => new ObjectPermissions(Administrator::ADMINISTRATOR_CLASS),
                    'log_entry'     => new ObjectPermissions(LogEntry::LOG_ENTRY_CLASS),
                ),
                array(
                    'form' => array(
                        'options' => array(
                            'type' => 'darvin_admin_security_object_permissions',
                        ),
                    ),
                )
            ),
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
            Administrator::ROLE_SUPERADMIN,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_security';
    }
}
