<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Oneup\UploaderBundle\Event\PostUploadEvent;
use Oneup\UploaderBundle\Uploader\Response\AbstractResponse;
use Oneup\UploaderBundle\UploadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Uploader event subscriber
 */
class UploaderSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UploadEvents::POST_UPLOAD => 'postUpload',
        ];
    }

    /**
     * @param \Oneup\UploaderBundle\Event\PostUploadEvent $event Event
     */
    public function postUpload(PostUploadEvent $event): void
    {
        $response = $event->getResponse();

        if (!$response instanceof AbstractResponse) {
            return;
        }

        $file = $event->getFile();

        if (!$file instanceof File) {
            return;
        }

        $response->addToOffset($file->getFilename(), []);
    }
}
