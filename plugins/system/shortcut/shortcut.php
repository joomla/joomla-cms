<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Shortcut plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemShortcut extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    AdministratorApplication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  - The method name to call (priority defaults to 0)
	 *  - An array composed of the method name to call and the priority
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeCompileHead' => 'initialize',
			'onLoadShortcuts' => 'addShortcuts'
		];
	}


	/**
	 * Add the javascript for the shortcuts
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function initialize()
	{
		if (!$this->app->isClient('administrator'))
		{
			return;
		}

		PluginHelper::importPlugin('shortcut');

		$context = $this->app->get('option') . '.' . $this->app->get('view');

		$shortcuts = [];

		$event = new GenericEvent('onLoadShortcuts', [
			'context' => $context,
			'shortcuts' => $shortcuts
		]);

		$this->app->getDispatcher()->dispatch('onLoadShortcuts', $event);

		$shortcuts = $event->getArgument('shortcuts');

		print_r($shortcuts);

		$wa = $this->app->getDocument()->getWebAssetManager();

		$wa->registerAndUseScript('script', 'plg_system_shortcut/shortcut.min.js', ['dependencies' => ['hotkeys.js']]);

		$this->app->getDocument()->addScriptOptions('plg_system_shortcut.shortcuts', $shortcuts);

		return true;
	}

	/**
	 * Add default shortcuts to the document
	 *
	 * @param Event $event The event
	 *
	 * @return void
	 */
	public function addShortcuts(Event $event)
	{
		$context = $event->getArgument('context');
		$shortcuts = $event->getArgument('shortcuts');

		$shortcuts['ctrl+alt+a'] = 'alert(\'test\');';

		$event->setArgument('shortcuts', $shortcuts);

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
	}
}
