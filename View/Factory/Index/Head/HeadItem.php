<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index\Head;

/**
 * Index view head item
 */
class HeadItem
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $attr;

    /**
     * @var bool
     */
    private $sortable;

    /**
     * @var string|null
     */
    private $sortablePropertyPath;

    /**
     * @var int
     */
    private $width;

    /**
     * @param string $content              Content
     * @param array  $attr                 HTML attributes
     * @param bool   $sortable             Is sortable
     * @param string $sortablePropertyPath Sortable property path
     * @param int    $width                Width
     */
    public function __construct(string $content, array $attr = [], bool $sortable = false, ?string $sortablePropertyPath = null, int $width = 1)
    {
        $this->content = $content;
        $this->attr = $attr;
        $this->sortable = $sortable;
        $this->width = $width;

        $this->setSortablePropertyPath($sortablePropertyPath);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return array_merge([
            'colspan' => $this->width,
        ], $this->attr);
    }

    /**
     * @param bool $sortable sortable
     */
    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @param string|null $sortablePropertyPath sortablePropertyPath
     */
    public function setSortablePropertyPath(?string $sortablePropertyPath): void
    {
        if (null !== $sortablePropertyPath && false === strpos($sortablePropertyPath, '.')) {
            $sortablePropertyPath = 'o.'.$sortablePropertyPath;
        }

        $this->sortablePropertyPath = $sortablePropertyPath;
    }

    /**
     * @return string|null
     */
    public function getSortablePropertyPath(): ?string
    {
        return $this->sortablePropertyPath;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }
}
