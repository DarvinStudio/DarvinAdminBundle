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

/**
 * Push provider registry
 */
class PushProviderRegistry implements PushProviderRegistryInterface
{
    /**
     * @var \Darvin\AdminBundle\Push\Provider\PushProviderInterface[]
     */
    private $providers;

    /**
     * Push provider registry constructor.
     */
    public function __construct()
    {
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
            foreach ($provider->providePushes() as $push) {
                if (null === $latest || $push->getDate() > $latest->getDate()) {
                    $latest = $push;
                }
            }
        }

        return $latest;
    }
}
