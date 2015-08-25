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

use Oneup\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Dropzone form type
 */
class DropzoneType extends AbstractType
{
    /**
     * @var \Oneup\UploaderBundle\Templating\Helper\UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @param \Oneup\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper Uploader helper
     */
    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dropzone', 'form', array(
                'label' => false,
                'attr'  => array(
                    'class'          => 'dropzone',
                    'data-filenames' => '.filenames',
                    'data-url'       => $this->uploaderHelper->endpoint($options['oneup_uploader_mapping']),
                ),
            ))
            ->add('filenames', 'collection', array(
                'label'     => false,
                'type'      => 'hidden',
                'allow_add' => true,
                'attr'      => array(
                    'class'         => 'filenames',
                    'data-autoinit' => 0,
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'oneup_uploader_mapping' => 'darvin',
            ))
            ->setAllowedTypes(array(
                'oneup_uploader_mapping' => 'string',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_dropzone';
    }
}
