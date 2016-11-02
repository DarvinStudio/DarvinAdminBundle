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

use Darvin\AdminBundle\Security\User\Roles;
use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;
use Darvin\ImageBundle\Configuration\ImageConfigurationInterface;
use Darvin\ImageBundle\Form\Type\SizeType;
use Darvin\ImageBundle\Size\Size;
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
        return [
            new ParameterModel(
                'image_sizes',
                ParameterModel::TYPE_ARRAY,
                [
                    'darvin_admin' => new Size(self::IMAGE_SIZE_ADMIN, 80, 80),
                ],
                [
                    'form' => [
                        'options' => [
                            'entry_type'    => SizeType::SIZE_TYPE_CLASS,
                            'entry_options' => [
                                'size_group' => $this->getImageSizeGroupName(),
                            ],
                        ],
                    ],
                ]
            ),
        ];
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
        return [
            Roles::ROLE_SUPERADMIN,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin';
    }
}
