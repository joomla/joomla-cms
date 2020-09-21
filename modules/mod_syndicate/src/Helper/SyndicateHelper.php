<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Syndicate\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_syndicate
 *
 * @since  1.5
 */
class SyndicateHelper
{
	/**
	 * Gets the link
	 *
	 * @param   Registry      $params    The module parameters
	 * @param   HtmlDocument  $document  The document
	 *
	 * @return  array  The link as a string
	 *
	 * @since   1.5
	 */
	public static function getLink(Registry $params, HtmlDocument $document)
	{
		foreach ($document->_links as $link => $value)
		{
			$value = ArrayHelper::toString($value);

			if (strpos($value, 'application/' . $params->get('format') . '+xml'))
			{
				return $link;
			}
		}
	}
}
