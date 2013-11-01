<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\View\Complete;

defined('_JEXEC') or die;

use JFactory,
	JViewHtml;

/**
 * The Installation Complete View
 *
 * @package     Joomla.Installation
 * @subpackage  View
 * @since       3.1
 */
class Html extends JViewHtml
{
	/**
	 * The JConfiguration data if present
	 *
	 * @var    \JRegistry
	 * @since  3.1
	 */
	protected $config;

	/**
	 * Redefine the model so the correct type hinting is available.
	 *
	 * @var     \Installation\Model\SetupModel
	 * @since   3.1
	 */
	protected $model;

	/**
	 * The session options
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $options;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$this->options = $this->model->getOptions();

		// Get the config string from the session.
		$session = JFactory::getSession();
		$this->config = $session->get('setup.config', null);

		return parent::render();
	}
}
