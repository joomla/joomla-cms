<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ContentView extends JView
{
	function __construct($config = array())
	{
		parent::__construct($config);

		//Add the helper path to the JHTML library
		JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
	}
}
