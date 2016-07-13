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
    const BASE_TYPE_CLASS = __CLASS__;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configuration = $this->getMetadata($options)->getConfiguration();

        $fields = $configuration['form'][$options['action_type']]['fields'];

        foreach ($configuration['form'][$options['action_type']]['field_groups'] as $groupFields) {
            $fields = array_merge($fields, $groupFields);
        }

        $fieldFilterProvided = isset($options['field_filter']);

        foreach ($fields as $field => $attr) {
            if ($fieldFilterProvided && $field !== $options['field_filter']) {
                continue;
            }

            $fieldOptions = $this->resolveFieldOptionValues($attr['options']);
            $this->addValidConstraint($fieldOptions);
            $builder->add($field, $attr['type'], $fieldOptions);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'filterEntityFields']);
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

            $fieldOptions['query_builder'] = function (EntityRepository $er) use ($doctrineMeta, $entity) {
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
        $resolver
            ->setDefaults(
                [
                'csrf_token_id'      => md5(__FILE__),
                'translation_domain' => 'admin',
                ]
            )
            ->remove('data_class')
            ->setRequired(
                [
                'action_type',
                'data_class',
                'metadata',
                ]
            )
            ->setDefined('field_filter')
            ->setAllowedTypes('action_type', 'string')
            ->setAllowedTypes('data_class', 'string')
            ->setAllowedTypes('field_filter', 'string')
            ->setAllowedTypes('metadata', Metadata::METADATA_CLASS);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityTranslationPrefix(array $options)
    {
        return $this->getMetadata($options)->getEntityTranslationPrefix();
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

        $fieldOptions['constraints'] = [
            $fieldOptions['constraints'],
            new Valid(),
        ];
    }

    /**
     * @param array $options Form options
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    private function getMetadata(array $options)
    {
        return $options['metadata'];
    }
}
