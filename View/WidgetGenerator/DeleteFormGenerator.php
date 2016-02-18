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
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactory $adminFormFactory Admin form factory
     */
    public function setAdminFormFactory(AdminFormFactory $adminFormFactory)
    {
        $this->adminFormFactory = $adminFormFactory;
    }

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
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
    protected function generateWidget($entity, array $options, $property)
    {
        return $this->adminRouter->isRouteExists($entity, AdminRouter::TYPE_DELETE)
            ? $this->render($options, array(
                'form'               => $this->adminFormFactory->createDeleteForm($entity, $options['entity_class'])->createView(),
                'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
            ))
            : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('entity_class', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return array(
            Permission::CREATE_DELETE,
        );
    }
}
