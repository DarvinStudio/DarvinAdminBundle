<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Form\AdminFormFactoryInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Delete form view widget
 */
class DeleteFormWidget extends AbstractWidget
{
    public const ALIAS = 'delete_form';

    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactoryInterface
     */
    private $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactoryInterface $adminFormFactory Admin form factory
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface     $adminRouter      Admin router
     */
    public function __construct(AdminFormFactoryInterface $adminFormFactory, AdminRouterInterface $adminRouter)
    {
        $this->adminFormFactory = $adminFormFactory;
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        if (!$this->adminRouter->exists($entity, AdminRouterInterface::TYPE_DELETE)) {
            return null;
        }

        return $this->render([
            'form'               => $this->adminFormFactory->createDeleteForm($entity, $options['entity_class'])->createView(),
            'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('entity_class', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::CREATE_DELETE;
    }
}
