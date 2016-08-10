<?php
/**
 * @package    category
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Category plugin.
 *
 * @package  Category
 * @since    1.0
 */
class plgTagsCategory extends JPlugin
{
	/**
	 * Is called when the category helper tries to construct a tag item list query
	 *
	 * @param   JDatabaseQuery  $query  The database query to be modified
	 *
	 * @return  JDatabaseQuery   The modified database query
	 *
	 * @since 1.0
	 */
	public function onTagListQuery($query)
	{
		$query->select('COALESCE(cat.params, \'\') AS params');
		$query->join('LEFT',  '#__categories AS cat on cat.id=m.content_item_id AND m.type_alias LIKE \'%category\'');

		return $query;
	}
}
