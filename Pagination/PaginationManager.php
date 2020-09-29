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

use Darvin\AdminBundle\Configuration\SectionConfigurationInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Pagination manager
 */
class PaginationManager implements PaginationManagerInterface
{
    private const SESSION_KEY_ITEMS_PER_PAGE = 'darvin_admin.pagination.items_per_page';

    /**
     * @var \Darvin\AdminBundle\Configuration\SectionConfigurationInterface
     */
    private $sectionConfig;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param \Darvin\AdminBundle\Configuration\SectionConfigurationInterface $sectionConfig Section configuration
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface      $session       Session
     */
    public function __construct(SectionConfigurationInterface $sectionConfig, SessionInterface $session)
    {
        $this->sectionConfig = $sectionConfig;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getItemsPerPage(string $entity): int
    {
        $this->validate($entity);

        $collection = $this->getItemsPerPageCollection();

        if (isset($collection[$entity])) {
            $itemsPerPage = $collection[$entity];

            if ($this->isValid($itemsPerPage)) {
                return $itemsPerPage;
            }
        }

        return $this->sectionConfig->getSection($entity)->getConfig()['pagination']['items'];
    }

    /**
     * {@inheritDoc}
     */
    public function setItemsPerPage(string $entity, int $itemsPerPage): void
    {
        $this->validate($entity);

        if (!$this->isValid($itemsPerPage)) {
            throw new \InvalidArgumentException(sprintf('Items per page "%d" is invalid.', $itemsPerPage));
        }

        $collection = $this->getItemsPerPageCollection();

        $collection[$entity] = $itemsPerPage;

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
     * @param string $entity Entity class
     *
     * @throws \InvalidArgumentException
     */
    private function validate(string $entity): void
    {
        if (!$this->sectionConfig->getSection($entity)->getConfig()['pagination']['enabled']) {
            throw new \InvalidArgumentException(sprintf('Pagination is disabled for entity "%s".', $entity));
        }
    }
}
