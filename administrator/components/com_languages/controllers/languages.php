<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 */
class LanguagesControllerLanguages extends JControllerAdmin
{
	protected $_context = 'com_languages';
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('disable',		'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('unfeatured',	'featured');
		$this->setURL('index.php?option=com_languages&view=languages');
		$this->_option = 'com_languages';
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Languages', $prefix = 'LanguagesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}