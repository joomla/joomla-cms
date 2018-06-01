<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

/**
 * Interface for a form model.
 *
 * @since  __DEPLOY_VERSION__
 */
interface FormModelInterface
{
	/**
	 * Method for getting a form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws \Exception
	 */
	public function getForm($data = array(), $loadData = true);
}
