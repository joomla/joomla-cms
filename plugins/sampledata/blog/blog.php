<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.Blog
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Sampledata - Blog Plugin
 *
 * @since  3.5
 */
class PlgSampledataBlog extends JPlugin
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
		$data->title       = 'Blog Sampledata';
		$data->description = 'Sampledata which will set up a blog site.';
		$data->icon        = 'broadcast';
		$data->steps       = 5;

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
		$response->message     = JText::_('PLG_SAMPLEDATA_BLOG_STEP1');

		return $response;
	}
}
