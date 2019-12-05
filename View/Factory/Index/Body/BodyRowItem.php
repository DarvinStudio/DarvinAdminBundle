<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index\Body;

/**
 * Index view body row item
 */
class BodyRowItem
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
     * @param mixed|null $content Content
     * @param array      $attr    HTML attributes
     */
    public function __construct($content = null, array $attr = [])
    {
        $this->content = trim((string)$content);
        $this->attr = $attr;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return $this->attr;
    }
}
