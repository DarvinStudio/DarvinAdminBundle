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
     * @param string $entityClass Entity class
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getItemsPerPage(string $entityClass): int;

    /**
     * @param string $entityClass  Entity class
     * @param int    $itemsPerPage Items per page
     *
     * @throws \InvalidArgumentException
     */
    public function setItemsPerPage(string $entityClass, int $itemsPerPage): void;
}
