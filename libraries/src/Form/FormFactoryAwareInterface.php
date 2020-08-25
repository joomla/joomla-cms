<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a form factory.
 *
 * @since  4.0.0
 */
interface FormFactoryAwareInterface
{
	/**
	 * Set the form factory to use.
	 *
	 * @param   FormFactoryInterface  $factory  The form factory to use.
	 *
	 * @return  FormFactoryAwareInterface  This method is chainable.
	 *
	 * @since   4.0.0
	 */
	public function setFormFactory(FormFactoryInterface $factory);
}
