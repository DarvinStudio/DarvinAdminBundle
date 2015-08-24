<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Repository;

use Darvin\AdminBundle\Entity\Administrator;
use Doctrine\ORM\EntityRepository;

/**
 * Administrator entity repository
 */
class AdministratorRepository extends EntityRepository
{
    /**
     * @param string $emailOrUsername Email or username
     *
     * @return \Darvin\AdminBundle\Entity\Administrator
     */
    public function getByEmailOrUsername($emailOrUsername)
    {
        return $this->createQueryBuilder('o')
            ->where('o.email = :email_or_username')
            ->orWhere('o.username = :email_or_username')
            ->setParameter('email_or_username', $emailOrUsername)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNotSuperadminsBuilder()
    {
        $qb = $this->createDefaultQueryBuilder();

        return $qb
            ->where($qb->expr()->notLike('o.roles', ':role_superadmin'))
            ->setParameter('role_superadmin', '%'.Administrator::ROLE_SUPERADMIN.'%');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createDefaultQueryBuilder()
    {
        return $this->createQueryBuilder('o')->orderBy('o.username');
    }
}
