<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\TreeListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Position form type
 */
class PositionType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Gedmo\Tree\TreeListener
     */
    private $treeListener;

    /**
     * @param \Doctrine\ORM\EntityManager                                 $em               Entity manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Gedmo\Tree\TreeListener                                    $treeListener     Tree event listener
     */
    public function __construct(EntityManager $em, PropertyAccessorInterface $propertyAccessor, TreeListener $treeListener)
    {
        $this->em = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->treeListener = $treeListener;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['level'] = $this->getLevel($form);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', [
            'data-reload-page' => 1,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return TextType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_position';
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form Form
     *
     * @return int|null
     */
    private function getLevel(FormInterface $form): ?int
    {
        if (null === $form->getParent()) {
            return null;
        }

        $entity = $form->getParent()->getData();

        if (!is_object($entity)) {
            return null;
        }

        $config = $this->treeListener->getConfiguration($this->em, ClassUtils::getClass($entity));

        if (empty($config)) {
            return null;
        }

        return $this->propertyAccessor->getValue($entity, $config['level']);
    }
}
