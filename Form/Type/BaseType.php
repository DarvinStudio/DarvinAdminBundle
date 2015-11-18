<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Metadata\Metadata;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Base form type
 */
class BaseType extends AbstractFormType
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $meta;

    /**
     * @var string
     */
    private $fieldFilter;

    /**
     * @var string
     */
    private $nameSuffix;

    /**
     * @param string                                $action      Action
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta        Metadata
     * @param string                                $fieldFilter Field filter
     * @param string                                $nameSuffix  Name suffix
     */
    public function __construct($action, Metadata $meta, $fieldFilter = null, $nameSuffix = null)
    {
        $this->action = $action;
        $this->meta = $meta;
        $this->fieldFilter = $fieldFilter;
        $this->nameSuffix = $nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configuration = $this->meta->getConfiguration();

        $fields = $configuration['form'][$this->action]['fields'];

        foreach ($configuration['form'][$this->action]['field_groups'] as $groupFields) {
            $fields = array_merge($fields, $groupFields);
        }
        foreach ($fields as $field => $attr) {
            if (!empty($this->fieldFilter) && $field !== $this->fieldFilter) {
                continue;
            }

            $options = $this->resolveFieldOptionValues($attr['options']);
            $this->addValidConstraint($options);
            $builder->add($field, $attr['type'], $options);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'filterEntityFields'));
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event Form event
     */
    public function filterEntityFields(FormEvent $event)
    {
        foreach ($event->getForm()->all() as $name => $field) {
            if (!$field->getConfig()->getType()->getInnerType() instanceof EntityType) {
                continue;
            }

            $fieldOptions = $field->getConfig()->getOptions();

            if (!empty($fieldOptions['query_builder'])) {
                continue;
            }

            $entity = $event->getData();

            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $fieldOptions['em'];
            $doctrineMeta = $em->getClassMetadata($fieldOptions['class']);

            unset($fieldOptions['choice_list'], $fieldOptions['choice_loader']);

            $fieldOptions['query_builder'] = function (EntityRepository $er) use ($doctrineMeta, $entity, $fieldOptions) {
                $qb = $er->createQueryBuilder('o');

                if (empty($entity)) {
                    return $qb;
                }

                $ids = $doctrineMeta->getIdentifierValues($entity);
                $id = reset($ids);

                return !empty($id) ? $qb->andWhere('o != :entity')->setParameter('entity', $entity) : $qb;
            };

            $event->getForm()->add($name, $field->getConfig()->getType()->getName(), $fieldOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => $this->meta->getEntityClass(),
            'intention'          => md5(__FILE__.$this->getName().$this->meta->getEntityClass()),
            'translation_domain' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->meta->getFormTypeName().$this->nameSuffix;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMetadata()
    {
        return $this->meta;
    }

    /**
     * @param array $fieldOptions Field options
     */
    private function addValidConstraint(array &$fieldOptions)
    {
        if (!isset($fieldOptions['constraints'])) {
            $fieldOptions['constraints'] = new Valid();

            return;
        }
        if (is_array($fieldOptions['constraints'])) {
            $fieldOptions['constraints'][] = new Valid();

            return;
        }

        $fieldOptions['constraints'] = array(
            $fieldOptions['constraints'],
            new Valid(),
        );
    }
}
