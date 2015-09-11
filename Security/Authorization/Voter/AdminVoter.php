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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Admin authorization voter
 */
class AdminVoter implements VoterInterface
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
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $objectOrClass = $object;

        if (empty($objectOrClass)) {
            return self::ACCESS_ABSTAIN;
        }

        $class = $this->getClass($objectOrClass);

        if (!$this->supportsClass($class)) {
            return self::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof Administrator) {
            return self::ACCESS_ABSTAIN;
        }
        if ($user->isSuperadmin()) {
            return self::ACCESS_GRANTED;
        }

        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $vote = self::ACCESS_GRANTED;

            if (!$this->isGranted($attribute, $class, $user)) {
                return self::ACCESS_DENIED;
            }
        }

        return $vote;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, Permission::getAll());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $this->init();

        if (in_array($class, $this->supportedClasses)) {
            return true;
        }
        foreach ($this->supportedClasses as $supportedClass) {
            if (is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string                                   $attribute     Attribute
     * @param string                                   $class         Object class
     * @param \Darvin\AdminBundle\Entity\Administrator $administrator Administrator
     *
     * @return bool
     */
    private function isGranted($attribute, $class, Administrator $administrator)
    {
        $this->init();

        if (isset($this->permissions[$class][$administrator->getId()][$attribute])) {
            return $this->permissions[$class][$administrator->getId()][$attribute];
        }

        $defaultPermissions = $administrator->getDefaultPermissions();

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

    /**
     * @param mixed $objectOrClass Object or class
     *
     * @return string
     */
    private function getClass($objectOrClass)
    {
        return is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;
    }
}
