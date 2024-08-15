<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
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
 * Web Asset Item class for form.validate asset
 *
 * @since  4.0.0
 */
class FormValidateAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
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
        // Add validate.js language strings
        Text::script('JLIB_FORM_CONTAINS_INVALID_FIELDS');
        Text::script('JLIB_FORM_FIELD_REQUIRED_VALUE');
        Text::script('JLIB_FORM_FIELD_REQUIRED_CHECK');
        Text::script('JLIB_FORM_FIELD_INVALID_VALUE');
    }
}
