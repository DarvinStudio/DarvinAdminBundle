<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Darvin\AdminBundle\Security\User\Roles;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\Translator;
use Twig\Environment;

/**
 * Show error page event subscriber
 */
class ShowErrorPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $homepageRoute;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Psr\Log\LoggerInterface                                                     $logger               Logger
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     * @param \Twig\Environment                                                            $twig                 Twig
     * @param \Symfony\Component\Translation\Translator                                    $translator           Translator
     * @param string                                                                       $homepageRoute        Homepage route
     * @param string[]                                                                     $locales              Locales
     * @param string                                                                       $defaultLocale        Default locale
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        LoggerInterface $logger,
        RouterInterface $router,
        Environment $twig,
        Translator $translator,
        string $homepageRoute,
        array $locales,
        string $defaultLocale
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->router = $router;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->homepageRoute = $homepageRoute;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'showErrorPage',
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event Event
     */
    public function showErrorPage(ExceptionEvent $event): void
    {
        try {
            $isAdmin = $this->authorizationChecker->isGranted(Roles::ROLE_ADMIN);
        } catch (AuthenticationCredentialsNotFoundException $ex) {
            return;
        }
        if (!$isAdmin) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();

        $this->configureContexts($request);

        if (0 !== strpos($request->getRequestUri(), $this->router->generate($this->homepageRoute))) {
            return;
        }

        $template = sprintf('@DarvinAdmin/error/%d.html.twig', $this->getStatusCode($exception));

        if (!$this->twig->getLoader()->exists($template)) {
            return;
        }

        $this->logger->log(
            $exception instanceof HttpExceptionInterface ? LogLevel::ERROR : LogLevel::CRITICAL,
            $exception->getMessage()
        );

        $content = $this->twig->render($template, [
            'referer' => $request->headers->get('referer'),
        ]);

        $event->setResponse(new Response($content));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     */
    private function configureContexts(Request $request): void
    {
        if ($request->attributes->has('_route')) {
            return;
        }

        $locale = $this->getLocale($request);

        $request->attributes->add([
            '_route'        => $this->homepageRoute,
            '_route_params' => [],
        ]);

        $this->router->getContext()->setParameters([
            '_locale' => $locale,
        ]);

        $this->translator->setLocale($locale);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return string
     */
    private function getLocale(Request $request): string
    {
        $uri = $request->getRequestUri();

        foreach ($this->locales as $locale) {
            if (false !== strpos($uri, '/'.$locale.'/')) {
                return $locale;
            }
        }

        return $this->defaultLocale;
    }

    /**
     * @param \Throwable $exception Exception
     *
     * @return int
     */
    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
