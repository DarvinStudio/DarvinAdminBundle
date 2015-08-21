<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DataFixtures\ORM\Administrator;

use Darvin\AdminBundle\Entity\Administrator;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load administrator data
 */
class LoadAdministratorData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createAdministrator());
        $manager->flush();
    }

    /**
     * @return \Darvin\AdminBundle\Entity\Administrator
     */
    private function createAdministrator()
    {
        $administrator = new Administrator(array(Administrator::ROLE_SUPERADMIN));

        return $administrator
            ->setPlainPassword('123')
            ->setUsername('admin');
    }
}
