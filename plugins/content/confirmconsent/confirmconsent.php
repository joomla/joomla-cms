<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.confirmconsent
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * The Joomla Core confirm consent plugin
 *
 * @since  3.9.0
 */
class PlgContentConfirmConsent extends CMSPlugin
{
	/**
	 * The Application object
	 *
	 * @var    JApplicationSite
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * The Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The supported form contexts
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $supportedContext = array(
		'com_contact.contact',
		'com_mailto.mailto',
		'com_privacy.request',
	);

	/**
	 * Add additional fields to the supported forms
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		if ($this->app->isClient('administrator') || !in_array($form->getName(), $this->supportedContext))
		{
			return true;
		}

		// Get the consent box Text & the selected privacyarticle
		$consentboxLabel = Text::_('PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL');
		$consentboxText  = (string) $this->params->get('consentbox_text', Text::_('PLG_CONTENT_CONFIRMCONSENT_FIELD_NOTE_DEFAULT'));
		$privacyArticle  = $this->params->get('privacy_article', false);

		// When we have a article let's add the modal and make the title clickable
		if ($privacyArticle)
		{
			$modalName = 'privacyArticle';
			$modalParams['title']  = $consentboxLabel;
			$modalParams['url']    = $this->getAssignedArticleUrl($privacyArticle);
			$modalParams['height'] = 800;
			$modalParams['width']  = "100%";
			echo HTMLHelper::_('bootstrap.renderModal', 'modal-' . $modalName, $modalParams);

			$attribs['data-toggle'] = 'modal';

			$consentboxLabel = HTMLHelper::_(
				'link',
				'#modal-' . $modalName,
				$consentboxLabel,
				$attribs
			);
		}

		$form->load('
			<form>
				<fieldset name="default">
					<field
						name="consentbox"
						type="checkboxes"
						label="' . htmlspecialchars($consentboxLabel, ENT_COMPAT, 'UTF-8') . '"
						required="true"
						>
						<option value="0">' . htmlspecialchars($consentboxText, ENT_COMPAT, 'UTF-8') . '</option>
					</field>
				</fieldset>
			</form>'
		);

		return true;
	}

	/**
	 * Return the url of the assigned article based on the current user language
	 *
	 * @param   integer  $articleId  The form to be altered.
	 *
	 * @return  string  Returns the a tag containing everything for the modal
	 *
	 * @since   3.9.0
	 */
	private function getAssignedArticleUrl($articleId)
	{
		// Get the info from the article
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'catid', 'language')))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('id') . ' = ' . (int) $articleId);
		$this->db->setQuery($query);

		try
		{
			$article = $this->db->loadObject();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			// Something at the database layer went wrong
			return Route::_(
				'index.php?option=com_content&view=article&id='
				. $articleId . '&tmpl=component'
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
				) . '&tmpl=component'
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
				) . '&tmpl=component'
			);
		}

		// Association is enabled but this article is not associated
		return Route::_(
			'index.php?option=com_content&view=article&id='
				. $article->id . '&catid=' . $article->catid
				. '&tmpl=component&lang=' . $article->language
		);
	}
}
