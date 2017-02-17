<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Router;

use Symfony\Component\EventDispatcher\Event;

/**
 * Route event
 */
class RouteEvent extends Event
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $params;

    /**
     * @var int
     */
    private $referenceType;

    /**
     * @var object
     */
    private $entity;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param string $name          Route name
     * @param string $type          Route type
     * @param array  $params        Route parameters
     * @param int    $referenceType Reference type
     * @param object $entity        Entity
     * @param string $entityClass   Entity class
     */
    public function __construct($name, $type, array $params, $referenceType, $entity, $entityClass)
    {
        $this->name = $name;
        $this->type = $type;
        $this->params = $params;
        $this->referenceType = $referenceType;
        $this->entity = $entity;
        $this->entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param array $params params
     *
     * @return RouteEvent
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param int $referenceType referenceType
     *
     * @return RouteEvent
     */
    public function setReferenceType($referenceType)
    {
        $this->referenceType = $referenceType;

        return $this;
    }

    /**
     * @return int
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }
}
