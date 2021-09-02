<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
/**
 * Joomla! Plugin Class for Shortcut plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemShortcut extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Base path for keyboard shortcut
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_basePath = 'media/plg_system_shortcut';

	/**
	 * Add a shortcut keys for Shortcut
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		if (!$this->app->isClient('administrator'))
		{
			return true;
		}

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
				),
			'articles' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'a'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_categories' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'c'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_fields' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'f'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_sitemodules' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'm'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_adminmodules' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'a'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 1),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_banners' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'b'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_contacts' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'c'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 1),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_newsfeeds' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'n'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', ),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_smartsearch' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 's'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_tags' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 't'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			),
			'com_users' => array(
				'keyEvent' => $this->params->get('new_keyEvent', 'u'),
				'hasShift' => $this->params->get('new_hasShift', 1),
				'hasAlt' => $this->params->get('new_hasAlt', 0),
				'hasControl' => $this->params->get('new_hasControl', 1),
				'selector' => 'joomla-toolbar-button button.button-new'
			)
			);

			$wa = $this->app->getDocument()->getWebAssetManager();
			$config = $this->app->getConfig();
			$editor = $config->get('editor', 'tinymce');
			$document = $this->app->getDocument();
			$document->addScriptOptions('editor', $editor);
			$document->addScriptOptions('joomla-shortcut-keys', $joomla_shortcut_keys);

		if (!$wa->assetExists('script', 'shortcut'))
		{
			$wa->registerScript('shortcut', $this->_basePath . '/js/shortcut.js', [], ['defer' => true , 'type' => 'module']);
		}

			$wa->useScript('shortcut');

			return true;
	}
}
