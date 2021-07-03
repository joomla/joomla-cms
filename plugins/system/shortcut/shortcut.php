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
use Joomla\CMS\HTML\HTMLHelper;

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
		if ($this->app->isClient('administrator'))
		{
			$joomla_shortcut_keys = array(
				'new' => array(
				  'keyEvent' => $this->params->get('new_keyEvent', 'n'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-new'
				),
				'apply' => array(
				  'keyEvent' => $this->params->get('apply_keyEvent', 's'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-apply'
				),
				'save' => array(
				  'keyEvent' => $this->params->get('save_keyEvent', 'w'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-save'
				),
				'saveNew' => array(
				  'keyEvent' => $this->params->get('saveNew_keyEvent', 'n'),
				  'hasShift' => 1,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-save-new'
				),
				'help' => array(
				  'keyEvent' => $this->params->get('help_keyEvent', 'x'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-help'
				),
				'cancel' => array(
				  'keyEvent' => $this->params->get('cancel_keyEvent', 'q'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-cancel'
				),
				'copy' => array(
				  'keyEvent' => $this->params->get('copy_keyEvent', 'c'),
				  'hasShift' => 1,
				  'hasAlt' => 1,
				  'hasControl' => 0,
				  'selector' => 'joomla-toolbar-button button.button-button-copy'
				),
				'article' => array(
				  'keyEvent' => $this->params->get('article_keyEvent', 'a'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~article_modal'
				),
				'contact' => array(
				  'keyEvent' => $this->params->get('contact_keyEvent', 'c'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~contact_modal'
				),
				'fields' => array(
				  'keyEvent' => $this->params->get('fields_keyEvent', 'f'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~fields_modal'
				),
				'image' => array(
				  'keyEvent' => $this->params->get('image_keyEvent', 'i'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~image_modal'
				),
				'menu' => array(
				  'keyEvent' => $this->params->get('menu_keyEvent', 'm'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~menu_modal'
				),
				'module' => array(
				  'keyEvent' => $this->params->get('module_keyEvent', 'm'),
				  'hasShift' => 1,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~module_modal'
				),
				'pagebreak' => array(
				  'keyEvent' => $this->params->get('pagebreak_keyEvent', 'p'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~pagebreak_modal'
				),
				'readmore' => array(
				  'keyEvent' => $this->params->get('readmore_keyEvent', 'r'),
				  'hasShift' => 0,
				  'hasAlt' => 1,
				  'hasControl' => 1,
				  'selector' => 'joomla-editor-option~read_more'
				)
			);
			$wa = $this->app->getDocument()->getWebAssetManager();
			$config = $this->app->getConfig();
			$editor = $config->get('editor', 'tinymce');
			Factory::getDocument()->addScriptOptions('editor', $editor);
			Factory::getDocument()->addScriptOptions('joomla-shortcut-keys', $joomla_shortcut_keys);

			if (!$wa->assetExists('script', 'shortcut'))
			{
				$wa->registerScript('shortcut', $this->_basePath . '/js/shortcut.js', [], ['defer' => true , 'type' => 'module']);
			}

			$wa->useScript('shortcut');

			return true;
		}

		return true;
	}
}