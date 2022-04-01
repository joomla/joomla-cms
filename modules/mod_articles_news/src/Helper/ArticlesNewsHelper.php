<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesNews\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Helper for mod_articles_news
 *
 * @since  1.6
 *
 * @deprecated  5.0 Use the none abstract helper class instead
 */
abstract class ArticlesNewsHelper
{
	/**
	 * Get a list of the latest articles from the article model
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 *
	 * @since 1.6
	 *
	 * @deprecated  5.0 Use the none abstract helper class instead
	 */
	public static function getList(&$params)
	{
		return (new ArticlesNews)->getList($params, Factory::getApplication());
	}
}
