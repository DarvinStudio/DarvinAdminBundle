<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Pagination;

/**
 * Pagination manager
 */
interface PaginationManagerInterface
{
    /**
     * @param string $entity Entity class
     *
     * @return int
     */
    public function getItemsPerPage(string $entity): int;

    /**
     * @param string $entity       Entity class
     * @param int    $itemsPerPage Items per page
     */
    public function setItemsPerPage(string $entity, int $itemsPerPage): void;
}
