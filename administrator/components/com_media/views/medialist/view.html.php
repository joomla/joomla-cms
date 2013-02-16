<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since 1.0
 */
class MediaViewMediaList extends JViewLegacy
{
	function display($tpl = null)
	{
		// Do not allow cache
		JResponse::allowCache(false);

		$app	= JFactory::getApplication();
		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$lang	= JFactory::getLanguage();

		JHtml::_('behavior.framework', true);

		$document = JFactory::getDocument();
		$document->addStyleSheet('../media/media/css/medialist-'.$style.'.css');
		if ($lang->isRTL()) :
			$document->addStyleSheet('../media/media/css/medialist-'.$style.'_rtl.css');
		endif;

		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			window.parent.document.updateUploader();
			$$('a.img-preview').each(function(el) {
				el.addEvent('click', function(e) {
					new Event(e).stop();
					window.top.document.preview.fromElement(el);
				});
			});
		});");

		$images = $this->get('images');
		$documents = $this->get('documents');
		$folders = $this->get('folders');
		$state = $this->get('state');

		$this->baseURL = JURI::root();
		$this->assignRef('images', $images);
		$this->assignRef('documents', $documents);
		$this->assignRef('folders', $folders);
		$this->assignRef('state', $state);

		parent::display($tpl);
	}

	function setFolder($index = 0)
	{
		if (isset($this->folders[$index])) {
			$this->_tmp_folder = &$this->folders[$index];
		} else {
			$this->_tmp_folder = new JObject;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index])) {
			$this->_tmp_img = &$this->images[$index];
		} else {
			$this->_tmp_img = new JObject;
		}
	}

	function setDoc($index = 0)
	{
		if (isset($this->documents[$index])) {
			$this->_tmp_doc = &$this->documents[$index];
		} else {
			$this->_tmp_doc = new JObject;
		}
	}
}
