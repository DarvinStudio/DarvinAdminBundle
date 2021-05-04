<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Push\Model;

/**
 * Push
 */
class Push implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @param string      $id   ID
     * @param string      $type Type
     * @param string      $text Text
     * @param \DateTime   $date Date
     * @param string|null $url  URL
     */
    public function __construct(string $id, string $type, string $text, \DateTime $date, ?string $url = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->text = $text;
        $this->date = $date;
        $this->url = $url;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'id'   => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'url'  => $this->url,
        ];
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
