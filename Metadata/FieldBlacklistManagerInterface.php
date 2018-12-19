<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

/**
 * Field blacklist manager
 */
interface FieldBlacklistManagerInterface
{
    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta       Metadata
     * @param string                                $field      Field
     * @param string|null                           $configPath Config path
     *
     * @return bool
     */
    public function isFieldBlacklisted(Metadata $meta, string $field, ?string $configPath = null): bool;
}
