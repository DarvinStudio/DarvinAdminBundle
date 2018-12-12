<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Search;

use Darvin\AdminBundle\Metadata\Metadata;

/**
 * Searcher
 */
interface SearcherInterface
{
    /**
     * @param string $entityName Entity name
     * @param string $query      Search query
     *
     * @return object[]
     * @throws \RuntimeException
     */
    public function search(string $entityName, string $query): array;

    /**
     * @param string $entityName Entity name
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \InvalidArgumentException
     */
    public function getSearchableEntityMeta(string $entityName): Metadata;

    /**
     * @return string[]
     */
    public function getSearchableEntityNames(): array;

    /**
     * @param string $entityName Entity name
     *
     * @return bool
     */
    public function isSearchable(string $entityName): bool;
}
