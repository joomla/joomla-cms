<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Resize\Extension;

use Joomla\CMS\Event\Model\BeforeSaveEvent;
use Joomla\CMS\Image\Image;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Resize Action
 *
 * @since  4.0.0
 */
final class Resize extends MediaActionPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onContentBeforeSave' => 'onContentBeforeSave',
        ]);
    }

    /**
     * The save event.
     *
     * @param   BeforeSaveEvent $event  The event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onContentBeforeSave(BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        if ($context != 'com_media.file') {
            return;
        }

        if (!$this->params->get('batch_width') && !$this->params->get('batch_height')) {
            return;
        }

        if (!\in_array(strtolower($item->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
            return;
        }

        if (strtolower($item->extension) === 'avif' && !\function_exists('imageavif')) {
            return;
        }

        $imgObject = new Image(imagecreatefromstring($item->data));

        if ($imgObject->getWidth() < $this->params->get('batch_width', 0) && $imgObject->getHeight() < $this->params->get('batch_height', 0)) {
            return;
        }

        $imgObject->resize(
            $this->params->get('batch_width', 0),
            $this->params->get('batch_height', 0),
            false,
            Image::SCALE_INSIDE
        );

        switch (strtolower($item->extension)) {
            case 'gif':
                $type = IMAGETYPE_GIF;
                break;
            case 'png':
                $type = IMAGETYPE_PNG;
                break;
            case 'avif':
                $type = IMAGETYPE_AVIF;
                break;
            case 'webp':
                $type = IMAGETYPE_WEBP;
                break;
            default:
                $type = IMAGETYPE_JPEG;
        }

        ob_start();
        $imgObject->toFile(null, $type);
        $item->data = ob_get_clean();
    }
}
