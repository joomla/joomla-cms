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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

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
	 * Event to specify whether a privacy policy has been published.
	 *
	 * @param   array  &$policy  The privacy policy status data, passed by reference, with keys "published", "editLink" and "articlePublished".
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyCheckPrivacyPolicyPublished(&$policy)
	{
		$articleId = $this->params->get('privacy_article');

		if (!$articleId)
		{
			return;
		}

		// Check if the article exists in database and is published
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'state')))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('id') . ' = ' . (int) $articleId);
		$this->db->setQuery($query);

		$article = $this->db->loadObject();

		// Check if the article exists
		if (!$article)
		{
			return;
		}

		// Check if the article is published
		if ($article->state == 1)
		{
			$policy['articlePublished'] = true;
		}

		$policy['published'] = true;
		$policy['editLink']  = Route::_('index.php?option=com_content&task=article.edit&id=' . $articleId);
	}

	/**
	 * Get policylink article ID. If the site is a multilingual website and there is an associated article for the
	 * current language, ID of the associlated article will be returned.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getPolicylinkArticleId()
	{
		$policylinkArticleId = $this->params->get('policylink');

		if ($policylinkArticleId > 0 && Associations::isEnabled())
		{
			$policylinkAssociated = Associations::getAssociations('com_content', '#__content', 'com_content.item', $policylinkArticleId);
			$currentLang = Factory::getLanguage()->getTag();

			if (isset($policylinkAssociated[$currentLang]))
			{
				$policylinkArticleId = $policylinkAssociated[$currentLang]->id;
			}
		}

		return $policylinkArticleId;
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
		$position = $this->params->get('position', 'bottom');
		$layout = $this->params->get('layout', 'block');
		$backgroundcolour = $this->params->get('bannercolour', '#000000');
		$buttoncolour = $this->params->get('buttoncolour', '#ffffff');
		$buttontextcolour = $this->params->get('buttontextcolour', '#383b75');
		$backgroundtextcolour = $this->params->get('bannertextcolour', '#f1d600');
		// 'policylink' => $this->params->get('policylink'),
		// 'message-text' => $this->params->get('message-text', Text::_('PLG_SYSTEM_COOKIECONSENT_MESSAGE_TEXT_DEFAULT')),
		// 'policylink-text' => $this->params->get('policylink-text', Text::_('PLG_SYSTEM_COOKIECONSENT_POLICY_TEXT_DEFAULT')),
		// 'button-text' => $this->params->get('button-text', Text::_('PLG_SYSTEM_COOKIECONSENT_BUTTON_TEXT_DEFAULT'))


		HTMLHelper::_('script', 'vendor/cookieconsent/cookieconsent.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
		HTMLHelper::_('stylesheet', 'vendor/cookieconsent/cookieconsent.min.css', ['version' => 'auto', 'relative' => true]);


		// Initialise the script and apply configuration
		$document->addScriptDeclaration("document.addEventListener('DOMContentLoaded', function() {
			window.cookieconsent.initialise({
				'palette': {
					'popup': {
						'background': '$backgroundcolour',
						'text': '$backgroundtextcolour'
					},
					'button': {
						'background': '$buttoncolour',
						'text': '$buttontextcolour'
					}
				  },
				'position': '$position',
				'theme': '$layout'
				})

			  });
		");
	}
}


