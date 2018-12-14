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
use Symfony\Component\HttpFoundation\Request;
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
     * @param \Darvin\AdminBundle\Controller\Crud\Action\ActionInterface $action Action
     */
    public function addAction(ActionInterface $action): void
    {
        $this->actions[$action->getName()] = $action;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param bool                                      $widget  Is widget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, bool $widget = false): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copy(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request  Request
     * @param int                                       $id       Entity ID
     * @param string                                    $property Property to update
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateProperty(Request $request, $id, string $property): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function batchDelete(Request $request): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name Name
     * @param array  $args Arguments
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    private function action(string $name, array $args): Response
    {
        if (!isset($this->actions[$name])) {
            throw new \InvalidArgumentException(sprintf('CRUD action "%s" does not exist.', $name));
        }

        $action = $this->actions[$name];
        $action->configure(new ActionConfig($this->entityClass));

        return $action->{$action->getRunMethod()}(...$args);
    }
}
