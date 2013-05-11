<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Template style controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesControllerStyle extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_TEMPLATES_STYLE';
	/*
	 * @var  string Model name
	* @since  3.1
	*/
	protected $modelName = 'Style';

	/**
	 * @var    string  The URL option for the component.
	 * @since  3.1
	 */
	protected $option = 'com_templates';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	*/
	protected $redirectUrl = 'index.php?option=com_templates&view=styles';

}
