<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Darvin\AdminBundle\Event\Router\RouteEvent;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Preserve filter data in URL event listener
 */
class PreserveFilterInUrlListener
{
    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager   $metadataManager Admin metadata manager
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack    Request stack
     */
    public function __construct(MetadataManager $metadataManager, RequestStack $requestStack)
    {
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @param \Darvin\AdminBundle\Event\Router\RouteEvent $event Event
     */
    public function preRouteGenerate(RouteEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return;
        }

        $routeParams = $event->getParams();

        $param = $this->metadataManager->getMetadata($event->getEntityClass())->getFilterFormTypeName();

        if ((isset($routeParams[$param]) && is_array($routeParams[$param])) || !$request->query->has($param)) {
            return;
        }

        $filterData = $request->query->get($param);

        if (!is_array($filterData)) {
            return;
        }

        $routeParams[$param] = $filterData;

        $event->setParams($routeParams);
    }
}
