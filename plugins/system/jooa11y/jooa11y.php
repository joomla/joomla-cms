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

/**
 * Jooa11y plugin to add an accessibility checker
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemJooa11y extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplicationInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

  	/**
	 * Method to check if the current user is allowed to see the debug information or not.
	 *
	 * @return  boolean  True if access is allowed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isAuthorisedDisplayChecker(): bool
	{
		static $result = null;

		if ($result !== null)
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

				return false;
			}
		}

		$result = true;

		return true;
	}

	/**
	 * Add the checker.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterDispatch()
	{
		// Only on site application
		if (!$this->app->isClient('site'))
		{
			return false;
		}

		// Get the document object.
		$document = $this->app->getDocument();

		// Only on HTML documents
		if ($document->getType() !== 'html')
		{
			return false;
		}

		// @todo Check if we are in a modal ie we pressed the toolbar button

		// Load the checker if authorised
		if (!$this->isAuthorisedDisplayChecker())
		{
			return false;
		}

		// Load language file.
		$this->loadLanguage();

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

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa
		* I know the code below can be improved - help! */

		$wa = $document->getWebAssetManager()
		->registerAndUseScript('popper', 'https://unpkg.com/@popperjs/core@2')
		->registerAndUseScript('tippy', 'https://unpkg.com/tippy.js@6')
			->registerAndUseScript('jooa11y', 'plg_system_jooa11y/joomla-a11y-checker.js')
			->registerAndUseScript('jooa11y-lang', 'plg_system_jooa11y/lang/en.js')
			->registerAndUseStyle('jooa11y', 'plg_system_jooa11y/joomla-a11y-checker.css')
			->addInlineScript("
window.addEventListener('load', () => {
    // Set translations
    Jooa11y.Lang.addI18n(Jooa11yLangEn.strings);

    // Instantiate
    const checker = new Jooa11y.Jooa11y(Jooa11yLangEn.options);
    checker.doInitialCheck();
});
", ['name' => 'jooa11y-init'], ['type' => 'module']);

		return true;

	}
}
