<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Darvin\AdminBundle\Security\User\Roles;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;

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
     * @var \Symfony\Bundle\SecurityBundle\Security\FirewallMap
     */
    private $firewallMap;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $firewallName;

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
     * @param \Symfony\Bundle\SecurityBundle\Security\FirewallMap                          $firewallMap          Firewall map
     * @param \Psr\Log\LoggerInterface                                                     $logger               Logger
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     * @param \Symfony\Component\Templating\EngineInterface                                $templating           Templating
     * @param \Symfony\Component\Translation\Translator                                    $translator           Translator
     * @param string                                                                       $firewallName         Firewall name
     * @param string                                                                       $homepageRoute        Homepage route
     * @param string[]                                                                     $locales              Locales
     * @param string                                                                       $defaultLocale        Default locale
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FirewallMap $firewallMap,
        LoggerInterface $logger,
        RouterInterface $router,
        EngineInterface $templating,
        Translator $translator,
        string $firewallName,
        string $homepageRoute,
        array $locales,
        string $defaultLocale
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->firewallMap = $firewallMap;
        $this->logger = $logger;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->firewallName = $firewallName;
        $this->homepageRoute = $homepageRoute;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
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
        if (!method_exists($this->firewallMap, 'getFirewallConfig')) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();

        $config = $this->firewallMap->getFirewallConfig($request);

        if (empty($config) || $config->getName() !== $this->firewallName) {
            return;
        }

        $template = sprintf('@DarvinAdmin/error/%d.html.twig', $this->getStatusCode($exception));

        if (!$this->templating->exists($template)) {
            return;
        }
        if (!$this->authorizationChecker->isGranted(Roles::ROLE_ADMIN)) {
            return;
        }

        $this->logger->log(
            $exception instanceof HttpExceptionInterface ? LogLevel::ERROR : LogLevel::CRITICAL,
            $exception->getMessage()
        );

        $this->configureContexts($request);

        $content = $this->templating->render($template, [
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
     * @param \Exception $exception Exception
     *
     * @return int
     */
    private function getStatusCode(\Exception $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
