<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dashboard;

use Darvin\Utils\Strings\StringsUtil;
use Twig\Environment;

/**
 * Dashboard widget abstract implementation
 */
abstract class AbstractDashboardWidget implements DashboardWidgetInterface
{
    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var string|null
     */
    private $name = null;

    /**
     * @param \Twig\Environment $twig Twig
     */
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredPermissions(): iterable
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        if (null === $this->name) {
            $this->name = StringsUtil::toUnderscore(preg_replace('/^.*\\\|Widget$/', '', get_class($this)));
        }

        return $this->name;
    }
}
