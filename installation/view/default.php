<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Generic Installation View
 *
 * @since  3.1
 */
class InstallationViewDefault extends JViewHtml
{
	/**
	 * The JForm object
	 *
	 * @var    JForm
	 * @since  3.1
	 */
	protected $form;

	/**
	 * Redefine the model so the correct type hinting is available.
	 *
	 * @var     InstallationModelSetup
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
