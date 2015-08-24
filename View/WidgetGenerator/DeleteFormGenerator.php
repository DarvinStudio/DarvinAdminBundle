<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * Delete form view widget generator
 */
class DeleteFormGenerator extends AbstractWidgetGenerator
{
    const ALIAS = 'delete_form';

    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private $adminFormFactory;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactory $adminFormFactory Admin form factory
     */
    public function setAdminFormFactory(AdminFormFactory $adminFormFactory)
    {
        $this->adminFormFactory = $adminFormFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        if (!$this->isGranted(Permission::CREATE_DELETE, $entity)) {
            return '';
        }

        return $this->render($options, array(
            'form'               => $this->adminFormFactory->createDeleteForm($entity)->createView(),
            'translation_prefix' => $this->metadataManager->getByEntity($entity)->getBaseTranslationPrefix(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinAdminBundle:widget:delete_form.html.twig';
    }
}
