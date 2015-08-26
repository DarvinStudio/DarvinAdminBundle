<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dropzone;

/**
 * Dropzone file
 */
class DropzoneFile
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $originalFilename;

    /**
     * @param string $filename filename
     *
     * @return DropzoneFile
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $originalFilename originalFilename
     *
     * @return DropzoneFile
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }
}
