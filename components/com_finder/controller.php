<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Finder Component Controller.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached. [optional]
	 * @param   array    $urlparams  An array of safe url parameters and their variable types,
	 *                               for valid values see {@link JFilterInput::clean()}. [optional]
	 *
	 * @return  JControllerLegacy  This object is to support chaining.
	 *
	 * @since   2.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$input = JFactory::getApplication()->input;
		$cachable = true;

		// Load plug-in language files.
		FinderHelperLanguage::loadPluginLanguage();

		// Set the default view name and format from the Request.
		$viewName = $input->get('view', 'search', 'word');
		$input->set('view', $viewName);

		/* Don't cache view for search queries
		 * The last condition must explicitly force the filter type 'array', 
		 * because the default filter type is 'string', which makes JFilter::get() 
		 * fail to check for 't' (is array) as its default filter type applied is 'cmd'.
		 */
		if ($input->getString('q', null) || $input->getInt('f', null) || $input->get('t', null, 'array'))
		{
			$cachable = false;
		}

		$safeurlparams = array(
			'f' 	=> 'INT',
			'lang' 	=> 'CMD'
		);

		return parent::display($cachable, $safeurlparams);
	}
}
