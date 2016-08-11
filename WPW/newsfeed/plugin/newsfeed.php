<?php
/**
 * @package    newsfeed
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Newsfeed plugin.
 *
 * @package  Newsfeed
 * @since    1.0
 */
class plgTagsNewsfeed extends JPlugin
{
	/**
	 * Is called when the tags helper tries to construct a tag item list query
	 *
	 * @param   JDatabaseQuery  $query  The database query to be modified
	 *
	 * @return  JDatabaseQuery   The modified database query
	 *
	 * @since 1.0
	 */
	public function onTagItemListQuery($query)
	{
		$query->select('COALESCE(nfs.params, \'\') AS newsfeedparams');
		$query->join('LEFT',  '#__newsfeeds AS nfs on nfs.id=m.content_item_id AND m.type_alias = \'com_newsfeeds.newsfeed\'');

		return $query;
	}
}
