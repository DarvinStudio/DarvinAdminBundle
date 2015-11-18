<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Dropzone;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Dropzone file form type
 */
class FileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filename', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType', array(
                'label' => false,
                'attr'  => array(
                    'class' => 'filename',
                ),
            ))
            ->add('originalFilename', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType', array(
                'label' => false,
                'attr'  => array(
                    'class' => 'original_filename',
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => md5(__FILE__.$this->getName()),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_dropzone_file';
    }
}
