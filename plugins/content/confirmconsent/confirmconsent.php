<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.confirmconsent
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * The Joomla Core confirm consent plugin
 *
 * @since  3.9.0
 */
class PlgContentConfirmConsent extends CMSPlugin
{
	/**
	 * @var    \Joomla\CMS\Application\SiteApplication
	 *
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The supported form contexts
	 *
	 * @var    array
	 *
	 * @since  3.9.0
	 */
	protected $supportedContext = [
		'com_contact.contact',
		'com_privacy.request',
	];

	/**
	 * Add additional fields to the supported forms
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		if ($this->app->isClient('administrator') || !in_array($form->getName(), $this->supportedContext))
		{
			return true;
		}

		// Get the consent box Text & the selected privacyarticle
		$consentboxText  = (string) $this->params->get('consentbox_text', Text::_('PLG_CONTENT_CONFIRMCONSENT_FIELD_NOTE_DEFAULT'));
		$privacyArticle  = $this->params->get('privacy_article', false);
		$privacyType     = $this->params->get('privacy_type', 'article');
		$privacyMenuItem = $this->params->get('privacy_menu_item', false);

		$form->load('
			<form>
				<fieldset name="default" addfieldprefix="Joomla\\Plugin\\Content\\ConfirmConsent\\Field">
					<field
						name="consentbox"
						type="ConsentBox"
						articleid="' . $privacyArticle . '"
						menu_item_id="' . $privacyMenuItem . '"
						privacy_type="' . $privacyType . '"
						label="PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL"
						required="true"
						>
						<option value="0">' . htmlspecialchars($consentboxText, ENT_COMPAT, 'UTF-8') . '</option>
					</field>
				</fieldset>
			</form>'
		);

		return true;
	}
}
