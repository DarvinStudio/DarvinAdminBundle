<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud\Action;

/**
 * CRUD controller action
 */
interface ActionInterface
{
    /**
     * @param \Darvin\AdminBundle\Controller\Crud\Action\ActionConfig $actionConfig Configuration
     */
    public function configure(ActionConfig $actionConfig): void;

    /**
     * @return string
     */
    public function getRunMethod(): string;

    /**
     * @return string
     */
    public function getName(): string;
}
