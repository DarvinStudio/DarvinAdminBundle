<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Configuration;

use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;
use Darvin\ImageBundle\Configuration\ImageConfigurationInterface;
use Darvin\ImageBundle\Form\Type\SizeType;
use Darvin\ImageBundle\Size\Size;
use Darvin\UserBundle\Entity\BaseUser;
use Darvin\Utils\Security\SecurableInterface;

/**
 * Configuration
 */
class Configuration extends AbstractConfiguration implements ImageConfigurationInterface, SecurableInterface
{
    const IMAGE_SIZE_ADMIN = 'darvin_admin';

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return array(
            new ParameterModel(
                'image_sizes',
                ParameterModel::TYPE_ARRAY,
                array(
                    'darvin_admin' => new Size(self::IMAGE_SIZE_ADMIN, 128, 128),
                ),
                array(
                    'form' => array(
                        'options' => array(
                            'entry_type' => SizeType::SIZE_TYPE_CLASS,
                        ),
                    ),
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSizes()
    {
        return $this->__call(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function isImageSizesGlobal()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSizeGroupName()
    {
        return 'darvin_admin';
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedRoles()
    {
        return array(
            BaseUser::ROLE_SUPERADMIN,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin';
    }
}
