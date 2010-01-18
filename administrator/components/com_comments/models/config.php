<?php
/**
 * @version		$Id$
 * @package		JXtended.Comments
 * @subpackage	com_comments
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://jxtended.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * The JXtended Comments configuration model
 *
 * @package		JXtended.Comments
 * @version	1.0
 */
class CommentsModelConfig extends JModel
{
	/**
	 * Method to block either an email address or an IP from submitting data
	 *
	 * @access	public
	 * @param	string	$type	The type of entity to block (eg. email, ip)
	 * @param	array	$ids	The items to extract block data from
	 * @return	mixed	Boolean true on success, or JException on failure
	 * @since	1.0
	 */
	function block($type, $ids)
	{
		// make sure we have addresses to block
		if (empty($ids)) {
			return new JException('No item(s) supplied');
		}

		// sanitize array
		jimport('joomla.utilities.arrayhelper');
		JArrayHelper::toInteger($ids);

		// get the data to set based on the block type
		switch ($type)
		{
			case 'address':
				// get a database connection object
				$db	= &$this->getDBO();

				// get the list of addresses to block
				$db->setQuery(
					'SELECT `address`' .
					' FROM `#__jxcomments_comments`' .
					' WHERE `id` IN ('.implode(',', $ids).')'
				);
				if ($list = $db->loadResultArray())
				{
					// load the component data for com_comments
					$table = &JTable::getInstance('component');
					if (!$table->loadByOption('com_comments')) {
						$this->setError($table->getError());
						return false;
					}

					// get the existing blocked addresses and add the new ones to it
					$params = new JParameter($table->params);
					$blocked = $params->get('blockips');
					foreach (explode(',', $blocked) as $ip)
					{
						if ($ip = trim($ip)) {
							$list[]	= trim($ip);
						}
					}

					// remove duplicates and set the blocked addresses in the configuration object
					$list = array_unique($list);
					$params->set('blockips', implode(', ', $list));
					$table->set('params', $params->toString());

					// check the row.
					if (!$table->check()) {
						$this->setError($table->getError());
						return false;
					}

					// store the row.
					if (!$table->store()) {
						$this->setError($table->getError());
						return false;
					}
					return true;
				}
				break;

			default:
				return new JException('Unknown block type');
				break;
		}
	}
}