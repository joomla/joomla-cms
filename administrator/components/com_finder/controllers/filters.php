<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Filters controller class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderControllerFilters extends JControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_FINDER_FILTERS';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 * @since  3.1
	 */
	protected $redirectUrl = 'index.php?option=com_finder&view=filters';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_finder';

	/*
	 * @var  string  Model name
	 * @since  3.1
	 */
	protected $name = 'Filter';

	/*
	 * @var  string   Model prefix
	 * @since  3.1
	 */
	protected $prefix = 'FinderModel';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   2.5
	 * @deprecated  3.5
	 */
	public function getModel($name = 'Filter', $prefix = 'FinderModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
