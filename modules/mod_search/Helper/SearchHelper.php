<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Search\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Helper for mod_search
 *
 * @since  1.5
 */
class SearchHelper
{
	/**
	 * Display the search button as an image.
	 *
	 * @param   string  $button_text  The alt text for the button.
	 *
	 * @return  string  The HTML for the image.
	 *
	 * @since   1.5
	 */
	public static function getSearchImage($button_text)
	{
		return HTMLHelper::_('image', 'searchButton.gif', $button_text, null, true, true);
	}
}
