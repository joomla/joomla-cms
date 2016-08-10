<?php
/**
 * @package    tags
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Tags plugin.
 *
 * @package  Tags
 * @since    1.0
 */
class plgTagsTags extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Is called when the tags helper tries to construct a tag item list query
	 *
	 * @param   JDatabaseQuery  $query  The database query to be modified
	 *
	 * @return  JDatabaseQuery   The modified database query
	 *
	 * @since 1.0
	 */
	public function onTagListQuery($query)
	{
		$query->select('COALESCE(ctnt.attribs, \'\') AS attribs');
		$query->join('LEFT',  '#__content AS ctnt on ctnt.id=m.content_item_id AND m.type_alias = \'com_content.article\'');

		return $query;
	}
}
