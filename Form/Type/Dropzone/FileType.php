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

use Darvin\AdminBundle\Dropzone\DropzoneFile;
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
            ->add('filename', 'hidden', array(
                'label' => false,
                'attr'  => array(
                    'class' => 'filename',
                ),
            ))
            ->add('originalFilename', 'hidden', array(
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
            'data_class' => DropzoneFile::CLASS_NAME,
            'intention'  => md5(__FILE__.$this->getName().DropzoneFile::CLASS_NAME),
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
