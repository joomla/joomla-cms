<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;

/**
 * Shortcut plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemShortcut extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.1
	 */
	protected $app;

	/**
	 * Base path for keyboard shortcut
	 *
	 * @var    string
	 * @since  4.1
	 */
	protected $_basePath = 'media/plg_system_shortcut';

	public function onBeforeCompileHead()
	{
		if ($this->app->isClient('administrator')) {
			$joomla_shortcut_keys = array(
				'new' => array(
					'keyEvent' => $this->params->get('new_keyEvent', 'n'),
					'hasShift' => $this->params->get('new_hasShift', 0),
					'hasAlt' => $this->params->get('new_hasAlt', 1),
					'hasControl' => $this->params->get('new_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-new'
				),
				'apply' => array(
					'keyEvent' => $this->params->get('apply_keyEvent', 's'),
					'hasShift' => $this->params->get('apply_hasShift', 0),
					'hasAlt' => $this->params->get('apply_hasAlt', 1),
					'hasControl' => $this->params->get('apply_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-apply'
				),
				'save' => array(
					'keyEvent' => $this->params->get('save_keyEvent', 'w'),
					'hasShift' => $this->params->get('save_hasShift', 0),
					'hasAlt' => $this->params->get('save_hasAlt', 1),
					'hasControl' => $this->params->get('save_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-save'
				),
				'saveNew' => array(
					'keyEvent' => $this->params->get('saveNew_keyEvent', 'n'),
					'hasShift' => $this->params->get('saveNew_hasShift', 1),
					'hasAlt' => $this->params->get('saveNew_hasAlt', 1),
					'hasControl' => $this->params->get('saveNew_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-save-new'
				),
				'help' => array(
					'keyEvent' => $this->params->get('help_keyEvent', 'x'),
					'hasShift' => $this->params->get('help_hasShift', 0),
					'hasAlt' => $this->params->get('help_hasAlt', 1),
					'hasControl' => $this->params->get('help_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-help'
				),
				'cancel' => array(
					'keyEvent' => $this->params->get('cancel_keyEvent', 'q'),
					'hasShift' => $this->params->get('cancel_hasShift', 0),
					'hasAlt' => $this->params->get('cancel_hasAlt', 1),
					'hasControl' => $this->params->get('cancel_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-cancel'
				),
				'copy' => array(
					'keyEvent' => $this->params->get('copy_keyEvent', 'c'),
					'hasShift' => $this->params->get('copy_hasShift', 1),
					'hasAlt' => $this->params->get('copy_hasAlt', 1),
					'hasControl' => $this->params->get('copy_hasControl', 0),
					'selector' => 'joomla-toolbar-button button.button-button-copy'
				),
				'article' => array(
					'keyEvent' => $this->params->get('article_keyEvent', 'a'),
					'hasShift' => $this->params->get('article_hasShift', 0),
					'hasAlt' => $this->params->get('article_hasAlt', 1),
					'hasControl' => $this->params->get('article_hasControl', 1),
					'selector' => 'joomla-editor-option~article_modal'
				),
				'contact' => array(
					'keyEvent' => $this->params->get('contact_keyEvent', 'c'),
					'hasShift' => $this->params->get('contact_hasShift', 0),
					'hasAlt' => $this->params->get('contact_hasAlt', 1),
					'hasControl' => $this->params->get('contact_hasControl', 1),
					'selector' => 'joomla-editor-option~contact_modal'
				),
				'fields' => array(
					'keyEvent' => $this->params->get('fields_keyEvent', 'f'),
					'hasShift' => $this->params->get('fields_hasShift', 0),
					'hasAlt' => $this->params->get('fields_hasAlt', 1),
					'hasControl' => $this->params->get('fields_hasControl', 1),
					'selector' => 'joomla-editor-option~fields_modal'
				),
				'image' => array(
					'keyEvent' => $this->params->get('image_keyEvent', 'i'),
					'hasShift' => $this->params->get('image_hasShift', 0),
					'hasAlt' => $this->params->get('image_hasAlt', 1),
					'hasControl' => $this->params->get('image_hasControl', 1),
					'selector' => 'joomla-editor-option~image_modal'
				),
				'menu' => array(
					'keyEvent' => $this->params->get('menu_keyEvent', 'm'),
					'hasShift' => $this->params->get('menu_hasShift', 0),
					'hasAlt' => $this->params->get('menu_hasAlt', 1),
					'hasControl' => $this->params->get('menu_hasControl', 1),
					'selector' => 'joomla-editor-option~menu_modal'
				),
				'module' => array(
					'keyEvent' => $this->params->get('module_keyEvent', 'm'),
					'hasShift' => $this->params->get('module_hasShift', 1),
					'hasAlt' => $this->params->get('module_hasAlt', 1),
					'hasControl' => $this->params->get('module_hasControl', 1),
					'selector' => 'joomla-editor-option~module_modal'
				),
				'pagebreak' => array(
					'keyEvent' => $this->params->get('pagebreak_keyEvent', 'p'),
					'hasShift' => $this->params->get('pagebreak_hasShift', 0),
					'hasAlt' => $this->params->get('pagebreak_hasAlt', 1),
					'hasControl' => $this->params->get('pagebreak_hasControl', 1),
					'selector' => 'joomla-editor-option~pagebreak_modal'
				),
				'readmore' => array(
					'keyEvent' => $this->params->get('readmore_keyEvent', 'r'),
					'hasShift' => $this->params->get('readmore_hasShift', 0),
					'hasAlt' => $this->params->get('readmore_hasAlt', 1),
					'hasControl' => $this->params->get('readmore_hasControl', 1),
					'selector' => 'joomla-editor-option~read_more'
				)
			);
			$wa = $this->app->getDocument()->getWebAssetManager();
			$config = $this->app->getConfig();
			$editor = $config->get('editor', 'tinymce');

			$document = $this->app->getDocument();
			$document->addScript(JURI::Root().'media/plg_system_shortcut/js/mousetrap.min.js');
			$document->addScriptOptions('editor', $editor);
			$document->addScriptOptions('joomla-shortcut-keys', $joomla_shortcut_keys);
			$document->addScript(JURI::Root().'media/plg_system_shortcut/js/shortcut.js');
			
			return true;
		}

		return true;
	}
}
