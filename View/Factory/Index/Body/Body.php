<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index\Body;

/**
 * Index view body
 */
class Body
{
    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow[]
     */
    private $rows;

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow[] $rows Rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow $row Row
     */
    public function addRow(BodyRow $row): void
    {
        $this->rows[] = $row;
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return count($this->rows);
    }
}
