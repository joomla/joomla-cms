<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       1.6
 */
class LanguagesControllerLanguages extends JControllerAdmin
{
	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	* @since  3.1
	*/
	protected $redirectUrl = 'index.php?option=com_content&view=articles';


	/*
	 * @var  string  Model name
	* @since  3.1
	*/
	protected $name = 'Language';

	/*
	 * @var  string   Model prefix
	* @since  3.1
	*/
	protected $prefix = 'LangagesModel';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Language', $prefix = 'LanguagesModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
