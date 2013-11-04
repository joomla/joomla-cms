<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\View;

defined('_JEXEC') or die;

use JViewHtml;

/**
 * Generic Installation View
 *
 * @package     Joomla.Installation
 * @subpackage  View
 * @since       3.1
 */
class DefaultView extends JViewHtml
{
	/**
	 * The JForm object
	 *
	 * @var    \JForm
	 * @since  3.1
	 */
	protected $form;

	/**
	 * Redefine the model so the correct type hinting is available.
	 *
	 * @var     \Installation\Model\SetupModel
	 * @since   3.1
	 */
	protected $model;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$this->form = $this->model->getForm();

		return parent::render();
	}
}
