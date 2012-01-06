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
 * @since   2.5
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
	 * @param	bool		If the content has just been created
	 * @since	2.5
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('search');

		// Trigger the onSearchAfterSave event.
		$results = $dispatcher->trigger('onSearchAfterSave', array($context, $article, $isNew));

	}
	/**
	 * Finder before save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since   2.5
	 */
	public function onContentBeforeSave($context, $article, $isNew)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('search');

		// Trigger the onSearchBeforeSave event.
		$results = $dispatcher->trigger('onSearchBeforeSave', array($context, $article, $isNew));

	}
	/**
	 * Finder after delete content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @since   2.5
	 */
	public function onContentAfterDelete($context, $article)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('search');

		// Trigger the onSearchAfterDelete event.
		$results = $dispatcher->trigger('onSearchAfterDelete', array($context, $article));

	}
	/**
	 * Finder change state content method
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 * @since   2.5
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('search');

		// Trigger the onSearchChangeState event.
		$results = $dispatcher->trigger('onSearchChangeState', array($context, $pks, $value));
	}

	/**
	 * Finder change category state content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string   $extension  The extension whose category has been updated.
	 * @param   array    $pks        A list of primary key ids of the content that has changed state.
	 * @param   integer  $value      The value of the state that the content has been changed to.
	 * @since   2.5
	 */
	public function onCategoryChangeState($extension, $pks, $value)
	{
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('search');

		// Trigger the onSearchCategoryChangeState event.
		$dispatcher->trigger('onSearchCategoryChangeState', array($extension, $pks, $value));

	}
}
