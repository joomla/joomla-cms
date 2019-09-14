<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookieconsent
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;


/**
 * Cookie consent plugin to add simple cookie information.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemCookieconsent extends CMSPlugin
{
	/**
	 * If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

 	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Return the url of the assigned article based on the current user language
	 *
	 * @return  string  Returns the link to the cookie policy
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getAssignedPolicylinkUrl()
	{
		$db = Factory::getDbo();

		$article = false;
		$policyArticle = $this->params->get('policylink') > 0 ? (int) $this->params->get('policylink') : 0;

		if ($policyArticle)
		{
			// Get the info from the article
			$query = $db->getQuery(true)
				->select($db->quoteName(array('id', 'catid', 'language')))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $this->params->get('policylink'));
			$db->setQuery($query);

			try
			{
				$article = $db->loadObject();
			}
			catch (ExecutionFailureException $e)
			{
				// Something at the database layer went wrong
				return Route::_(
					'index.php?option=com_content&view=article&id='
					. $this->articleid
				);
			}

			// Register ContentHelperRoute
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			if (!Associations::isEnabled())
			{
				return Route::_(
					ContentHelperRoute::getArticleRoute(
						$article->id,
						$article->catid,
						$article->language
					)
				);
			}

			$associatedArticles = Associations::getAssociations('com_content', '#__content', 'com_content.item', $article->id);
			$currentLang        = Factory::getLanguage()->getTag();

			if (isset($associatedArticles) && $currentLang !== $article->language && array_key_exists($currentLang, $associatedArticles))
			{
				return Route::_(
					ContentHelperRoute::getArticleRoute(
						$associatedArticles[$currentLang]->id,
						$associatedArticles[$currentLang]->catid,
						$associatedArticles[$currentLang]->language
					)
				);
			}

			// Association is enabled but this article is not associated
			return Route::_(
				'index.php?option=com_content&view=article&id='
					. $article->id . '&catid=' . $article->catid
					. '&lang=' . $article->language
			);
		}
	}

	/**
	 * Add the javascript and css for the cookie consent
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		$section = $this->params->get('section', 'site');

		if ($section !== 'both' && $this->app->isClient($section) !== true)
		{
			return;
		}

		// Get the document object.
		$document = $this->app->getDocument();

		if ($document->getType() !== 'html')
		{
			return;
		}

		// Load language file.
		$this->loadLanguage();

		// Get the settings from the plugin
		$position 			= $this->params->get('position', 'bottom');
		$layout 			= $this->params->get('layout', 'block');
		$bannercolour 		= $this->params->get('bannercolour', '#000000');
		$buttoncolour 		= $this->params->get('buttoncolour', '#ffffff');
		$buttontextcolour 	= $this->params->get('buttontextcolour', '#383b75');
		$bannertextcolour	= $this->params->get('bannertextcolour', '#f1d600');
		$message 			= $this->params->get('message-text', Text::_('PLG_SYSTEM_COOKIECONSENT_MESSAGE_TEXT_DEFAULT'));
		$link 				= $this->params->get('policylink-text', Text::_('PLG_SYSTEM_COOKIECONSENT_POLICY_TEXT_DEFAULT'));
		$dismiss 			= $this->params->get('button-text', Text::_('PLG_SYSTEM_COOKIECONSENT_BUTTON_TEXT_DEFAULT'));
		$valid				= $this->params->get('valid', '-1');
		$href 				= $this->getAssignedPolicylinkUrl();

		// Load the javascript and css
		HTMLHelper::_('script', 'vendor/cookieconsent/cookieconsent.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
		HTMLHelper::_('stylesheet', 'vendor/cookieconsent/cookieconsent.min.css', ['version' => 'auto', 'relative' => true]);

		// Initialise the script and apply configuration
		$document->addScriptDeclaration("document.addEventListener('DOMContentLoaded', function() {
			window.cookieconsent.initialise({
				'palette': {
					'popup': {
						'background': '$bannercolour',
						'text': '$bannertextcolour'
					},
					'button': {
						'background': '$buttoncolour',
						'text': '$buttontextcolour'
					}
				},
				'position': '$position',
				'theme': '$layout',
				'content': {
					'message': '$message',
					'dismiss': '$dismiss',
					'link': '$link',
					'href': '$href',
					'target': ''
				},
				'cookie': {
					'expiryDays': '$valid'
				}
			})
		});
		");
	}
}


