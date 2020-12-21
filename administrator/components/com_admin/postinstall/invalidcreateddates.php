<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for invalid created dates checks
 */

defined('_JEXEC') or die;

/**
 * Checks if the installation has some content with invalid created dates
 *
 * @return  boolean  True if the check fails.
 *
 * @since   __DEPLOY_VERSION__
 *
 * @link    https://github.com/joomla/joomla-cms/issues/30546
 * @link    https://github.com/joomla/joomla-cms/pull/31450
 */
function admin_postinstall_invalidcreateddates_condition()
{
	$db       = JFactory::getDbo();
	$nullDate = $db->quote($db->getNullDate());

	$query1 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__banners'))
		->where($db->quoteName('created') . ' = ' . $nullDate);

	$query2 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__categories'))
		->where($db->quoteName('created_time') . ' = ' . $nullDate);

	$query3 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__contact_details'))
		->where($db->quoteName('created') . ' = ' . $nullDate);

	$query4 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__content'))
		->where($db->quoteName('created') . ' = ' . $nullDate);

	$query5 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__fields'))
		->where($db->quoteName('created_time') . ' = ' . $nullDate);

	$query6 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__fields_groups'))
		->where($db->quoteName('created') . ' = ' . $nullDate);

	$query7 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__newsfeeds'))
		->where($db->quoteName('created') . ' = ' . $nullDate);

	$query8 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__redirect_links'))
		->where($db->quoteName('created_date') . ' = ' . $nullDate);

	$query9 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__tags'))
		->where($db->quoteName('created_time') . ' = ' . $nullDate);

	$query10 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__users'))
		->where($db->quoteName('registerDate') . ' = ' . $nullDate);

	$query11 = $db->getQuery(true)
		->select('1')
		->from($db->quoteName('#__user_notes'))
		->where($db->quoteName('created_time') . ' = ' . $nullDate);

	$query1->unionAll(array($query2, $query3, $query4, $query5, $query6, $query7, $query8, $query9, $query10, $query11));

	$db->setQuery($query1);
	$db->execute();
	$numRows = $db->getNumRows();

	if (isset($numRows) && $numRows != 0)
	{
		// We have rows here so we have at minumum one row with an invalid created date
		return true;
	}

	// All good, the query returned nothing.
	return false;
}
