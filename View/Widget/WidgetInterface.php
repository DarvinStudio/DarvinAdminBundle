<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget;

/**
 * View widget
 */
interface WidgetInterface
{
    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return string|null
     */
    public function getContent(object $entity, array $options = []): ?string;

    /**
     * @return string
     */
    public function getAlias(): string;
}
