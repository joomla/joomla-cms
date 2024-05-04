<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.confirmconsent
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\ConfirmConsent\Extension;

use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Joomla Core confirm consent plugin
 *
 * @since  3.9.0
 */
final class ConfirmConsent extends CMSPlugin implements SubscriberInterface
{
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
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

    /**
     * Add additional fields to the supported forms
     *
     * @param   PrepareFormEvent $event  The event instance.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function onContentPrepareForm(PrepareFormEvent $event)
    {
        $form = $event->getForm();

        if ($this->getApplication()->isClient('administrator') || !\in_array($form->getName(), $this->supportedContext)) {
            return true;
        }

        $this->loadLanguage();

        // Get the consent box Text & the selected privacyarticle
        $consentboxText  = (string) $this->params->get(
            'consentbox_text',
            $this->getApplication()->getLanguage()->_('PLG_CONTENT_CONFIRMCONSENT_FIELD_NOTE_DEFAULT')
        );
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
			</form>');

        return true;
    }
}
