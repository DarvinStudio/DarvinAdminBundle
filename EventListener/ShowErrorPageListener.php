<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Show error page event listener
 */
class ShowErrorPageListener
{
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
     * @var \Symfony\Component\Translation\TranslatorInterface
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
     * @param \Symfony\Bundle\SecurityBundle\Security\FirewallMap $firewallMap   Firewall map
     * @param \Psr\Log\LoggerInterface                            $logger        Logger
     * @param \Symfony\Component\Routing\RouterInterface          $router        Router
     * @param \Symfony\Component\Templating\EngineInterface       $templating    Templating
     * @param \Symfony\Component\Translation\TranslatorInterface  $translator    Translator
     * @param string                                              $firewallName  Firewall name
     * @param string                                              $homepageRoute Homepage route
     * @param string[]                                            $locales       Locales
     * @param string                                              $defaultLocale Default locale
     */
    public function __construct(
        FirewallMap $firewallMap,
        LoggerInterface $logger,
        RouterInterface $router,
        EngineInterface $templating,
        TranslatorInterface $translator,
        $firewallName,
        $homepageRoute,
        array $locales,
        $defaultLocale
    ) {
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
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event Event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!method_exists($this->firewallMap, 'getFirewallConfig')) {
            return;
        }

        $exception = $event->getException();
        $request = $event->getRequest();

        $config = $this->firewallMap->getFirewallConfig($request);

        if (empty($config) || $config->getName() !== $this->firewallName) {
            return;
        }

        $template = sprintf('DarvinAdminBundle:Error:%d.html.twig', $this->getStatusCode($exception));

        if (!$this->templating->exists($template)) {
            return;
        }

        $this->logger->critical($exception->getMessage());

        $this->configureContexts($request);

        $content = $this->templating->render($template, [
            'referer' => $request->headers->get('referer'),
        ]);

        $event->setResponse(new Response($content));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     */
    private function configureContexts(Request $request)
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
    private function getLocale(Request $request)
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
    private function getStatusCode(\Exception $exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }
        if ($exception instanceof AccessDeniedException) {
            return 403;
        }

        return 500;
    }
}
