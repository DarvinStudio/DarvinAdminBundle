<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Cookie Twig extension
 */
class CookieExtension extends AbstractExtension
{
    private const COOKIE_VISIBILITY = 'darvin_admin_visibility';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_admin_visible', [$this, 'isVisible']),
        ];
    }

    /**
     * @param string $key     Key
     * @param bool   $default Default visibility
     *
     * @return bool
     */
    public function isVisible(string $key, bool $default = true): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return $default;
        }

        $json = $request->cookies->get(self::COOKIE_VISIBILITY);

        if (!is_string($json)) {
            return $default;
        }

        $visibility = @json_decode($json, true);

        return $visibility[$key] ?? $default;
    }
}
