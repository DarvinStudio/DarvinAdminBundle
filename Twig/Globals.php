<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * @var string|null
     */
    private $logo;

    /**
     * @var string
     */
    private $projectTitle;

    /**
     * @var string[]
     */
    private $scripts;

    /**
     * @var string[]
     */
    private $styles;

    /**
     * @param string      $frontendPath Frontend path
     * @param string[]    $locales      Available locales
     * @param string|null $logo         Custom logo pathname
     * @param string      $projectTitle Project title
     * @param string[]    $scripts      Script file pathnames
     * @param string[]    $styles       Style file pathnames
     */
    public function __construct(
        string $frontendPath,
        array $locales,
        ?string $logo,
        string $projectTitle,
        array $scripts,
        array $styles
    ) {
        $this->frontendPath = $frontendPath;
        $this->locales = $locales;
        $this->logo = $logo;
        $this->projectTitle = $projectTitle;
        $this->scripts = $scripts;
        $this->styles = $styles;
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
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getProjectTitle(): string
    {
        return $this->projectTitle;
    }

    /**
     * @return string[]
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @return string[]
     */
    public function getStyles(): array
    {
        return $this->styles;
    }
}
