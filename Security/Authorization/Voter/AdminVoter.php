<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Authorization\Voter;

use Darvin\AdminBundle\Entity\Administrator;
use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

/**
 * Admin authorization voter
 */
class AdminVoter extends AbstractVoter
{
    /**
     * @var \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool
     */
    private $securityConfigurationPool;

    /**
     * @var array
     */
    private $supportedClasses;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool $securityConfigurationPool Security configuration pool
     */
    public function __construct(SecurityConfigurationPool $securityConfigurationPool)
    {
        $this->securityConfigurationPool = $securityConfigurationPool;
        $this->supportedClasses = array();
        $this->permissions = array();
        $this->initialized = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        $this->init();

        return $this->supportedClasses;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return Permission::getAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$user instanceof Administrator) {
            return false;
        }

        $class = ClassUtils::getClass($object);

        if (isset($this->permissions[$class][$user->getId()][$attribute])) {
            return $this->permissions[$class][$user->getId()][$attribute];
        }

        $defaultPermissions = $user->getDefaultPermissions();

        return isset($defaultPermissions[$attribute]) ? $defaultPermissions[$attribute] : false;
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }
        foreach ($this->securityConfigurationPool->getAll() as $configuration) {
            foreach ($configuration->getPermissions() as $objectPermissions) {
                $objectClass = $objectPermissions->getObjectClass();

                $this->supportedClasses[] = $objectClass;

                $this->permissions[$objectClass] = array();

                foreach ($objectPermissions->getAdministratorPermissionsSet() as $administratorPermissions) {
                    $this->permissions[$objectClass][$administratorPermissions->getAdministratorId()]
                        = $administratorPermissions->getPermissions();
                }
            }
        }

        $this->initialized = true;
    }
}
