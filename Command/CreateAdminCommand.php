<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Darvin\AdminBundle\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create administrator command
 */
class CreateAdminCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('darvin:admin:create-admin')
            ->setDescription('Creates administrator.')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'Administrator username'),
                new InputArgument('password', InputArgument::REQUIRED, 'Administrator password'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list(, $username, $plainPassword) = array_values($input->getArguments());

        $admin = new Admin();
        $admin
            ->setUsername($username)
            ->setPlainPassword($plainPassword);

        $violations = $this->getValidator()->validate($admin, array('Default', 'New'));

        if ($violations->count() > 0) {
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $output->writeln(sprintf('<error>%s: %s</error>', $violation->getInvalidValue(), $violation->getMessage()));
            }

            return;
        }

        $em = $this->getEntityManager();
        $em->persist($admin);
        $em->flush();

        $output->writeln(
            sprintf('<info>Administrator "%s" with password "%s" successfully created.</info>', $username, $plainPassword)
        );
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private function getValidator()
    {
        return $this->getContainer()->get('validator');
    }
}
