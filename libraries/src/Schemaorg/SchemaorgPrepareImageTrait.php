<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prepare images to be valid for JSON-LD output
 *
 * @since  5.0.0
 */
trait SchemaorgPrepareImageTrait
{
    /**
     * Prepare media image files
     *
     * @param   string|array $image
     *
     * @return  string|array
     *
     * @since   5.0.0
     */
    protected function prepareImage($image)
    {
        if (\is_array($image)) {
            // We don't handle references, they should be ok
            if (\count($image) === 1 && isset($image['@id'])) {
                return $image;
            }

            $newImages = [];

            foreach ($image as $img) {
                $newImages[] = $this->prepareImage($img);
            }

            return $newImages;
        }

        $img = HTMLHelper::_('cleanImageUrl', $image);

        return $img->url ?? null;
    }
}
