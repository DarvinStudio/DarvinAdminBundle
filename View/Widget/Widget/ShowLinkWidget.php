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

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Show link view widget
 */
class ShowLinkWidget extends AbstractWidget
{
    public const ALIAS = 'show_link';

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface $adminRouter Admin router
     */
    public function __construct(AdminRouterInterface $adminRouter)
    {
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
        if (!empty($options['property'])) {
            $entity = $this->getPropertyValue($entity, $options['property']);

            if (empty($entity) || !$this->metadataManager->hasMetadata($entity)) {
                return null;
            }
        }
        if (!$this->adminRouter->exists($entity, AdminRouterInterface::TYPE_SHOW) && $this->isGranted(Permission::VIEW, $entity)) {
            return null;
        }

        return $this->render([
            'entity'             => $entity,
            'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'entity_class' => null,
                'text'         => false,
            ])
            ->setAllowedTypes('text', 'boolean');
    }
}
