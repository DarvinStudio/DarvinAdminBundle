<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Form\CacheFormManager;

/**
 * Cache Twig extension
 */
class CacheExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Form\CacheFormManager
     */
    private $cacheFormManager;

    /**
     * @param \Darvin\AdminBundle\Form\CacheFormManager $cacheFormManager Cache form manager
     */
    public function __construct(CacheFormManager $cacheFormManager)
    {
        $this->cacheFormManager = $cacheFormManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'admin_cache_clear_form',
                [$this->cacheFormManager, 'renderClearForm'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
