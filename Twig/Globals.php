<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig;

/**
 * Twig Globals
 */
class Globals
{
    /**
     * @var string
     */
    private $frontendPath;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string
     */
    private $projectTitle;

    /**
     * @var string|null
     */
    private $logo;

    /**
     * @var string|null
     */
    private $yandexTranslateApiKey;

    /**
     * @param string      $frontendPath          Frontend path
     * @param string[]    $locales               Available locales
     * @param string      $projectTitle          Project title
     * @param string|null $logo                  Custom logo pathname
     * @param string|null $yandexTranslateApiKey Yandex.Translate API key
     */
    public function __construct(string $frontendPath, array $locales, string $projectTitle, ?string $logo, ?string $yandexTranslateApiKey)
    {
        $this->frontendPath = $frontendPath;
        $this->locales = $locales;
        $this->projectTitle = $projectTitle;
        $this->logo = $logo;
        $this->yandexTranslateApiKey = $yandexTranslateApiKey;
    }

    /**
     * @return string
     */
    public function getFrontendPath(): string
    {
        return $this->frontendPath;
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @return string
     */
    public function getProjectTitle(): string
    {
        return $this->projectTitle;
    }

    /**
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @return string|null
     */
    public function getYandexTranslateApiKey(): ?string
    {
        return $this->yandexTranslateApiKey;
    }
}
