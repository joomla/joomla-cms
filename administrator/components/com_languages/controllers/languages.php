<?php
/**
 * @version		$Id: languages.php 21032 2011-03-29 16:38:31Z dextercowley $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since	1.6
 */
class LanguagesControllerLanguages extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 * @since	1.6
	 */
	function &getModel($name = 'Language', $prefix = 'LanguagesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
