<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Push\Provider\Registry;

use Darvin\AdminBundle\Push\Model\Push;
use Darvin\AdminBundle\Push\Provider\PushProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Push provider registry
 */
class PushProviderRegistry implements PushProviderRegistryInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Push\Provider\PushProviderInterface[]
     */
    private $providers;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

        $this->providers = [];
    }

    /**
     * @param \Darvin\AdminBundle\Push\Provider\PushProviderInterface $provider Push provider
     */
    public function register(PushProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getLatestPush(): ?Push
    {
        /** @var \Darvin\AdminBundle\Push\Model\Push|null $latest */
        $latest = null;

        foreach ($this->providers as $provider) {
            if (!$this->isAllowed($provider)) {
                continue;
            }
            foreach ($provider->providePushes() as $push) {
                if (null === $latest || $push->getDate() > $latest->getDate()) {
                    $latest = $push;
                }
            }
        }

        return $latest;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->providers);
    }

    /**
     * @param \Darvin\AdminBundle\Push\Provider\PushProviderInterface $provider Push provider
     *
     * @return bool
     */
    private function isAllowed(PushProviderInterface $provider): bool
    {
        foreach ($provider->getRequiredPermissions() as $subject => $attribute) {
            try {
                if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
                    return false;
                }
            } catch (AuthenticationCredentialsNotFoundException $ex) {
                return false;
            }
        }

        return true;
    }
}
