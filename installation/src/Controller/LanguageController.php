<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Language controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class LanguageController extends JSONController
{
    /**
     * Sets the language.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function set()
    {
        $this->checkValidToken();

        // Check for potentially unwritable session
        $session = $this->app->getSession();

        if ($session->isNew()) {
            $this->sendJsonResponse(new \Exception(Text::_('INSTL_COOKIES_NOT_ENABLED'), 500));
        }

        /** @var SetupModel $model */
        $model = $this->getModel('Setup');

        // Get the posted values from the request and validate them.
        $data   = $this->input->post->get('jform', [], 'array');
        $return = $model->validate($data, 'language');

        $r = new \stdClass();

        // Check for validation errors.
        if ($return === false) {
            /*
             * The validate method enqueued all messages for us, so we just need to
             * redirect back to the site setup screen.
             */
            $r->view = $this->input->getWord('view', 'setup');
            $this->sendJsonResponse($r);
        }

        // Store the options in the session.
        $model->storeOptions($return);

        // Setup language
        Factory::$language = Language::getInstance($return['language']);

        // Redirect to the page.
        $r->view = $this->input->getWord('view', 'setup');

        $this->sendJsonResponse($r);
    }

    /**
     * Sets the default language.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setdefault()
    {
        $this->checkValidToken();

        $app = $this->app;

        /** @var \Joomla\CMS\Installation\Model\LanguagesModel $model */
        $model = $this->getModel('Languages');

        // Check for request forgeries in the administrator language
        $admin_lang = $this->input->getString('administratorlang', false);

        // Check that the string is an ISO Language Code avoiding any injection.
        if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $admin_lang)) {
            $admin_lang = 'en-GB';
        }

        // Attempt to set the default administrator language
        if (!$model->setDefault($admin_lang, 'administrator')) {
            // Create an error response message.
            $this->app->enqueueMessage(Text::_('INSTL_DEFAULTLANGUAGE_ADMIN_COULDNT_SET_DEFAULT'), 'error');
        } else {
            // Create a response body.
            $app->enqueueMessage(Text::sprintf('INSTL_DEFAULTLANGUAGE_ADMIN_SET_DEFAULT', $admin_lang), 'message');
        }

        // Check for request forgeries in the site language
        $frontend_lang = $this->input->getString('frontendlang', false);

        // Check that the string is an ISO Language Code avoiding any injection.
        if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $frontend_lang)) {
            $frontend_lang = 'en-GB';
        }

        // Attempt to set the default site language
        if (!$model->setDefault($frontend_lang, 'site')) {
            // Create an error response message.
            $app->enqueueMessage(Text::_('INSTL_DEFAULTLANGUAGE_FRONTEND_COULDNT_SET_DEFAULT'), 'error');
        } else {
            // Create a response body.
            $app->enqueueMessage(Text::sprintf('INSTL_DEFAULTLANGUAGE_FRONTEND_SET_DEFAULT', $frontend_lang), 'message');
        }

        $r = new \stdClass();

        // Redirect to the final page.
        $r->view = 'remove';
        $this->sendJsonResponse($r);
    }
}
