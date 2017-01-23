<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

use Darvin\AdminBundle\Form\Type\Security\Permissions\ObjectPermissionsType;
use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
use Darvin\AdminBundle\Security\User\Roles;
use Darvin\ConfigBundle\Configuration\AbstractConfiguration;
use Darvin\ConfigBundle\Parameter\ParameterModel;

/**
 * Security configuration
 */
class SecurityConfiguration extends AbstractConfiguration implements SecurityConfigurationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $securableAlias;

    /**
     * @var string
     */
    private $securableClass;

    /**
     * @param string $name           Name
     * @param string $securableAlias Securable alias
     * @param string $securableClass Securable class
     */
    public function __construct($name, $securableAlias, $securableClass)
    {
        parent::__construct();

        $this->name = $name;
        $this->securableAlias = $securableAlias;
        $this->securableClass = $securableClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return [
            new ParameterModel(
                'permissions',
                ParameterModel::TYPE_ARRAY,
                [
                    $this->securableAlias => new ObjectPermissions($this->securableClass),
                ],
                [
                    'form' => [
                        'options' => [
                            'label'      => false,
                            'entry_type' => ObjectPermissionsType::class,
                        ],
                    ],
                ]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        return $this->__call(__FUNCTION__);
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
        return $this->name;
    }
}
