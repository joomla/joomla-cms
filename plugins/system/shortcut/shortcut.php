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
		$shortcut = array(
			'button_apply' => array(
			  'keyEvent' => 'meta+alt+s',
			  'selector' => 'joomla-toolbar-button button.button-apply'
			),
			'button_new' => array(
			  'keyEvent' => 'meta+alt+n',
			  'selector' => 'joomla-toolbar-button button.button-new'
			),
			'button_save' => array(
			  'keyEvent' => 'meta+alt+w',
			  'selector' => 'joomla-toolbar-button button.button-save'
			),
			'button_saveNew' => array(
			  'keyEvent' => 'meta+shift+alt+w',
			  'selector' => 'joomla-toolbar-button button.button-save-new'
		),
		'button_help' => array(
			  'keyEvent' => 'meta+alt+x',
			  'selector' => 'joomla-toolbar-button button.button-help'
		),
		'button_cancel' => array(
			  'keyEvent' => 'meta+alt+q',
			  'selector' => 'joomla-toolbar-button button.button-cancel'
		),
		'button_copy' => array(
			  'keyEvent' => 'meta+shift+alt+c',
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
