<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * Page controller
 */
class PageController
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var string[]
     */
    private $fallbackLocales;

    /**
     * @param \Twig\Environment $twig            Twig
     * @param string[]          $fallbackLocales Fallback locales
     */
    public function __construct(Environment $twig, array $fallbackLocales)
    {
        $this->twig = $twig;
        $this->fallbackLocales = $fallbackLocales;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param string                                    $slug    Page slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(Request $request, string $slug): Response
    {
        $loader = $this->twig->getLoader();

        foreach (array_merge([$request->getLocale()], $this->fallbackLocales) as $locale) {
            $template = sprintf('@DarvinAdmin/page/%s/%s.html.twig', $slug, $locale);

            if ($loader->exists($template)) {
                return new Response($this->twig->render($template));
            }
        }

        throw new NotFoundHttpException(sprintf('Page "%s" does not exist.', $slug));
    }
}
