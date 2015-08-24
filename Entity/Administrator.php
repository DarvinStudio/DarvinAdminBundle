<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Entity;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Doctrine;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Administrator
 *
 * @ORM\Entity(repositoryClass="Darvin\AdminBundle\Repository\AdministratorRepository")
 * @Doctrine\UniqueEntity(fields={"email"})
 * @Doctrine\UniqueEntity(fields={"username"})
 */
class Administrator implements \Serializable, AdvancedUserInterface
{
    const CLASS_NAME = 'Darvin\\AdminBundle\\Entity\\Administrator';

    const ROLE_ADMIN      = 'ROLE_ADMIN';
    const ROLE_GUEST      = 'ROLE_GUEST';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    /**
     * @var array
     */
    private static $availableExtraRoles = array(
        self::ROLE_GUEST      => 'administrator.entity.role.guest',
        self::ROLE_SUPERADMIN => 'administrator.entity.role.superadmin',
    );

    /**
     * @var int
     *
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Id
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @Assert\Email
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"New"})
     */
    private $plainPassword;

    /**
     * @param array $roles   Roles
     * @param bool  $locked  Is locked
     * @param bool  $enabled Is enabled
     */
    public function __construct(array $roles = array(), $locked = false, $enabled = true)
    {
        $this->roles = $roles;
        $this->locked = $locked;
        $this->enabled = $enabled;

        $this->updateSalt();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @return array
     */
    public function getDefaultPermissions()
    {
        return array_fill_keys(Permission::getAll(), !$this->isGuest());
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return in_array(self::ROLE_GUEST, $this->roles);
    }

    /**
     * @return bool
     */
    public function isSuperadmin()
    {
        return in_array(self::ROLE_SUPERADMIN, $this->roles);
    }

    /**
     * @return Administrator
     */
    public function setRandomPlainPassword()
    {
        $this->plainPassword = hash('sha512', uniqid(mt_rand(), true));

        return $this;
    }

    /**
     * @return Administrator
     */
    public function updateSalt()
    {
        $this->salt = hash('sha512', uniqid(mt_rand(), true));

        return $this;
    }

    /**
     * @return array
     */
    public static function getAvailableExtraRoles()
    {
        return self::$availableExtraRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @param boolean $enabled enabled
     *
     * @return Administrator
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param array $roles roles
     *
     * @return Administrator
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_ADMIN;

        return $roles;
    }

    /**
     * @param string $password password
     *
     * @return Administrator
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $salt salt
     *
     * @return Administrator
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $username username
     *
     * @return Administrator
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $locked locked
     *
     * @return Administrator
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param string $email email
     *
     * @return Administrator
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param \DateTime $updatedAt updatedAt
     *
     * @return Administrator
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $plainPassword plainPassword
     *
     * @return Administrator
     */
    public function setPlainPassword($plainPassword)
    {
        $this->updatedAt = new \DateTime();

        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }
}
