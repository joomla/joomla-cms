<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Admin\Controller;

defined('_JEXEC') or die;

use Joomla\Cms\Controller\Controller;

/**
 * Component Controller
 *
 * @since  1.5
 */
class View extends Controller
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'articles';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  View  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view   = $this->input->get('view', 'articles');
		$layout = $this->input->get('layout', 'articles');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'article' && $layout == 'edit' && !$this->checkEditId('com_content.edit.article', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(\JRoute::_('index.php?option=com_content&view=articles', false));

			return false;
		}

		return parent::display();
	}
}
