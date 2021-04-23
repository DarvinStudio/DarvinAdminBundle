<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Push;

use Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Latest push controller
 */
class LatestController
{
    /**
     * @var \Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface
     */
    private $pushProviderRegistry;

    /**
     * @param \Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface $pushProviderRegistry Push provider registry
     */
    public function __construct(PushProviderRegistryInterface $pushProviderRegistry)
    {
        $this->pushProviderRegistry = $pushProviderRegistry;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        return new JsonResponse($this->pushProviderRegistry->getLatestPush());
    }
}
