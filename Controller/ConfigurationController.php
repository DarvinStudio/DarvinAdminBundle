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

use Darvin\AdminBundle\Form\Type\Configuration\ConfigurationsType;
use Darvin\AdminBundle\Menu\MenuItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configuration controller
 */
class ConfigurationController extends Controller implements MenuItemInterface
{
    /**
     * @var array
     */
    private $menuItemAttributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuItemAttributes = $this->generateMenuItemAttributes();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $configurationPool = $this->getConfigurationPool();

        $url = $this->generateUrl('darvin_admin_configuration');

        $form = $this->createForm(ConfigurationsType::CONFIGURATIONS_TYPE_CLASS, $configurationPool, array(
            'action'             => $url,
            'translation_domain' => 'admin',
        ))->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $configurationPool->saveAll();

                $this->getFlashNotifier()->success('configuration.action.edit.success');

                return $this->redirect($url);
            }

            $this->getFlashNotifier()->formError();
        }

        return $this->render('DarvinAdminBundle:Configuration:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setChildMenuItems(array $childMenuItems)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getChildMenuItems()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexUrl()
    {
        return $this->generateUrl('darvin_admin_configuration');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMenuItemAttributes(array $menuItemAttributes)
    {
        $this->menuItemAttributes = $menuItemAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItemAttributes()
    {
        return $this->menuItemAttributes;
    }

    /**
     * @return array
     */
    private function generateMenuItemAttributes()
    {
        return array(
            'color'              => '#5a4fb6',
            'description'        => 'configuration.menu.description',
            'homepage_menu_icon' => 'bundles/darvinadmin/images/icons/homepage/configuration.png',
            'index_title'        => 'configuration.action.edit.link',
            'name'               => 'configuration',
        );
    }

    /**
     * @return \Darvin\ConfigBundle\Configuration\ConfigurationPool
     */
    private function getConfigurationPool()
    {
        return $this->get('darvin_config.configuration.pool');
    }

    /**
     * @return \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private function getFlashNotifier()
    {
        return $this->get('darvin_utils.flash.notifier');
    }
}
