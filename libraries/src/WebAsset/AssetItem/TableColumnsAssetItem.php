<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Web Asset Item class for tables.column asset
 *
 * @since  4.2.0
 */
class TableColumnsAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    /**
     * Method called when asset attached to the Document.
     * Used to add the language strings required by the script.
     *
     * @param   Document  $doc  Active document
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function onAttachCallback(Document $doc)
    {
        // Add table-columns.js language strings
        Text::script('JGLOBAL_COLUMNS');
    }
}
