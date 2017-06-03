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
 * Interface defining a factory which can create Form objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface FormFactoryInterface
{
	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string          $name     The name of the form.
	 * @param   string          $data     The name of an XML file or string to load as the form definition.
	 * @param   array           $options  An array of form options.
	 * @param   boolean         $replace  Flag to toggle whether form fields should be replaced if a field
	 *                                    already exists with the same group/name.
	 * @param   string|boolean  $xpath    An optional xpath to search for the fields.
	 *
	 * @return  Form  Form instance.
	 *
	 * @since   11.1
	 * @throws  \InvalidArgumentException if no data provided.
	 * @throws  \RuntimeException if the form could not be loaded.
	 */
	public static function createForm($name, $data = null, $options = array(), $replace = true, $xpath = false);
}