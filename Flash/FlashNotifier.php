<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 05.08.15
 * Time: 11:08
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
