<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Joomla! udpate notification plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Quickicon.Joomla
 * @since		1.7.1
 */
class plgQuickiconJoomlaupdate extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.7.1
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 * 
	 * @return array An icon definition associative array, consisting of the
	 *				 keys link, image, text and access.
	 */
	public function onGetIcon()
	{
		$cur_template = JFactory::getApplication()->getTemplate();
		$ajax_url = JURI::base().'index.php?option=com_installer&view=update&task=update.ajax';
		$script = "var plg_quickicon_joomlaupdate_ajax_url = '$ajax_url';\n";
		$script .= 'var plg_quickicon_jupdatecheck_jversion = "'.JVERSION.'";'."\n";
		$script .= 'var plg_quickicon_joomlaupdate_text = {"UPTODATE" : "'.
			JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE').'", "UPDATEFOUND": "'.
			JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND').'", "ERROR": "'.
			JText::_('PLG_QUICKICON_JOOMLAUPDATE_ERROR')."\"};\n";
		$script .= 'var plg_quickicon_joomlaupdate_img = {"UPTODATE" : "'.
			JURI::base(true) .'/templates/'. $cur_template .'/images/header/icon-48-checkin.png'.'", "ERROR": "'.
			JURI::base(true) .'/templates/'. $cur_template .'/images/header/icon-48-deny.png'.'", "UPDATEFOUND": "'.
			JURI::base(true) .'/templates/'. $cur_template .'/images/header/icon-48-alert.png'."\"};\n";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);
		$document->addScript(JURI::base().'../media/plg_quickicon_joomlaupdate/jupdatecheck.js');
		
		return array(
			'link' => 'index.php?option=com_installer&view=update',
			'image' => 'header/icon-48-download.png',
			'text' => JText::_('PLG_QUICKICON_JOOMLAUPDATE_CHECKING'),
			'access' => array('core.manage', 'com_installer'),
			'id' => 'plg_quickicon_joomlaupdate'
		);
	}
}