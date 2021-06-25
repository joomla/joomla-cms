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
		$shortcut_button_apply = $this->params->get('shortcut_button_apply', 's');
		$shortcut_button_new = $this->params->get('shortcut_button_new', 'n');
		$shortcut_button_save = $this->params->get('shortcut_button_save', 'w');
		$shortcut_button_saveNew = $this->params->get('shortcut_button_saveNew', 'n');
		$shortcut_button_help = $this->params->get('shortcut_button_help', 'h');
		$shortcut_button_cancel = $this->params->get('shortcut_button_cancel', 'q');
		$shortcut_button_copy = $this->params->get('shortcut_button_copy', 'c');

		$shortcut = array(
			'button_apply' => array(
			  'keyEvent' => $shortcut_button_apply,
			  'selector' => 'joomla-toolbar-button button.button-apply'
			),
			'button_new' => array(
			  'keyEvent' => $shortcut_button_new,
			  'selector' => 'joomla-toolbar-button button.button-new'
			),
			'button_save' => array(
			  'keyEvent' => $shortcut_button_save,
			  'selector' => 'joomla-toolbar-button button.button-save'
			),
			'button_saveNew' => array(
			  'keyEvent' => $shortcut_button_saveNew,
			  'selector' => 'joomla-toolbar-button button.button-save-new'
			),
			'button_help' => array(
			  'keyEvent' => $shortcut_button_help,
			  'selector' => 'joomla-toolbar-button button.button-help'
			),
			'button_cancel' => array(
			  'keyEvent' => $shortcut_button_cancel,
			  'selector' => 'joomla-toolbar-button button.button-cancel'
			),
			'button_copy' => array(
			  'keyEvent' => $shortcut_button_copy,
			  'selector' => 'joomla-toolbar-button button.button-button-copy'
			)

		  );

		if ($this->app->isClient('administrator'))
		{
			$wa = $this->app->getDocument()->getWebAssetManager();
			Factory::getDocument()->addScriptOptions('system-shortcut', $shortcut);

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