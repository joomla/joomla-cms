<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.jooa11y
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * Jooa11y plugin to add an accessibility checker
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemJooa11y extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplicationInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Subscribe to certain events
	 *
	 * @return string[]  An array of event mappings
	 *
	 * @since __DEPLOY_VERSION__
	 *
	 * @throws Exception
	 */
	public static function getSubscribedEvents(): array
	{
		$mapping = [];

		// Only trigger in frontend
		if (Factory::getApplication()->isClient('site'))
		{
			$mapping['onBeforeCompileHead'] = 'initJooa11y';
		}

		return $mapping;
	}

  	/**
	 * Method to check if the current user is allowed to see the debug information or not.
	 *
	 * @return  boolean  True if access is allowed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isAuthorisedDisplayChecker(): bool
	{
		static $result;

		if (is_bool($result))
		{
			return $result;
		}

		// If the user is not allowed to view the output then end here.
		$filterGroups = (array) $this->params->get('filter_groups', []);

		if (!empty($filterGroups))
		{
			$userGroups = $this->app->getIdentity()->get('groups');

			if (!array_intersect($filterGroups, $userGroups))
			{
				$result = false;

				return $result;
			}
		}

		$result = true;

		return $result;
	}

	/**
	 * Add the checker.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function initJooa11y()
	{
		// Check if we are in a modal or the plugin enforce loading
		$showJooa11y = $this->app->input->get('jooa11y', $this->params->get('showAlways', 0));

		// Load the checker if authorised
		if (!$showJooa11y || !$this->isAuthorisedDisplayChecker())
		{
			return;
		}

		// Get the document object.
		$document = $this->app->getDocument();

		// Determine if it is an LTR or RTL language
		$direction = Factory::getLanguage()->isRtl() ? 'right' : 'left';

		// Detect the current active language
		$lang = Factory::getLanguage()->getTag();

		// Add plugin settings from the xml
		$document->addScriptOptions(
			'jooa11yOptions',
			[
				// Language
				'langCode'        => $this->params->get('langCode', 'en'),
				'readabilityLang' => $this->params->get('readabilityLang', 'en'),
				// Advanced Fieldset
				'checkRoot'       => $this->params->get('checkRoot', 'main'),
				'readabilityRoot' => $this->params->get('readabilityRoot', 'main'),
				'containerIgnore' => $this->params->get('containerIgnore'),
				'outlineIgnore'   => $this->params->get('outlineIgnore'),
				'headerIgnore'    => $this->params->get('headerIgnore'),
				'imageIgnore'     => $this->params->get('imageIgnore'),
				'linkIgnore'      => $this->params->get('linkIgnore'),
				'linkIgnoreSpan'  => $this->params->get('linkIgnore'),
				'linksToFlag'     => $this->params->get('linkIgnore'),
				// Start up preferences
				'contrast'        => $this->params->get('contrast'),
				'labels'          => $this->params->get('labels'),
				'links_advanced'  => $this->params->get('links_advanced'),
				'readability'     => $this->params->get('readability'),
				'darkmode'        => $this->params->get('darkmode'),
			]
		);

		$this->attachLanguageStrings();

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa*/
		$wa = $document->getWebAssetManager();

		$wa->getRegistry()->addRegistryFile('media/plg_system_jooa11y/joomla.asset.json');

		$wa->usePreset('plg_system_jooa11y.jooa11y');

		return true;

	}

	/**
	 * Attach the language string to JS options
	 *
	 * @return void
	 */
	protected function attachLanguageStrings()
	{
		// @todo Text::script(...);
	}
}
