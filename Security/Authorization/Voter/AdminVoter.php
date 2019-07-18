<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Authorization\Voter;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\Util\ClassUtils;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Admin authorization voter
 */
class AdminVoter extends Voter
{
    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                  $entityResolver  Entity resolver
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     * @param array                                                      $permissions     Permissions
     */
    public function __construct(EntityResolverInterface $entityResolver, AdminMetadataManagerInterface $metadataManager, array $permissions)
    {
        $this->entityResolver = $entityResolver;
        $this->metadataManager = $metadataManager;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $subject = $this->resolveSubject($subject);

        if ($this->metadataManager->hasMetadata($subject)
            && $this->metadataManager->getConfiguration($subject)['oauth_only']
            && !$token instanceof OAuthToken
        ) {
            return false;
        }
        foreach ($token->getRoleNames() as $role) {
            if ($this->isAllowed($role, $subject, $attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, Permission::getAllPermissions());
    }

    /**
     * @param string $role      Role
     * @param string $subject   Subject
     * @param string $attribute Attribute
     *
     * @return bool
     */
    private function isAllowed(string $role, string $subject, string $attribute): bool
    {
        if (!isset($this->permissions[$role])) {
            return false;
        }

        $permissions = $this->permissions[$role];

        if (isset($permissions['subjects'][$subject][$attribute])) {
            return $permissions['subjects'][$subject][$attribute];
        }
        if (class_exists($subject)) {
            foreach (class_parents($subject) as $class) {
                if (isset($permissions['subjects'][$class][$attribute])) {
                    return $permissions['subjects'][$class][$attribute];
                }
            }
        }

        return $permissions['default'][$attribute] ?? false;
    }

    /**
     * @param mixed $subject Subject
     *
     * @return string
     */
    private function resolveSubject($subject): string
    {
        $subject = is_object($subject) ? ClassUtils::getClass($subject) : (string)$subject;

        if (class_exists($subject)) {
            return $this->entityResolver->resolve($subject);
        }

        return $subject;
    }
}
