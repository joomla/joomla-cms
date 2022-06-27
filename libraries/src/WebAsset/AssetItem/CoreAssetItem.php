<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

use Joomla\CMS\Document\Document;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Web Asset Item class for Core asset
 *
 * @since  4.0.0
 */
class CoreAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    /**
     * Method called when asset attached to the Document.
     * Useful for Asset to add a Script options.
     *
     * @param   Document  $doc  Active document
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function onAttachCallback(Document $doc)
    {
        // Add core and base uri paths so javascript scripts can use them.
        $doc->addScriptOptions(
            'system.paths',
            [
                'root' => Uri::root(true),
                'rootFull' => Uri::root(),
                'base' => Uri::base(true),
                'baseFull' => Uri::base(),
            ]
        );

        HTMLHelper::_('form.csrf');
    }
}
