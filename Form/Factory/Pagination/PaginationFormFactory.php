<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Pagination;

use Darvin\AdminBundle\Form\Type\Pagination\RepaginateType;
use Darvin\AdminBundle\Pagination\PaginationManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Pagination form factory
 */
class PaginationFormFactory implements PaginationFormFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Darvin\AdminBundle\Pagination\PaginationManagerInterface
     */
    private $paginationManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface            $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface              $genericFormFactory Generic form factory
     * @param \Darvin\AdminBundle\Pagination\PaginationManagerInterface $paginationManager  Pagination manager
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        FormFactoryInterface $genericFormFactory,
        PaginationManagerInterface $paginationManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericFormFactory = $genericFormFactory;
        $this->paginationManager = $paginationManager;
    }

    /**
     * {@inheritDoc}
     */
    public function createRepaginateForm(string $entityClass): FormInterface
    {
        return $this->genericFormFactory->create(
            RepaginateType::class,
            [
                'itemsPerPage' => $this->paginationManager->getItemsPerPage($entityClass),
            ],
            [
                'action'       => $this->adminRouter->generate(null, $entityClass, AdminRouterInterface::TYPE_REPAGINATE),
                'entity_class' => $entityClass,
            ]
        );
    }
}
