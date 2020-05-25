<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\CKEditor;

use Darvin\AdminBundle\CKEditor\CKEditorWidgetInterface;
use Darvin\ContentBundle\Widget\WidgetPoolInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * CKEditor plugin controller
 */
class PluginController
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var \Darvin\ContentBundle\Widget\WidgetPoolInterface
     */
    private $widgetPool;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator Translator
     * @param \Twig\Environment                                  $twig       Twig
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface   $widgetPool Widget pool
     * @param bool                                               $debug      Is debug
     */
    public function __construct(TranslatorInterface $translator, Environment $twig, WidgetPoolInterface $widgetPool, bool $debug)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->widgetPool = $widgetPool;
        $this->debug = $debug;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(): Response
    {
        $widgets = [];
        $icons   = [];
        $letters = [];

        foreach ($this->widgetPool->getAllWidgets() as $widget) {
            if ($widget instanceof CKEditorWidgetInterface) {
                $widgets[$widget->getName()] = $widget;
                $icons[$widget->getName()]   = $this->getWidgetIcon($widget);
                $letters[$widget->getName()] = $this->getWidgetLetter($widget);
            }
        }
        if (empty($widgets)) {
            throw new NotFoundHttpException('No CKEditor widgets found.');
        }

        $response = new Response($this->twig->render('@DarvinAdmin/ckeditor/plugin.js.twig', [
            'widgets' => $widgets,
            'icons'   => $icons,
            'letters' => $letters,
        ]));
        $response->headers->set('Content-Type', 'application/javascript');

        if (!$this->debug) {
            $response->setMaxAge(365 * 24 * 60 * 60);
        }

        return $response;
    }

    /**
     * @param \Darvin\AdminBundle\CKEditor\CKEditorWidgetInterface $widget Widget
     *
     * @return string|null
     */
    private function getWidgetIcon(CKEditorWidgetInterface $widget): ?string
    {
        $pathname = $widget->getResolvedOptions()['icon'];

        $content = @file_get_contents($pathname);

        if (false === $content) {
            return null;
        }

        $mime = false;

        if (extension_loaded('fileinfo')) {
            $info = finfo_open(FILEINFO_MIME_TYPE);

            $mime = @finfo_buffer($info, $content);
        }
        if (false === $mime) {
            $extension = preg_replace('/.*\./', '', $pathname);

            if ('' !== $extension) {
                $mime = sprintf('image/%s', $extension);
            }
        }
        if (false === $mime) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mime, base64_encode($content));
    }

    /**
     * @param \Darvin\AdminBundle\CKEditor\CKEditorWidgetInterface $widget Widget
     *
     * @return string|null
     */
    private function getWidgetLetter(CKEditorWidgetInterface $widget): ?string
    {
        $options = $widget->getResolvedOptions();

        if (!$options['show_letter']) {
            return null;
        }

        $source = $options['letter_source'];

        if (null === $source) {
            $source = $this->translator->trans($options['title'], [], 'admin');
        }

        $source = preg_replace('/([^\w]|_)+/u', '', $source);

        if ('' === $source) {
            return null;
        }

        return mb_strtoupper(mb_substr($source, 0, 1));
    }
}
