<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Plugin class for redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.redirect
 */
class plgSystemRedirect extends JPlugin
{
	/**
	 * Object Constructor.
	 *
	 * @access	public
	 * @param	object	The object to observe -- event dispatcher.
	 * @param	object	The configuration object for the plugin.
	 * @return	void
	 * @since	1.0
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the error handler for E_ERROR to be the class handleError method.
		JError::setErrorHandling(E_ERROR, 'callback', array('plgSystemRedirect', 'handleError'));
	}

	static function handleError(&$error)
	{
		// Get the application object.
		$app = JFactory::getApplication();

		// Make sure the error is a 404 and we are not in the administrator.
		if (!$app->isAdmin() and ($error->getCode() == 404))
		{
			// Get the full current URI.
			$uri = JURI::getInstance();
			$current = $uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment'));

			// Attempt to ignore idiots.
			if ((strpos($current, 'mosConfig_') !== false) || (strpos($current, '=http://') !== false)) {
				// Render the error page.
				JError::customErrorPage($error);
			}

			// See if the current url exists in the database as a redirect.
			$db = JFactory::getDBO();
				$db->setQuery(
				'SELECT '.$db->quoteName('new_url').', '.$db->quoteName('published').
				' FROM '.$db->quoteName('#__redirect_links') .
				' WHERE '.$db->quoteName('old_url').' = '.$db->quote($current),
				0, 1
			);
			$link = $db->loadObject();

			// If a redirect exists and is published, permanently redirect.
			if ($link and ($link->published == 1)) {
				$app->redirect($link->new_url, null, null, true, true);
			}
			else
			{
				$referer = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];

				$db->setQuery('SELECT id FROM ' . $db->quoteName('#__redirect_links') . '  WHERE old_url= ' . $db->quote($current));
				$res = $db->loadResult();
				if(!$res) {

					// If not, add the new url to the database.
					 $query = $db->getQuery(true);
					 $query->insert($db->quoteName('#__redirect_links'), false);
					 $columns = array( $db->quoteName('old_url'),
									$db->quoteName('new_url'),
									$db->quoteName('referer'),
									$db->quoteName('comment'),
									$db->quoteName('published'),
									$db->quoteName('created_date')
								);
					$query->columns($columns);
				    $query->values($db->Quote($current). ', '. $db->Quote('').
				  				' ,'.$db->Quote($referer).', '.$db->Quote('').',0, '.
								  $db->Quote(JFactory::getDate()->toSql())
								);

					$db->setQuery($query);
					$db->query();

				}
				// Render the error page.
				JError::customErrorPage($error);
			}
		}
		else {
			// Render the error page.
			JError::customErrorPage($error);
		}
	}
}
