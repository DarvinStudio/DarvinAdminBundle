<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\ContentBundle\Widget\WidgetException;
use Darvin\ContentBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * CKEditor controller
 */
class CKEditorController extends Controller
{
    /**
     * @param string $widgetName Widget name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function pluginAction($widgetName)
    {
        try {
            $widget = $this->getWidgetPool()->getWidget($widgetName);
        } catch (WidgetException $ex) {
            throw $this->createNotFoundException($ex->getMessage());
        }

        $response = $this->render('DarvinAdminBundle:CKEditor:plugin.js.twig', [
            'icon'   => $this->getWidgetIcon($widget),
            'widget' => $widget,
        ]);
        $response->headers->set('Content-Type', 'application/javascript');

        if (!$this->getParameter('kernel.debug')) {
            $response->setMaxAge(365 * 24 * 60 * 60);
        }

        return $response;
    }

    /**
     * @param \Darvin\ContentBundle\Widget\WidgetInterface $widget Widget
     *
     * @return string
     */
    private function getWidgetIcon(WidgetInterface $widget)
    {
        $options = $widget->getResolvedOptions();

        if (!isset($options['icon']) || empty($options['icon'])) {
            return null;
        }

        $content = @file_get_contents($options['icon']);

        if (!$content) {
            return null;
        }

        $mime = @mime_content_type($options['icon']);

        if (!$mime) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mime, base64_encode($content));
    }

    /**
     * @return \Darvin\ContentBundle\Widget\WidgetPoolInterface
     */
    private function getWidgetPool()
    {
        return $this->get('darvin_content.widget.pool');
    }
}
