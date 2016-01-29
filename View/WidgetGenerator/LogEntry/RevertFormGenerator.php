<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator\LogEntry;

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\WidgetGenerator\AbstractWidgetGenerator;
use Symfony\Component\Routing\RouterInterface;

/**
 * Log entry revert form view widget generator
 */
class RevertFormGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private $adminFormFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactory $adminFormFactory Admin form factory
     */
    public function setAdminFormFactory(AdminFormFactory $adminFormFactory)
    {
        $this->adminFormFactory = $adminFormFactory;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router Router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param \Darvin\AdminBundle\Entity\LogEntry $logEntry Log entry
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRevertForm(LogEntry $logEntry)
    {
        $action = $this->router->generate('darvin_admin_log_revert', array('id' => $logEntry->getId()));

        return $this->adminFormFactory->createIdForm($logEntry, 'revert_', $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'log_entry_revert_form';
    }

    /**
     * @param \Darvin\AdminBundle\Entity\LogEntry $logEntry Log entry
     * @param array                               $options  Options
     * @param string                              $property Property name
     *
     * @return string
     */
    protected function generateWidget($logEntry, array $options, $property)
    {
        $object = $logEntry->getObject();

        if (empty($object) || !$this->isGranted(Permission::EDIT, $object)) {
            return '';
        }

        return $this->render($options, array(
            'form' => $this->createRevertForm($logEntry)->createView(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredEntityClass()
    {
        return LogEntry::LOG_ENTRY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return array(
            Permission::VIEW,
        );
    }
}
