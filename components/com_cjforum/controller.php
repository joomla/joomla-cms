<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumController extends JControllerLegacy
{

	public function __construct ($config = array())
	{
		$this->input = JFactory::getApplication()->input;
		
		// Topic frontpage Editor pagebreak proxying:
		if ($this->input->get('view') === 'topic' && $this->input->get('layout') === 'pagebreak')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		// Topic frontpage Editor topic proxying:
		elseif ($this->input->get('view') === 'topics' && $this->input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		
		parent::__construct($config);
	}

	public function display ($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		
		$cachable = true;
		$custom_tag = true;
		
		// Set the default view name and format from the Request.
		// Note we are using t_id to avoid collisions with the router and the
		// return page.
		// Frontend is a bit messier than the backend.
		$id = $this->input->getInt('t_id');
		$replyId = $this->input->getInt('r_id');
		$vName = $this->input->getCmd('view', 'categories');
		$this->input->set('view', $vName);
		
		if ( $user->get('id') || in_array($vName, array('profile')))
		{
			$cachable = false;
		}
		
		$safeurlparams = array(
				'catid' => 'INT',
				'id' => 'INT',
				'topic_id' => 'INT',
				't_id' => 'INT',
				'r_id' => 'INT',
				'cid' => 'ARRAY',
				'year' => 'INT',
				'month' => 'INT',
				'limit' => 'UINT',
				'limitstart' => 'UINT',
				'showall' => 'INT',
				'return' => 'BASE64',
				'filter' => 'STRING',
				'filter_order' => 'CMD',
				'filter_order_Dir' => 'CMD',
				'filter-search' => 'STRING',
				'print' => 'BOOLEAN',
				'lang' => 'CMD',
				'Itemid' => 'INT'
		);
		
		// Check for edit form.
		if ($vName == 'form' && ! $this->checkEditId('com_cjforum.edit.topic', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}
		elseif ($vName == 'reply' && ! $this->checkEditId('com_cjforum.edit.reply', $replyId))
		{
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}
		
		if($vName == 'form')
		{
			JHtml::_('behavior.framework');
		}
		
		$params = JComponentHelper::getParams('com_cjforum');
		$loadBsCss = $params->get('load_bootstrap_css', false);
		
		if($loadBsCss)
		{
			CjLib::behavior('bootstrap', array('loadcss' => $loadCss, 'customtag'=>$custom_tag));
		}
		
		CJLib::behavior('bscore', array('customtag'=>$custom_tag));
		CJFunctions::load_jquery(array('libs'=>array('fontawesome'), 'custom_tag'=>$custom_tag));
		
		if ($vName == 'profileform')
		{
		    CJFunctions::add_script(JUri::root(true).'/media/system/js/tabs-state.js', $custom_tag);
		    CJFunctions::add_script(JUri::root(true).'/media/system/js/validate.js', $custom_tag);
			CJFunctions::add_script(JUri::root(true).'/media/com_cjforum/js/jquery.guillotine.js', $custom_tag);
		}
		else if ($vName == 'topic')
		{
			CJFunctions::load_jquery(array('libs'=>array('colorbox'), 'custom_tag'=>$custom_tag));
		}
		
		CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjforum/css/cj.forum.min.css', $custom_tag);
		CJFunctions::add_script(JUri::root(true).'/media/com_cjforum/js/cj.forum.min.js', $custom_tag);

		parent::display($cachable, $safeurlparams);
		return $this;
	}
}
