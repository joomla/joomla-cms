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
use Joomla\CMS\Language\Text;
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
	 * @param   Event $event The event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addShortcuts(Event $event)
	{
		$shortcuts = $event->getArgument('shortcuts');

		$keys = [
			'helpKey'            => (object) ['selector' => 'joomla-toolbar-button .button-help', 'default' => 'ALT + X'],
			'newKey'             => (object) ['selector' => 'joomla-toolbar-button .button-new', 'default' => 'ALT + N'],
			'applyKey'           => (object) ['selector' => 'joomla-toolbar-button .button-apply', 'default' => 'ALT + S'],
			'saveKey'            => (object) ['selector' => 'joomla-toolbar-button .button-save', 'default' => 'ALT + W'],
			'saveNewKey'         => (object) ['selector' => 'joomla-toolbar-button .button-save-new', 'default' => 'SHIFT + ALT + N'],
			'cancelKey'          => (object) ['selector' => 'joomla-toolbar-button .button-cancel', 'default' => 'ALT + Q'],
			'optionKey'          => (object) ['selector' => 'joomla-toolbar-button .button-options', 'default' => 'ALT + O'],
			'searchKey'          => (object) ['selector' => 'input[placeholder=' . Text::_('JSEARCH_FILTER') . ']', 'default' => 'S'],
			'editorArticleKey'   => (object) ['selector' => 'joomla-editor-option ~ article_modal', 'default' => 'CTRL + ALT + A'],
			'editorContactKey'   => (object) ['selector' => 'joomla-editor-option ~ contact_modal', 'default' => 'CTRL + ALT + C'],
			'editorFieldsKey'    => (object) ['selector' => 'joomla-editor-option ~ fields_modal', 'default' => 'CTRL + ALT + F'],
			'editorImageKey'     => (object) ['selector' => 'joomla-editor-option ~ image_modal', 'default' => 'CTRL + ALT + I'],
			'editorMenuKey'      => (object) ['selector' => 'joomla-editor-option ~ menu_modal', 'default' => 'CTRL + ALT + M'],
			'editorModuleKey'    => (object) ['selector' => 'joomla-editor-option ~ module_modal', 'default' => 'CTRL + SHIFT + ALT + M'],
			'editorPagebreakKey' => (object) ['selector' => 'joomla-editor-option ~ pagebreak_modal', 'default' => 'CTRL + ALT + P'],
			'editorReadmoreKey'  => (object) ['selector' => 'joomla-editor-option ~ read_more', 'default' => 'CTRL + ALT + R'],
		];

		foreach ($keys as $name => $key)
		{
			$shortcut = $this->params->get($name, $key->default);

			if ($shortcut)
			{
				$shortcuts[$shortcut] = $key->selector;
			}
		}

		$event->setArgument('shortcuts', $shortcuts);
	}
}
