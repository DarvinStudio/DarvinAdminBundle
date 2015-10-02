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

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Log controller
 */
class LogController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Log entry ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function revertAction(Request $request, $id)
    {
        $logEntry = $this->getLogEntry($id);

        $this->checkIfUserHasPermission(Permission::VIEW, LogEntry::LOG_ENTRY_CLASS);

        $this->getCustomObjectLoader()->loadForObject($logEntry);
        $logged = $logEntry->getObject();

        if (empty($logged)) {
            throw $this->createNotFoundException(sprintf('Unable to find logged entity for log entry with ID "%d".', $id));
        }

        $this->checkIfUserHasPermission(Permission::EDIT, $logEntry->getObjectClass());

        $url = $request->headers->get('referer', $this->getAdminRouter()->generate($logEntry, AdminRouter::TYPE_INDEX));

        if (!$this->getLogEntryRevertFormWidgetGenerator()->createRevertForm($logEntry)->handleRequest($request)->isValid()) {
            $this->getFlashNotifier()->formError();

            return $this->redirect($url);
        }

        $this->getFlashNotifier()->success('log.action.revert.success');

        $this->getLogEntryRepository()->revert($logged, $logEntry->getVersion());
        $this->getEntityManager()->flush();

        return $this->redirect($url);
    }

    /**
     * @param string $permission  Permission
     * @param string $entityClass Entity class
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    private function checkIfUserHasPermission($permission, $entityClass)
    {
        if (!$this->isGranted($permission, $entityClass)) {
            throw $this->createAccessDeniedException(
                sprintf('You do not have "%s" permission on "%s" class objects.', $permission, $entityClass)
            );
        }
    }

    /**
     * @param int $id Log entry ID
     *
     * @return \Darvin\AdminBundle\Entity\LogEntry
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getLogEntry($id)
    {
        $logEntry = $this->getLogEntryRepository()->find($id);

        if (empty($logEntry)) {
            throw $this->createNotFoundException(sprintf('Unable to find log entry by ID "%d".', $id));
        }

        return $logEntry;
    }

    /**
     * @return \Darvin\AdminBundle\Route\AdminRouter
     */
    private function getAdminRouter()
    {
        return $this->get('darvin_admin.route.router');
    }

    /**
     * @return \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private function getCustomObjectLoader()
    {
        return $this->get('darvin_utils.custom_object.loader.entity');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private function getFlashNotifier()
    {
        return $this->get('darvin_utils.flash.notifier');
    }

    /**
     * @return \Darvin\AdminBundle\Repository\LogEntryRepository
     */
    private function getLogEntryRepository()
    {
        return $this->getDoctrine()->getRepository(LogEntry::LOG_ENTRY_CLASS);
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\LogEntry\RevertFormGenerator
     */
    private function getLogEntryRevertFormWidgetGenerator()
    {
        return $this->getViewWidgetGeneratorPool()->getWidgetGenerator('log_entry_revert_form');
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private function getViewWidgetGeneratorPool()
    {
        return $this->get('darvin_admin.view.widget_generator.pool');
    }
}
