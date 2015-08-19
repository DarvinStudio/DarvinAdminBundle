<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Stringifier;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Stringifier
 */
class Stringifier
{
    /**
     * @var array
     */
    private static $datetimeFormats = array(
        Type::DATE       => 'd.m.Y',
        Type::DATETIME   => 'd.m.Y H:i:s',
        Type::DATETIMETZ => 'd.m.Y H:i:s',
        Type::TIME       => 'H:i:s',
    );

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param mixed  $value        Value to stringify
     * @param string $doctrineType Doctrine data type
     *
     * @return string
     */
    public function stringify($value, $doctrineType)
    {
        switch ($doctrineType) {
            case Type::BIGINT:
            case Type::BLOB:
            case Type::DECIMAL:
            case Type::FLOAT:
            case Type::GUID:
            case Type::INTEGER:
            case Type::SMALLINT:
            case Type::STRING:
            case Type::TEXT:
                return $value;

            case Type::BOOLEAN:
                return $this->stringifyBoolean($value);

            case Type::DATE:
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::TIME:
                return $this->stringifyDatetime($value, self::$datetimeFormats[$doctrineType]);

            case Type::JSON_ARRAY:
            case Type::SIMPLE_ARRAY:
            case Type::TARRAY:
                return $this->stringifyArray($value);

            case Type::OBJECT:
                return $this->stringifyObject($value);

            default:
                return '';
        }
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyArray($value)
    {
        return is_array($value) ? json_encode($value) : '';
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyBoolean($value)
    {
        return $this->translator->trans(sprintf('boolean.%d', $value), array(), 'admin');
    }

    /**
     * @param mixed  $value  Value to stringify
     * @param string $format Datetime format
     *
     * @return string
     */
    private function stringifyDatetime($value, $format)
    {
        return $value instanceof \DateTime ? $value->format($format) : '';
    }

    /**
     * @param mixed $value Value to stringify
     *
     * @return string
     */
    private function stringifyObject($value)
    {
        return is_object($value) ? serialize($value) : '';
    }
}
