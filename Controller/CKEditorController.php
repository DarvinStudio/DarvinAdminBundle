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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

        $content = $this->renderView('DarvinAdminBundle:CKEditor:plugin.js.twig', [
            'widget' => $widget,
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/javascript',
        ]);
    }

    /**
     * @return \Darvin\ContentBundle\Widget\WidgetPoolInterface
     */
    private function getWidgetPool()
    {
        return $this->get('darvin_content.widget.pool');
    }
}
