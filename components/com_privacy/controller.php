<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Privacy Controller
 *
 * @since  3.9.0
 */
class PrivacyController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  $this
	 *
	 * @since   3.9.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view = $this->input->get('view', $this->default_view);

		// Submitting information requests through the frontend is restricted to authenticated users at this time
		if ($view === 'request' && JFactory::getUser()->guest)
		{
			$this->setRedirect(
				JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_privacy&view=request'), false)
			);

			return $this;
		}

		// Set a Referrer-Policy header for views which require it
		if (in_array($view, array('confirm', 'remind')))
		{
			JFactory::getApplication()->setHeader('Referrer-Policy', 'no-referrer', true);
		}

		return parent::display($cachable, $urlparams);
	}
}
