<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.Default
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sampledata - Data Plugin
 *
 * @since  3.5
 */
class PlgSampledataDefault extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.5
	 */
	protected $db;

	/**
	 * Application object
	 *
	 * @var    JApplication
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Get an overview of the proposed sampledata.
	 *
	 * @return  boolean	True on success.
	 */
	public function onSampledataGetOverview()
	{
		$data = new stdClass;
		$data->name        = $this->_name;
		$data->title       = 'Default Sampledata';
		$data->description = 'Default Sampledata.';
		$data->icon        = 'book';
		$data->steps       = 1;

		return $data;
	}

	/**
	 * First step to enter the sampledata.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 */
	public function onAjaxSampledataApplyStep1()
	{
		if ($this->app->input->get('type') != $this->_name)
		{
			return;
		};

		$response = new stdClass;
		$response->success     = true;
		$response->message     = JText::_('PLG_SAMPLEDATA_DEFAULT_STEP1');

		return $response;
	}
}
