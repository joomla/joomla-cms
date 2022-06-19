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
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

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

		$event = new GenericEvent(
			'onLoadShortcuts',
			[
				'context'   => $context,
				'shortcuts' => $shortcuts,
			]
		);

		$this->app->getDispatcher()->dispatch('onLoadShortcuts', $event);

		$shortcuts = $event->getArgument('shortcuts');

		Text::script('PLG_SYSTEM_SHORTCUT_OVERVIEW_HINT');
		Text::script('PLG_SYSTEM_SHORTCUT_OVERVIEW_TITLE');

		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->useScript('bootstrap.modal');
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
			'applyKey'   => (object) ['selector' => 'joomla-toolbar-button .button-apply', 'shortcut' => 'J + A', 'title' => Text::_('JAPPLY')],
			'cancelKey'  => (object) ['selector' => 'joomla-toolbar-button .button-cancel', 'shortcut' => 'J + Q', 'title' => Text::_('JCANCEL')],
			'helpKey'    => (object) ['selector' => 'joomla-toolbar-button .button-help', 'shortcut' => 'J + H', 'title' => Text::_('JHELP')],
			'newKey'     => (object) ['selector' => 'joomla-toolbar-button .button-new', 'shortcut' => 'J + N', 'title' => Text::_('JTOOLBAR_NEW')],
			'optionKey'  => (object) ['selector' => 'joomla-toolbar-button .button-options', 'shortcut' => 'J + O', 'title' => Text::_('JOPTIONS')],
			'saveKey'    => (object) ['selector' => 'joomla-toolbar-button .button-save', 'shortcut' => 'J + S', 'title' => Text::_('JTOOLBAR_SAVE')],
			'saveNewKey' => (object) ['selector' => 'joomla-toolbar-button .button-save-new', 'shortcut' => 'J + N', 'title' => Text::_('JTOOLBAR_SAVE_AND_NEW')],
			'searchKey'  => (object) ['selector' => 'input[placeholder=' . Text::_('JSEARCH_FILTER') . ']', 'shortcut' => 'J + S', 'title' => Text::_('JSEARCH_FILTER')],
		];

		$event->setArgument('shortcuts', $keys);
	}
}
