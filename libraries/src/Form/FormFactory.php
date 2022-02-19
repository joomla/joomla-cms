<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\DatabaseAwareInterface;
use Joomla\CMS\MVC\Model\DatabaseAwareTrait;

/**
 * Default factory for creating Form objects
 *
 * @since  4.0.0
 */
class FormFactory implements FormFactoryInterface, DatabaseAwareInterface
{
	use DatabaseAwareTrait;

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @return  Form
	 *
	 * @since   4.0.0
	 */
	public function createForm(string $name, array $options = array()): Form
	{
		$form = new Form($name, $options);

		$form->setDbo($this->getDbo());

		return $form;
	}
}
