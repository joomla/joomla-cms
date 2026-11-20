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

        [$maxWidth, $maxHeight] = $this->getMaxSize((string) $item->adapter, (string) $item->path);

        if (!$maxWidth && !$maxHeight) {
            return;
        }

        if (!\in_array(strtolower($item->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
            return;
        }

        if (strtolower($item->extension) === 'avif' && !\function_exists('imageavif')) {
            return;
        }

        $imgObject = new Image(imagecreatefromstring($item->data));

        if (
            !(($maxWidth && $imgObject->getWidth() > $maxWidth)
            || ($maxHeight && $imgObject->getHeight() > $maxHeight))
        ) {
            return;
        }

        $imgObject->resize(
            $maxWidth,
            $maxHeight,
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

    /**
     * Returns the maximum width and height for an upload into the given folder.
     * Uses default resizing if folder not matches and sizes given
     *
     * @param   string  $adapter  The media adapter name, e.g. "local-images"
     * @param   string  $path     The folder path inside the adapter
     *
     * @return  int[]  The maximum width and height, 0 meaning no resizing
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getMaxSize(string $adapter, string $path): array
    {
        $uploadPath  = trim($path, '/');
        $matchedRule = null;
        $matchedPath = null;

        foreach ((array) $this->params->get('batch_folders', []) as $folderRule) {
            $folderRule = (array) $folderRule;
            $folder     = explode(':', (string) ($folderRule['folder'] ?? ''), 2);

            if (\count($folder) !== 2 || $folder[0] !== $adapter) {
                continue;
            }

            $folderPath = trim($folder[1], '/');

            if ($folderPath !== '' && $uploadPath !== $folderPath && !str_starts_with($uploadPath, $folderPath . '/')) {
                continue;
            }

            if ($matchedPath === null || \strlen($folderPath) > \strlen($matchedPath)) {
                $matchedRule = $folderRule;
                $matchedPath = $folderPath;
            }
        }

        if ($matchedRule !== null) {
            return [(int) ($matchedRule['width'] ?? 0), (int) ($matchedRule['height'] ?? 0)];
        }

        return [(int) $this->params->get('batch_width', 0), (int) $this->params->get('batch_height', 0)];
    }
}
