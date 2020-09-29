<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Pagination;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Pagination manager
 */
class PaginationManager implements PaginationManagerInterface
{
    private const SESSION_KEY_ITEMS_PER_PAGE = 'darvin_admin.pagination.items_per_page';

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Admin metadata manager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session         Session
     */
    public function __construct(AdminMetadataManagerInterface $metadataManager, SessionInterface $session)
    {
        $this->metadataManager = $metadataManager;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getItemsPerPage(string $entityClass): int
    {
        $this->validate($entityClass);

        $collection = $this->getItemsPerPageCollection();

        if (isset($collection[$entityClass])) {
            $itemsPerPage = $collection[$entityClass];

            if ($this->isValid($itemsPerPage)) {
                return $itemsPerPage;
            }
        }

        return $this->metadataManager->getConfiguration($entityClass)['pagination']['items'];
    }

    /**
     * {@inheritDoc}
     */
    public function setItemsPerPage(string $entityClass, int $itemsPerPage): void
    {
        $this->validate($entityClass);

        if (!$this->isValid($itemsPerPage)) {
            throw new \InvalidArgumentException(sprintf('Items per page "%d" is invalid.', $itemsPerPage));
        }

        $collection = $this->getItemsPerPageCollection();

        $collection[$entityClass] = $itemsPerPage;

        $this->setItemsPerPageCollection($collection);
    }

    /**
     * @return array
     */
    private function getItemsPerPageCollection(): array
    {
        $collection = $this->session->get(self::SESSION_KEY_ITEMS_PER_PAGE, []);

        if (is_array($collection)) {
            return $collection;
        }

        return [];
    }

    /**
     * @param array $collection Items per page collection
     */
    private function setItemsPerPageCollection(array $collection): void
    {
        $this->session->set(self::SESSION_KEY_ITEMS_PER_PAGE, $collection);
    }

    /**
     * @param mixed $itemsPerPage Items per page
     *
     * @return bool
     */
    private function isValid($itemsPerPage): bool
    {
        return is_int($itemsPerPage) && $itemsPerPage > 0;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @throws \InvalidArgumentException
     */
    private function validate(string $entityClass): void
    {
        if (!$this->metadataManager->getConfiguration($entityClass)['pagination']['enabled']) {
            throw new \InvalidArgumentException(sprintf('Pagination is disabled for entity class "%s".', $entityClass));
        }
    }
}
