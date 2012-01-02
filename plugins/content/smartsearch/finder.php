<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Smartsearch Content Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.Finder
 * @since   2.5
 */
class plgContentFinder extends JPlugin
{
	/**
	 * Smartsearch after save content method
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
		JPluginHelper::importPlugin('smartsearch');

		// Trigger the onSmartsearchAfterSave event.
		$results = $dispatcher->trigger('onSmartsearchAfterSave', array($context, $article, $isNew));

	}
	/**
	 * Smartsearch before save content method
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
		JPluginHelper::importPlugin('smartsearch');

		// Trigger the onSmartsearchBeforeSave event.
		$results = $dispatcher->trigger('onSmartsearchBeforeSave', array($context, $article, $isNew));

	}
	/**
	 * Smartsearch after delete content method
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
		JPluginHelper::importPlugin('smartsearch');

		// Trigger the onSmartsearchAfterDelete event.
		$results = $dispatcher->trigger('onSmartsearchAfterDelete', array($context, $article));

	}
	/**
	 * Smartsearch change state content method
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
		JPluginHelper::importPlugin('smartsearch');

		// Trigger the onSmartsearchChangeState event.
		$results = $dispatcher->trigger('onSmartsearchChangeState', array($context, $pks, $value));
	}

	/**
	 * Smartsearch change category state content method
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
		JPluginHelper::importPlugin('smartsearch');

		// Trigger the onSmartsearchCategoryChangeState event.
		$dispatcher->trigger('onSmartsearchCategoryChangeState', array($extension, $pks, $value));

	}
}
