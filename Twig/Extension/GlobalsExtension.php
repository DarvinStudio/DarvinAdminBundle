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

use Darvin\Utils\Strings\StringsUtil;

/**
 * Globals Twig extension
 */
class GlobalsExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $projectTitle;

    /**
     * @var string
     */
    private $projectUrl;

    /**
     * @var string
     */
    private $visualAssetsPath;

    /**
     * @var string
     */
    private $yandexTranslateApiKey;

    /**
     * @var array
     */
    private $globals;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param array  $locales               Available locales
     * @param string $projectTitle          Project title
     * @param string $projectUrl            Project URL
     * @param string $visualAssetsPath      Visual assets path
     * @param string $yandexTranslateApiKey Yandex.Translate API key
     */
    public function __construct(array $locales, $projectTitle, $projectUrl, $visualAssetsPath, $yandexTranslateApiKey)
    {
        $this->locales = $locales;
        $this->projectTitle = $projectTitle;
        $this->projectUrl = $projectUrl;
        $this->visualAssetsPath = $visualAssetsPath;
        $this->yandexTranslateApiKey = $yandexTranslateApiKey;
        $this->globals = [];
        $this->initialized = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        $this->init();

        return $this->globals;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_globals_extension';
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }
        foreach (get_object_vars($this) as $name => $value) {
            $this->globals['darvin_admin_'.StringsUtil::toUnderscore($name)] = $value;
        }

        $this->initialized = true;
    }
}
