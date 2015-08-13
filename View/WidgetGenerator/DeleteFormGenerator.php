<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 11:34
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Form\AdminFormFactory;

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
