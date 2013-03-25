<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Example Content Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.example
 * @since		1.5
 */
class plgContentExample extends JPlugin
{
	/**
	 * Example after delete method.
	 *
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that was deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onContentAfterDelete($context, $data)
	{
		return true;
	}

	/**
	 * Example after display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string  The context for the content passed to the plugin.
	 * @param   object		The content object.  Note $article->text is also available
	 * @param   object		The content params
	 * @param   integer  	The 'page' number
	 * @return  string
	 * @since   1.6
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * Example after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onContentAfterSave($context, &$article, $isNew)
	{
		$app = JFactory::getApplication();

		return true;
	}

	/**
	 * Example after display title method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  Note $article->text is also available
	 * @param	object		The content params
	 * @param	int			The 'page' number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentAfterTitle($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * Example before delete method.
	 *
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that is to be deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onContentBeforeDelete($context, $data)
	{
		return true;
	}

	/**
	 * Example before display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string  The context for the content passed to the plugin.
	 * @param   object		The content object.  Note $article->text is also available
	 * @param   object		The content params
	 * @param   integer  	The 'page' number
	 * @return  string
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * Example before save content method
	 *
	 * Method is called right before content is saved into the database.
	 * Article object is passed by reference, so any changes will be saved!
	 * NOTE:  Returning false will abort the save with an error.
	 *You can set the error by calling $article->setError($message)
	 *
	 * @param	string		The context of the content passed to the plugin.
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @return	bool		If false, abort the save
	 * @since	1.6
	 */
	public function onContentBeforeSave($context, &$article, $isNew)
	{
		$app = JFactory::getApplication();

		return true;
	}

	/**
	 * Example after delete method.
	 *
	 * @param   string	The context for the content passed to the plugin.
	 * @param   array	A list of primary key ids of the content that has changed state.
	 * @param   integer  The value of the state that the content has been changed to.
	 * @return  boolean
	 * @since   1.6
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		return true;
	}

	/**
	 * Example prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param   string	The context of the content being passed to the plugin.
	 * @param   object	The content object.  Note $article->text is also available
	 * @param   object	The content params
	 * @param   integer  The 'page' number
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
	}
}
