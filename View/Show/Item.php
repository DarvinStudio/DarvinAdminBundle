<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Show;

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
     * @var string
     */
    private $content;

    /**
     * @param string $label   Label
     * @param string $content Content
     */
    public function __construct($label, $content)
    {
        $this->label = $label;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
