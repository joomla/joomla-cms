<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Search\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Search\Administrator\Helper\SearchHelper;

/**
 * Search master display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'searches';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Load the submenu.
		SearchHelper::addSubmenu($this->input->get('view', 'searches'));

		return parent::display();
	}
}
