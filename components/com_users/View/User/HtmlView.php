<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\View\User;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;

/**
 * Profile view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The user object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The form object for the user contact item
	 *
	 * @var    \JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * Should we show a captcha form for the submission of the contact request?
	 *
	 * @var   bool
	 * @since 3.6.3
	 */
	protected $captchaEnabled = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		$app            = Factory::getApplication();
		$this->item     = $this->get('Item');
		$this->state    = $this->get('State');
		$this->form = $this->get('Form');
		$this->params   = $this->state->get('params');
		$user = Factory::getUser();

		/**
		 * Check for no 'access-view',
		 * - Redirect guest users to login
		 * - Deny access to logged users with 403 code
		 */
		if ($this->item->params->get('access-view') == false)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		// Process the content plugins.
		PluginHelper::importPlugin('content');
		$offset = $this->state->get('list.offset');

		$this->item = (object) $this->item;

		// Store the events for later
		$this->item->event = new \stdClass;

		$results = $app->triggerEvent('onContentAfterTitle', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentBeforeDisplay', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentAfterDisplay', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		$captchaSet = $this->item->params->get('captcha', Factory::getApplication()->get('captcha', '0'));

		foreach (PluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$this->captchaEnabled = true;
				break;
			}
		}

		$this->_prepareDocument();

		return parent::display($tpl);
	}
	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();

		$pathway->addItem($this->item->name, '');

	}
}
