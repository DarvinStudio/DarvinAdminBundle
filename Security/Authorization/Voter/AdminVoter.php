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
use Darvin\UserBundle\Entity\BaseUser;
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
     * @param \Darvin\Utils\ORM\EntityResolverInterface                  $entityResolver  Entity resolver
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     */
    public function __construct(EntityResolverInterface $entityResolver, AdminMetadataManagerInterface $metadataManager)
    {
        $this->entityResolver = $entityResolver;
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof BaseUser) {
            return false;
        }
        if ($this->metadataManager->hasMetadata($subject)
            && $this->metadataManager->getConfiguration($subject)['oauth_only']
            && !$token instanceof OAuthToken
        ) {
            return false;
        }

        $class = $this->getClass($subject);

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
     * @param object|string $object Object
     *
     * @return string
     */
    private function getClass($object)
    {
        return $this->entityResolver->resolve(is_object($object) ? ClassUtils::getClass($object) : (string)$object);
    }
}
