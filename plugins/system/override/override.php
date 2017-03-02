<?php
defined('_JEXEC') or die;

class PlgSystemOverride extends JPlugin
{
	public function onAfterInitialise()
	{
		// Create an alias for JModelList on getItems() the following code is executed:
		// JFactory::getApplication()->enqueueMessage('Override called!!');
		JLoader::register('JModelList', __DIR__ . '/NoneNsListModel.php');
	}
}
