<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Copy form view widget
 */
class CopyFormWidget extends AbstractWidget
{
    const ALIAS = 'copy_form';

    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

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
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface $extendedMetadataFactory Extended metadata factory
     */
    public function setExtendedMetadataFactory(MetadataFactoryInterface $extendedMetadataFactory)
    {
        $this->extendedMetadataFactory = $extendedMetadataFactory;
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
    protected function createContent($entity, array $options, $property)
    {
        if (!$this->adminRouter->isRouteExists($entity, AdminRouter::TYPE_COPY)) {
            return null;
        }

        $extendedMeta = $this->extendedMetadataFactory->getExtendedMetadata($entity);

        return isset($extendedMeta['clonable'])
            ? $this->render($options, [
                'form'               => $this->adminFormFactory->createCopyForm($entity, $options['entity_class'])->createView(),
                'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
            ])
            : null;
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
        return [
            Permission::CREATE_DELETE,
        ];
    }
}
