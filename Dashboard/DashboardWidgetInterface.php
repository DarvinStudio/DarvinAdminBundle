<?php
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
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getModuleUrl();

    /**
     * @return array Key - object class, value - permissions
     */
    public function getRequiredPermissions();

    /**
     * @return string
     */
    public function getName();
}
