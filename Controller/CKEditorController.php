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

use Darvin\AdminBundle\CKEditor\AbstractCKEditorWidget;
use Darvin\ContentBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * CKEditor controller
 */
class CKEditorController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function pluginAction()
    {
        $widgets = [];
        $icons   = [];

        foreach ($this->getWidgetPool()->getAllWidgets() as $widget) {
            if ($widget instanceof AbstractCKEditorWidget) {
                $widgets[$widget->getName()] = $widget;
                $icons[$widget->getName()]   = $this->getWidgetIcon($widget);
            }
        }
        if (empty($widgets)) {
            throw $this->createNotFoundException('No CKEditor widgets found.');
        }

        $response = $this->render('DarvinAdminBundle:CKEditor:plugin.js.twig', [
            'widgets' => $widgets,
            'icons'   => $icons,
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

        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = @finfo_buffer($info, $content);

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
