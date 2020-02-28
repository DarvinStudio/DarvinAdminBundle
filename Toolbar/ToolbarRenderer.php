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

namespace Darvin\AdminBundle\Toolbar;

use Darvin\AdminBundle\Security\User\Roles;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Toolbar renderer
 */
class ToolbarRenderer implements ToolbarRendererInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Twig\Environment                                                            $twig                 Twig
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, Environment $twig)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderToolbar(): ?string
    {
        if (!$this->authorizationChecker->isGranted(Roles::ROLE_ADMIN)) {
            return null;
        }

        return $this->twig->render('@DarvinAdmin/toolbar.html.twig');
    }
}
