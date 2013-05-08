<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Articles list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class ContentControllerArticles extends JControllerAdmin
{
	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 * @since  3.1
	 */
	protected $redirectUrl = 'index.php?option=com_content&view=articles';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_content';

	/*
	 * @var  string Model name
	 * @since  3.1
	 */
	protected $name = 'Article';

	/*
	 * @var  string   Model prefix
	 * @since  3.1
	 */
	protected $prefix = 'ContentModel';

	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.

	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unfeatured', 'featured');

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   string  $config  Array of configuration options
	 *
	 * @return  JModel
	 * @since   1.6
	 */
	public function getModel($name = 'Article', $prefix = 'ContentModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
