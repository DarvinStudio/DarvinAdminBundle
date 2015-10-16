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

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\ImageBundle\Entity\Image\AbstractImage;

/**
 * Security configuration
 */
class SecurityConfiguration extends AbstractSecurityConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_security';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSecurableObjectClasses()
    {
        return array(
            'abstract_image' => AbstractImage::ABSTRACT_IMAGE_CLASS,
            'log_entry'      => LogEntry::LOG_ENTRY_CLASS,
        );
    }
}
