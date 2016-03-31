<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event;

use Darvin\AdminBundle\Metadata\Metadata;
use Symfony\Component\EventDispatcher\Event;

/**
 * CRUD controller action event
 */
class CrudControllerActionEvent extends Event
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $metadata;

    /**
     * @var string
     */
    private $action;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param string                                $action   CRUD controller action
     */
    public function __construct(Metadata $metadata, $action)
    {
        $this->metadata = $metadata;
        $this->action = $action;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
