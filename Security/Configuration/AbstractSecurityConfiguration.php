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
use Darvin\ConfigBundle\Configuration\AbstractConfiguration;

/**
 * Security configuration abstract implementation
 */
abstract class AbstractSecurityConfiguration extends AbstractConfiguration implements SecurityConfigurationInterface
{
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
}
