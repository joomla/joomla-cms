<?php
/**
 * @package    contact
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Contact plugin.
 *
 * @package  Contact
 * @since    1.0
 */
class plgTagsContact extends JPlugin
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
		$query->select('COALESCE(cntct.params, \'\') AS contactparams');
		$query->join('LEFT',  '#__contact_details AS cntct on cntct.id=m.content_item_id AND m.type_alias = \'com_contact.contact\'');

		return $query;
	}
}
