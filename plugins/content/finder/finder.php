<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Finder Content Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.finder
 * @since		1.6
 */
class plgContentFinder extends JPlugin
{
	/**
	 * Finder after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	2.5
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder', $context);

		// Trigger the onCategoryChangeState event.
		$results = $dispatcher->trigger('onFinderAfterSave', array($context, $pks, $value));

	}
	/**
	 * Finder before save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onContentBeforeSave($context, $article, $isNew)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');

		// Trigger the onFinderBeforeSave event.
		$results = $dispatcher->trigger('onFinderBeforeSave', array($context, $article, $isNew));

	}
	/**
	 * Finder after delete content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onContentAfterDelete($context, $article, $isNew)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder', $context);

		// Trigger the onFinderAfterState event.
		$results = $dispatcher->trigger('onFinderAfterDelete', array($context, $article, $isNew));

	}
	/**
	 * Finder change state content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder', $context);

		// Trigger the onCategoryChangeState event.
		$results = $dispatcher->trigger('onFinderChangeState', array($context, $pks, $value));
	}

	/**
	 * Finder change category state content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onCategoryChangeState($extension, $pks, $value)
	{
		JPluginHelper::importPlugin('finder');

		// Trigger the onCategoryChangeState event.
		$dispatcher->trigger('onFinderCategoryChangeState', array($context, $pks, $value));

	}
}
