<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

/**
 * CRUD controller action configuration
 */
class ActionConfig
{
    /**
     * @var string|null
     */
    private $entityClass;

    /**
     * @var string|null
     */
    private $template;

    /**
     * @return string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param string|null $entityClass entityClass
     *
     * @return ActionConfig
     */
    public function setEntityClass(?string $entityClass): ActionConfig
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template template
     *
     * @return ActionConfig
     */
    public function setTemplate(?string $template): ActionConfig
    {
        $this->template = $template;

        return $this;
    }
}
