<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Asset Item class for the drag and drop reorder JavaScript and language strings.
 *
* @since  __DEPLOY_VERSION__
 */
class DragAndDropAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    /**
     * Method called when asset attached to the Document.
     * Useful for Asset to add a Script options.
     *
     * @param   Document  $doc  Active document
     *
     * @return void
     *
    * @since   __DEPLOY_VERSION__
     */
    public function onAttachCallback(Document $doc)
    {
        // Add dnd.js language strings
        Text::script('JGLOBAL_DRAGANDDROP_DRAGEND_STARTED');
        Text::script('JGLOBAL_DRAGANDDROP_DRAGOVER_NO_ELEMENT');
        Text::script('JGLOBAL_DRAGANDDROP_DRAGOVER_ELEMENT');
        Text::script('JGLOBAL_DRAGANDDROP_DRAGEND_NO_ELEMENT');
        Text::script('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED_NO_ELEMENT');
        Text::script('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED');
    }
}
