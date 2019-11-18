<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\CKEditor\CKEditorWidgetInterface;
use Darvin\ContentBundle\Widget\Exception\WidgetNotExistsException;
use Darvin\ContentBundle\Widget\WidgetPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CKEditor controller
 */
class CKEditorController extends AbstractController
{
    /**
     * @param string $widgetName Widget name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function pluginAction(string $widgetName): Response
    {
        try {
            $widget = $this->getWidgetPool()->getWidget($widgetName);
        } catch (WidgetNotExistsException $ex) {
            throw $this->createNotFoundException($ex->getMessage());
        }
        if (!$widget instanceof CKEditorWidgetInterface) {
            throw $this->createNotFoundException(
                sprintf('Widget class "%s" must be instance of "%s".', get_class($widget), CKEditorWidgetInterface::class)
            );
        }

        $response = $this->render('@DarvinAdmin/ckeditor/plugin.js.twig', [
            'icon'   => $this->getWidgetIcon($widget),
            'letter' => $this->getWidgetLetter($widget),
            'widget' => $widget,
        ]);
        $response->headers->set('Content-Type', 'application/javascript');

        if (!$this->container->getParameter('kernel.debug')) {
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
            $source = $this->getTranslator()->trans($options['title'], [], 'admin');
        }

        $source = preg_replace('/([^\w]|_)+/u', '', $source);

        if ('' === $source) {
            return null;
        }

        return mb_strtoupper(mb_substr($source, 0, 1));
    }

    /**
     * @return \Symfony\Contracts\Translation\TranslatorInterface
     */
    private function getTranslator(): TranslatorInterface
    {
        return $this->get('translator');
    }

    /**
     * @return \Darvin\ContentBundle\Widget\WidgetPoolInterface
     */
    private function getWidgetPool(): WidgetPoolInterface
    {
        return $this->get('darvin_content.widget.pool');
    }
}
