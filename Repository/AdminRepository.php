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

use Doctrine\ORM\EntityRepository;

/**
 * Administrator entity repository
 */
class AdminRepository extends EntityRepository
{
    /**
     * @param string $emailOrUsername Email or username
     *
     * @return \Darvin\AdminBundle\Entity\Admin
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
}
