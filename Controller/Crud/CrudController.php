<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Controller\Crud\Action\ActionConfig;
use Darvin\AdminBundle\Controller\Crud\Action\ActionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller
 */
class CrudController
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var \Darvin\AdminBundle\Controller\Crud\Action\ActionInterface[]
     */
    private $actions;

    /**
     * @param string $entityClass Entity class
     */
    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;

        $this->actions = [];
    }

    /**
     * @param string $name Action name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __invoke(string $name): Response
    {
        if (!isset($this->actions[$name])) {
            throw new \InvalidArgumentException(sprintf('CRUD action "%s" does not exist.', $name));
        }

        $action = $this->actions[$name];

        if (!is_callable($action)) {
            throw new \LogicException(sprintf('CRUD action class "%s" is not callable.', get_class($action)));
        }

        $action->configure(new ActionConfig($this->entityClass));

        return $action();
    }

    /**
     * @param \Darvin\AdminBundle\Controller\Crud\Action\ActionInterface $action Action
     */
    public function addAction(ActionInterface $action): void
    {
        $this->actions[$action->getName()] = $action;
    }
}
