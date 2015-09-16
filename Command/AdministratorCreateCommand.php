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

use Darvin\AdminBundle\Entity\Administrator;
use Darvin\Utils\Command\AbstractContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Administrator create command
 */
class AdministratorCreateCommand extends AbstractContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('darvin:admin:administrator:create')
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
        parent::execute($input, $output);

        list(, $username, $plainPassword) = array_values($input->getArguments());

        $administrator = new Administrator();
        $administrator
            ->setUsername($username)
            ->setPlainPassword($plainPassword);

        $violations = $this->getValidator()->validate($administrator, array('Default', 'New'));

        if ($violations->count() > 0) {
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $this->error($violation->getInvalidValue().': '.$violation->getMessage());
            }

            return 1;
        }

        $em = $this->getEntityManager();
        $em->persist($administrator);
        $em->flush();

        $this->info(sprintf('Administrator "%s" with password "%s" successfully created.', $username, $plainPassword));

        return 0;
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
