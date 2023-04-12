<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Resize\Extension;

use Joomla\CMS\Image\Image;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Resize Action
 *
 * @since  4.0.0
 */
final class Resize extends MediaActionPlugin
{
    /**
     * The save event.
     *
     * @param   string   $context  The context
     * @param   object   $item     The item
     * @param   boolean  $isNew    Is new item
     * @param   array    $data     The validated data
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onContentBeforeSave($context, $item, $isNew, $data = [])
    {
        if ($context != 'com_media.file') {
            return;
        }

        if (!$this->params->get('batch_width') && !$this->params->get('batch_height')) {
            return;
        }

        if (!in_array($item->extension, ['jpg', 'jpeg', 'png', 'gif'])) {
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

        $type = IMAGETYPE_JPEG;

        switch ($item->extension) {
            case 'gif':
                $type = IMAGETYPE_GIF;
                break;
            case 'png':
                $type = IMAGETYPE_PNG;
        }

        ob_start();
        $imgObject->toFile(null, $type);
        $item->data = ob_get_contents();
        ob_end_clean();
    }
}
