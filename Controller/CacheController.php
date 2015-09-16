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

use Darvin\Utils\Flash\FlashNotifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $message = FlashNotifierInterface::MESSAGE_FORM_ERROR;
        $success = false;

        $form = $this->getCacheFormManager()->createClearForm();
        $formIsValid = $form->handleRequest($request)->isValid();

        if ($formIsValid) {
            set_time_limit(0);

            if ($this->getCachesClearCommand()->run(new ArrayInput(array()), new NullOutput()) > 0) {
                $message = 'cache.action.clear.error';
            } else {
                $message = 'cache.action.clear.success';
                $success = true;
            }
        }
        if (!$request->isXmlHttpRequest()) {
            $this->getFlashNotifier()->done($success, $message);

            return $this->redirect($request->headers->get('referer', $this->generateUrl('darvin_admin_homepage')));
        }

        return new JsonResponse(array(
            'html'     => $success ? '' : $this->getCacheFormManager()->renderClearForm($this->getTemplating(), $form),
            'message'  => $message,
            'redirect' => false,
            'success'  => $success,
        ));
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

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    private function getTemplating()
    {
        return $this->get('templating');
    }
}
