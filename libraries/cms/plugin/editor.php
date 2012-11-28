<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.abstract
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Abstract Editor Plugin Definition
 *
 * @package     Joomla
 * @subpackage  Editors.Plugin
 * @since       1.5
 */
abstract class JPluginEditor extends JPlugin
{
	/**
	 * Base path for any editor webfiles - css, js, etc
	 */
	protected $_basePath = 'media/editors/';

	/**
	 * Constructor
	 *
	 * @param  object  $subject  The object to observe
	 * @param  array   $config   An array that holds the plugin configuration
	 *
	 * @since       1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Returns a string to be inserted into the head of the document
	 * Can also be used to insert scripts and styles into the head
	 *
	 * @return  string  JavaScript Initialization string
	 *
	 * @since 1.5
	 */
	abstract public function onInit();

	/**
	 * Javascript function call string to provide for other objects to
	 * invoke when they need the editor contents.  ex: the readmore button
	 * will call this function when it is added to a form to build
	 * the javascript it uses to to get the content to modify
	 * The javascript function must return a string and be suitable
	 * for placement on the right hand side of an assignment including an ending semi
	 * colon!
	 * ie: var content = [onGetContent string]
	 *
	 * @param  string  The name of the editors css id
	 *
	 * @return string
	 */
	abstract public function onGetContent($editor);

	/**
	 * Javascript fubnction to be called to set the editor content.  For example if other fields on the form modify the editor content, they will call this through Joomla in order to update the content.
	 *
	 * @param   string  The name of the editor
	 *
	 * @return  string
	 */
	abstract public function onSetContent($editor, $html);

	/**
	 * Javascript function to be called for a specific editor instance when the form is submitted
	 *
	 * @param   string  The name of the editors css id
	 *
	 * @return  string
	 */
	abstract public function onSave($editor);

	/**
	 * Javascript function the editor will insert into the page in order to redefine the Joomla jInsertEditorText function.
	 *
	 * @return  boolean
	 */
	abstract public function onGetInsertMethod($name);

	/**
	 * Display the editor area..  This is the HTML used to display the editor on the screen
	 *
	 * @param   string   The name of the editor area.
	 * @param   string   The content of the field as passed through htmlspecialchars
	 * @param   string   The width of the editor area.
	 * @param   string   The height of the editor area.
	 * @param   int      The number of columns for the editor area.
	 * @param   int      The number of rows for the editor area.
	 * @param   boolean  True and the editor buttons will be displayed.
	 * @param   string   An optional ID for the textarea. If not supplied the name is used.
	 *
	 * @return  string
	 */
	abstract public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null);



}
