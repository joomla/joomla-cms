<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Shortcut plugin to add accessible keyboard shortcuts to the administrator templates.
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

		$context = $this->app->input->get('option') . '.' . $this->app->input->get('view');

		$shortcuts = [];

		$event = new GenericEvent('onLoadShortcuts', [
			'context' => $context,
			'shortcuts' => $shortcuts
			]
		);

		$this->app->getDispatcher()->dispatch('onLoadShortcuts', $event);

		$shortcuts = $event->getArgument('shortcuts');

		$wa = $this->app->getDocument()->getWebAssetManager();

		$wa->registerAndUseScript('script', 'plg_system_shortcut/shortcut.min.js', ['dependencies' => ['hotkeys.js']]);

		$this->app->getDocument()->addScriptOptions('plg_system_shortcut.shortcuts', $shortcuts);
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
		$shortcuts = $event->getArgument('shortcuts');

		$keys = [
			'helpKey'            => 'joomla-toolbar-button .button-help',
			'newKey'             => 'joomla-toolbar-button .button-new',
			'applyKey'           => 'joomla-toolbar-button .button-apply',
			'saveKey'            => 'joomla-toolbar-button .button-save',
			'saveNewKey'         => 'joomla-toolbar-button .button-save-new',
			'cancelKey'          => 'joomla-toolbar-button .button-cancel',
			'optionKey'          => 'joomla-toolbar-button .button-options',
			'editorArticleKey'   => 'joomla-editor-option ~ article_modal',
			'editorContactKey'   => 'joomla-editor-option ~ contact_modal',
			'editorFieldsKey'    => 'joomla-editor-option ~ fields_modal',
			'editorImageKey'     => 'joomla-editor-option ~ image_modal',
			'editorMenuKey'      => 'joomla-editor-option ~ menu_modal',
			'editorModuleKey'    => 'joomla-editor-option ~ module_modal',
			'editorPagebreakKey' => 'joomla-editor-option ~ pagebreak_modal',
			'editorReadmoreKey'  => 'joomla-editor-option ~ read_more',
		];

		foreach ($keys as $key => $selector)
		{
			$shortcut = $this->params->get($key);
			if ($shortcut)
			{
				$shortcuts[$shortcut] = $selector;
			}
		}

		$event->setArgument('shortcuts', $shortcuts);
	}
}
