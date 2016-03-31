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

use Symfony\Component\EventDispatcher\Event;

/**
 * CRUD controller action event
 */
class CrudControllerActionEvent extends Event
{
    /**
     * @var string
     */
    private $action;

    /**
     * @param string $action CRUD controller action
     */
    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
