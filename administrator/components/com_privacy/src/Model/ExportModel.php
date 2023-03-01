<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Privacy\Administrator\Export\Domain;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Export model class.
 *
 * @since  3.9.0
 */
class ExportModel extends BaseDatabaseModel
{
    /**
     * Create the export document for an information request.
     *
     * @param   integer  $id  The request ID to process
     *
     * @return  Domain[]|boolean  A SimpleXMLElement object for a successful export or boolean false on an error
     *
     * @since   3.9.0
     */
    public function collectDataForExportRequest($id = null)
    {
        $id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

        if (!$id) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_EXPORT'));

            return false;
        }

        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        if ($table->request_type !== 'export') {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_EXPORT'));

            return false;
        }

        if ($table->status != 1) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_EXPORT_UNCONFIRMED_REQUEST'));

            return false;
        }

        // If there is a user account associated with the email address, load it here for use in the plugins
        $db = $this->getDatabase();

        $userId = (int) $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
                ->bind(':email', $table->email)
                ->setLimit(1)
        )->loadResult();

        $user = $userId ? User::getInstance($userId) : null;

        // Log the export
        $this->logExport($table);

        PluginHelper::importPlugin('privacy');

        $pluginResults = Factory::getApplication()->triggerEvent('onPrivacyExportRequest', [$table, $user]);

        $domains = [];

        foreach ($pluginResults as $pluginDomains) {
            $domains = array_merge($domains, $pluginDomains);
        }

        return $domains;
    }

    /**
     * Email the data export to the user.
     *
     * @param   integer  $id  The request ID to process
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function emailDataExport($id = null)
    {
        $id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

        if (!$id) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_EXPORT'));

            return false;
        }

        $exportData = $this->collectDataForExportRequest($id);

        if ($exportData === false) {
            // Error is already set, we just need to bail
            return false;
        }

        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        if ($table->request_type !== 'export') {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_EXPORT'));

            return false;
        }

        if ($table->status != 1) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_EXPORT_UNCONFIRMED_REQUEST'));

            return false;
        }

        // Log the email
        $this->logExportEmailed($table);

        /*
         * If there is an associated user account, we will attempt to send this email in the user's preferred language.
         * Because of this, it is expected that Language::_() is directly called and that the Text class is NOT used
         * for translating all messages.
         *
         * Error messages will still be displayed to the administrator, so those messages should continue to use the Text class.
         */

        $lang = Factory::getLanguage();

        $db = $this->getDatabase();

        $userId = (int) $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
                ->bind(':email', $table->email),
            0,
            1
        )->loadResult();

        if ($userId) {
            $receiver = User::getInstance($userId);

            /*
             * We don't know if the user has admin access, so we will check if they have an admin language in their parameters,
             * falling back to the site language, falling back to the currently active language
             */

            $langCode = $receiver->getParam('admin_language', '');

            if (!$langCode) {
                $langCode = $receiver->getParam('language', $lang->getTag());
            }

            $lang = Language::getInstance($langCode, $lang->getDebug());
        }

        // Ensure the right language files have been loaded
        $lang->load('com_privacy', JPATH_ADMINISTRATOR)
            || $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy');

        // The mailer can be set to either throw Exceptions or return boolean false, account for both
        try {
            $app = Factory::getApplication();
            $mailer = new MailTemplate('com_privacy.userdataexport', $app->getLanguage()->getTag());

            $templateData = [
                'sitename' => $app->get('sitename'),
                'url'      => Uri::root(),
            ];

            $mailer->addRecipient($table->email);
            $mailer->addTemplateData($templateData);
            $mailer->addAttachment('user-data_' . Uri::getInstance()->toString(['host']) . '.xml', PrivacyHelper::renderDataAsXml($exportData));

            if ($mailer->send() === false) {
                $this->setError($mailer->ErrorInfo);

                return false;
            }

            return true;
        } catch (phpmailerException $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since   3.9.0
     */
    public function getTable($name = 'Request', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Log the data export to the action log system.
     *
     * @param   RequestTable  $request  The request record being processed
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function logExport(RequestTable $request)
    {
        $user = Factory::getUser();

        $message = [
            'action'      => 'export',
            'id'          => $request->id,
            'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_EXPORT', 'com_privacy.request', $user->id);
    }

    /**
     * Log the data export email to the action log system.
     *
     * @param   RequestTable  $request  The request record being processed
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function logExportEmailed(RequestTable $request)
    {
        $user = Factory::getUser();

        $message = [
            'action'      => 'export_emailed',
            'id'          => $request->id,
            'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_EXPORT_EMAILED', 'com_privacy.request', $user->id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function populateState()
    {
        // Get the pk of the record from the request.
        $this->setState($this->getName() . '.request_id', Factory::getApplication()->input->getUint('id'));

        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_privacy'));
    }

    /**
     * Method to fetch an instance of the action log model.
     *
     * @return  ActionlogModel
     *
     * @since   4.0.0
     */
    private function getActionlogModel(): ActionlogModel
    {
        return Factory::getApplication()->bootComponent('com_actionlogs')
            ->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);
    }
}
