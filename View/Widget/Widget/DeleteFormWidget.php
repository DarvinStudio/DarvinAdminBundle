<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Form\AdminFormFactoryInterface;
use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
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
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface
     */
    private $identifierAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface           $adminRouter        Admin router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface $identifierAccessor Identifier accessor
     */
    public function __construct(AdminRouterInterface $adminRouter, IdentifierAccessorInterface $identifierAccessor)
    {
        $this->adminRouter = $adminRouter;
        $this->identifierAccessor = $identifierAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        if (!$this->adminRouter->exists($entity, AdminRouterInterface::TYPE_DELETE)) {
            return null;
        }

        $id = $this->identifierAccessor->getId($entity);

        return $this->render([
            'entity'             => $entity,
            'id'                 => $id,
            'name'               => AdminFormFactoryInterface::NAME_PREFIX_DELETE.$id,
            'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('entity_class', null);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::CREATE_DELETE;
    }
}
