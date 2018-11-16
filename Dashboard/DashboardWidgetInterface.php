<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dashboard;

/**
 * Dashboard widget
 */
interface DashboardWidgetInterface
{
    /**
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * @return string|null
     */
    public function getModuleUrl(): ?string;

    /**
     * @return array Key - object class, value - permissions
     */
    public function getRequiredPermissions(): array;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getIcon(): ?string;
}
