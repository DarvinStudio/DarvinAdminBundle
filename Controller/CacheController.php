<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache controller
 */
class CacheController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearAction(Request $request)
    {
        $url = $request->headers->get('referer', $this->generateUrl('darvin_admin_homepage'));

        $form = $this->getCacheFormManager()->createClearForm()->handleRequest($request);

        if (!$form->isValid()) {
            $messages = [];

            /** @var \Symfony\Component\Form\FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $messages[] = $error->getMessage();
            }
            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse('', false, implode(' ', $messages), [], $url);
            }
            foreach ($messages as $message) {
                $this->getFlashNotifier()->error($message);
            }

            return $this->redirect($url);
        }

        set_time_limit(0);

        if ($this->getCachesClearCommand()->run(new ArrayInput([]), new NullOutput()) > 0) {
            $message = 'cache.action.clear.error';

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($this->getCacheFormManager()->renderClearForm($form), false, $message);
            }

            $this->getFlashNotifier()->error($message);

            return $this->redirect($url);
        }

        $message = 'cache.action.clear.success';

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse('', true, $message, [], $url);
        }

        $this->getFlashNotifier()->success($message);

        return $this->redirect($url);
    }

    /**
     * @return \Darvin\AdminBundle\Form\CacheFormManager
     */
    private function getCacheFormManager()
    {
        return $this->get('darvin_admin.cache.form_manager');
    }

    /**
     * @return \Darvin\AdminBundle\Command\CachesClearCommand
     */
    private function getCachesClearCommand()
    {
        return $this->get('darvin_admin.cache.clear_command');
    }

    /**
     * @return \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private function getFlashNotifier()
    {
        return $this->get('darvin_utils.flash.notifier');
    }
}
