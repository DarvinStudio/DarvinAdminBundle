<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Show;

/**
 * Show view item
 */
class Item
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @param string $label   Label
     * @param mixed  $content Content
     */
    public function __construct(string $label, $content)
    {
        $this->label = $label;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
