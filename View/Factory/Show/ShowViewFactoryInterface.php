<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Show;

use Darvin\AdminBundle\Metadata\Metadata;

/**
 * Show view factory
 */
interface ShowViewFactoryInterface
{
    /**
     * @param object                                $entity Entity
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     *
     * @return \Darvin\AdminBundle\View\Factory\Show\ShowView
     */
    public function createView(object $entity, Metadata $meta): ShowView;
}
