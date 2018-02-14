<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

defined('_JEXEC') or die;

/**
 * Default factory for creating Form objects
 *
 * @since  4.0.0
 */
class FormFactory implements FormFactoryInterface
{
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
		return new Form($name, $options);
	}
}
