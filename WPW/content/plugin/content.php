<?php
/**
 * @package    content
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Content plugin.
 *
 * @package  Content
 * @since    1.0
 */
class plgTagsContent extends JPlugin
{
	/**
	 * Is called when the content helper tries to construct a tag item list query
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
