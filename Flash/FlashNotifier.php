<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Flash;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Flash notifier
 */
class FlashNotifier
{
    const MESSAGE_FORM_ERROR = 'flash.error.form';

    const TYPE_ERROR   = 'error';
    const TYPE_SUCCESS = 'success';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    private $flashBag;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag Flash bag
     */
    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * Adds form error message.
     */
    public function formError()
    {
        $this->error(self::MESSAGE_FORM_ERROR);
    }

    /**
     * @param string $message Message
     */
    public function error($message)
    {
        $this->flashBag->add(self::TYPE_ERROR, $message);
    }

    /**
     * @param string $message Message
     */
    public function success($message)
    {
        $this->flashBag->add(self::TYPE_SUCCESS, $message);
    }
}
