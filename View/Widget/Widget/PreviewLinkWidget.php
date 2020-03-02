<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\View\Widget\Widget;

/**
 * Preview link view widget
 */
class PreviewLinkWidget extends PublicLinkWidget
{
    public const ALIAS = 'preview_link';

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
