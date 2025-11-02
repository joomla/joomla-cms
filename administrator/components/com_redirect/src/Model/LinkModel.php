<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Redirect link model.
 *
 * @since  1.6
 */
class LinkModel extends AdminModel
{
    /**
     * @var        string    The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_REDIRECT';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if ($record->published != -2) {
            return false;
        }

        return parent::canDelete($record);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_redirect.link', 'link', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // If in advanced mode then we make sure the new URL field is not compulsory and the header
        // field compulsory in case people select non-3xx redirects
        if (ComponentHelper::getParams('com_redirect')->get('mode', 0)) {
            $form->setFieldAttribute('new_url', 'required', 'false');
            $form->setFieldAttribute('header', 'required', 'true');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_redirect.edit.link.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_redirect.link', $data);

        return $data;
    }

    /**
     * Method to activate links.
     *
     * @param   array   &$pks     An array of link ids.
     * @param   string  $url      The new URL to set for the redirect.
     * @param   string  $comment  A comment for the redirect links.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   1.6
     */
    public function activate(&$pks, $url, $comment = null)
    {
        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();

        // Sanitize the ids.
        $pks = (array) $pks;
        $pks = ArrayHelper::toInteger($pks);

        // Populate default comment if necessary.
        $comment = (!empty($comment)) ? $comment : Text::sprintf('COM_REDIRECT_REDIRECTED_ON', HTMLHelper::_('date', time()));

        // Access checks.
        if (!$user->authorise('core.edit', 'com_redirect')) {
            $pks = [];
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

            return false;
        }

        if (!empty($pks)) {
            // Update the link rows.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__redirect_links'))
                ->set($db->quoteName('new_url') . ' = :url')
                ->set($db->quoteName('published') . ' = 1')
                ->set($db->quoteName('comment') . ' = :comment')
                ->whereIn($db->quoteName('id'), $pks)
                ->bind(':url', $url)
                ->bind(':comment', $comment);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Method to batch update URLs to have new redirect urls and comments. Note will publish any unpublished URLs.
     *
     * @param   array   &$pks     An array of link ids.
     * @param   string  $url      The new URL to set for the redirect.
     * @param   string  $comment  A comment for the redirect links.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   3.6.0
     */
    public function duplicateUrls(&$pks, $url, $comment = null)
    {
        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();

        // Sanitize the ids.
        $pks = (array) $pks;
        $pks = ArrayHelper::toInteger($pks);

        // Access checks.
        if (!$user->authorise('core.edit', 'com_redirect')) {
            $pks = [];
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

            return false;
        }

        if (!empty($pks)) {
            $date = Factory::getDate()->toSql();

            // Update the link rows.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__redirect_links'))
                ->set($db->quoteName('new_url') . ' = :url')
                ->set($db->quoteName('modified_date') . ' = :date')
                ->set($db->quoteName('published') . ' = 1')
                ->whereIn($db->quoteName('id'), $pks)
                ->bind(':url', $url)
                ->bind(':date', $date);

            if (!empty($comment)) {
                $query->set($db->quoteName('comment') . ' = ' . $db->quote($comment));
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        return true;
    }
}
