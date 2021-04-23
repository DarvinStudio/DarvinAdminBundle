<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Push\Provider\Registry;

use Darvin\AdminBundle\Push\Model\Push;

/**
 * Push provider registry
 */
interface PushProviderRegistryInterface
{
    /**
     * @return \Darvin\AdminBundle\Push\Model\Push|null
     */
    public function getLatestPush(): ?Push;
}
