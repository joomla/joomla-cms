<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Newsfeeds list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsControllerNewsfeeds extends JControllerAdmin
{
	protected $_context = 'com_newsfeeds';
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('archive',		'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->setURL('index.php?option=com_newsfeeds&view=newsfeeds');
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Newsfeed', $prefix = 'NewsfeedsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}