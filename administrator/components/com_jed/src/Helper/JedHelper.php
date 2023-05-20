<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2023 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects


use DateTime;
use Exception;
use Jed\Component\Jed\Administrator\MediaHandling\ImageSize;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User;
use Joomla\Registry\Registry;

/**
 * JED Helper
 *
 * @package   JED
 * @since     4.0.0
 */
class JedHelper
{
    /**
     * Add config toolbar to admin pages
     *
     * @since 4.0.0
     */
    public static function addConfigToolbar(Toolbar $bar)
    {
        $bar->linkButton('tickets')
            ->text(Text::_('COM_JED_TITLE_TICKETS'))
            ->url('index.php?option=com_jed&view=jedtickets')
            ->icon('fa fa-ticket-alt');
        $bar->linkButton('vulnerable')
            ->text('Vulnerable Items')
            ->url('index.php?option=com_jed&view=velvulnerableitems')
            ->icon('fa fa-bug');

        $bar->customHtml('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');


        $configGroup = $bar->dropdownButton('config-group')
            ->text(Text::_('COM_JED_GENERAL_CONFIG_LABEL'))
            ->toggleSplit(false)
            ->icon('fa fa-cog')
            ->buttonClass('btn btn-action')
            ->listCheck(false);

        $configChild = $configGroup->getChildToolbar();

        $configChild->linkButton('emailtemplates')
            ->text('COM_JED_TITLE_MESSAGETEMPLATES')
            ->icon('fa fa-envelope')
            ->url('index.php?option=com_jed&view=messagetemplates');

        $configChild->linkButton('ticketcategories')
            ->text('COM_JED_TITLE_TICKET_CATEGORIES')
            ->icon('fa fa-folder')
            ->url('index.php?option=com_jed&view=ticketcategories');

        $configChild->linkButton('ticketgroups')
            ->text('COM_JED_TITLE_ALLOCATEDGROUPS')
            ->icon('fa fa-user-friends')
            ->url('index.php?option=com_jed&view=ticketallocatedgroups');

        $configChild->linkButton('ticketlinkeditemtypes')
            ->text('COM_JED_TITLE_LINKED_ITEM_TYPES')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=ticketlinkeditemtypes');

        $configChild->linkButton('extensionsupplyoptions')
            ->text('COM_JED_TITLE_EXTENSION_SUPPLY_OPTIONS')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=extensionsupplyoptions');

        $configChild->linkButton('setupdemomenu')
            ->text('COM_JED_TITLE_SETUP_DEMO_MENU')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=setupdemo');

        /*
         * Only for finally moving live to test
         *
         $configChild->linkButton('copyjed3data')
            ->text('COM_JED_TITLE_COPY_JED3_DATA')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=copyjed3data');
        */
        $bar->customHtml('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

        $debugGroup = $bar->dropdownButton('debug-group')
            ->text('Debug')
            ->toggleSplit(false)
            ->icon('fa fa-cog')
            ->buttonClass('btn btn-action')
            ->listCheck(false);

        $debugChild = $debugGroup->getChildToolbar();

        $debugChild->linkButton('velabandonedreports')
            ->text('VEL Abandoned Reports')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=velabandonedreports');

        $debugChild->linkButton('velreports')
            ->text('VEL Reports')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=velreports');

        $debugChild->linkButton('veldeveloperupdates')
            ->text('VEL Developer Updates')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=veldeveloperupdates');

        $debugChild->linkButton('velvulnerableitems')
            ->text('VEL Vulnerable Items')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=velvulnerableitems');
        $debugChild->linkButton('ticketmessages')
            ->text('Ticket Messages')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=ticketmessages');

        $debugChild->linkButton('ticketinternalnotes')
            ->text('Ticket Internal Notes')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=ticketinternalnotes');

        $debugChild->linkButton('jedtickets')
            ->text('JED Tickets')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=jedtickets');
        $debugChild->linkButton('extensions')
            ->text('Extensions')
            ->icon('fa fa-link')
            ->url('index.php?option=com_jed&view=extensions');
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return registry
     *
     * @since    4.0.0
     * @throws Exception
     */
    public static function getActions(): registry
    {
        //$user   = Factory::getUser();

        $app = Factory::getApplication();

        $user   = $app->getSession()->get('user');
        $result = new Registry();

        $assetName = 'com_jed';

        $actions = [
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete',
        ];

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    /**
     * Returns a span string containing an icon denoting approved status
     *
     * @return registry
     *
     * @since    4.0.0
     * @throws Exception
     */
    public static function getApprovedIcon(int $state): string
    {
        switch ($state) { //Rejected
            case '-1':
                $icon = 'unpublish';
                break;
            case '1':// Approved
                $icon = 'publish';
                break;

            case '2':// Awaiting response
                $icon = 'expired';
                break;

            case '0':// Pending
            default:
                $icon = 'pending';
                break;
        }

        return '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
    }

    /**
     * Gets the files attached to an item
     *
     * @param   int     $pk     The item's id
     *
     * @param   string  $table  The table's name
     *
     * @param   string  $field  The field's name
     *
     * @return  array  The files
     *
     */
    public static function getFiles(int $pk, string $table, string $field): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query
            ->select($field)
            ->from($table)
            ->where('id = ' . $pk);

        $db->setQuery($query);

        return explode(',', $db->loadResult());
    }

    /**
     * Returns a span string containing an icon denoting published status
     *
     * @return registry
     *
     * @since    4.0.0
     * @throws Exception
     */
    public static function getPublishedIcon(int $state): string
    {
        switch ($state) { //Rejected
            case '-1':
                $icon = 'unpublish';
                break;
            case '1':// Approved
                $icon = 'publish';
                break;

            case '2':// Awaiting response
                $icon = 'expired';
                break;

            case '0':// Pending
            default:
                $icon = 'pending';
                break;
        }

        return '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
    }

    /**
     * Gets the current User .
     *
     * @return User\User
     *
     * @since    4.0.0
     */
    public static function getUser(): User\User
    {
        try {
            $app = Factory::getApplication();

            return $app->getSession()->get('user');
        } catch (Exception $e) {
            return new User\User();
        }
    }

    /**
     * Gets a user by ID number.
     *
     * @param $userId
     *
     * @return User\User
     *
     * @since    4.0.0
     */
    public static function getUserById($userId): User\User
    {
        try {//$user   = Factory::getUser();
            $container   = Factory::getContainer();
            $userFactory = $container->get('user.factory');

            return $userFactory->loadUserById($userId);
        } catch (Exception $e) {
            return new User\User();
        }
    }

    /**
     * Checks whether or not a user is manager or super user
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public static function isAdminOrSuperUser(): bool
    {
        try {
            $user = self::getUser();

            return in_array("8", $user->groups) || in_array("7", $user->groups);
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Lock form fields
     *
     * This takes a form and marks all fields as readonly/disabled
     *
     * @param $form     form of fields
     * @param $excluded array of fields not to lock
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public static function lockFormFields(Form $form, array $excluded): bool
    {
        $fields = $form->getFieldset();
        foreach ($fields as $field) :
            if (in_array($field->getAttribute('name'), $excluded)) {
                //Do Nothing
            } else {
                $form->setFieldAttribute($field->getAttribute('name'), 'disabled', 'true');
                $form->setFieldAttribute($field->getAttribute('name'), 'class', 'readonly');
                $form->setFieldAttribute($field->getAttribute('name'), 'readonly', 'true');
            }
        endforeach;

        return true;
    }

    /**
     * Prettyfy a Data
     *
     * @param   string  $datestr  A String Date
     *
     * @since 4.0.0
     **/
    public static function prettyDate(string $datestr): string
    {
        try {
            $d = new DateTime($datestr);

            return $d->format("d M y H:i");
        } catch (Exception $e) {
            return 'Sorry an error occured';
        }
    }

    /**
     * Function to format JED Extension Images
     *
     * @param   string  $filename  The image filename
     * @param   string  $size      Size of image, small|large
     *
     * @return  string  Full image url
     *
     * @since   4.0.0
     */
    public static function formatImage(string $filename, ImageSize $size = ImageSize::SMALL): string
    {
        if (!$filename) {
            return '';
        }

        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        $params = ComponentHelper::getParams('com_jed');
        $cdnUrl = rtrim($params->get('cdn_url', 'https://extensionscdn.joomla.org'), '/');

        $lastDot      = strrpos($filename, '.');
        $partialName  = substr($filename, 0, $lastDot - 1);
        $extension    = substr($filename, $lastDot);
        $bestFilename = match ($size) {
            ImageSize::ORIGINAL => $filename,
            ImageSize::SMALL    => $partialName . '_small' . $extension,
            ImageSize::LARGE    => $partialName . '_large' . $extension,
        };

        // TODO Check if the resized file exists; if not resize it

        // TODO If the file cannot be resized AND I am configured to use a CDN, fall back to the legacy CDN URLs
        if (false && $params->get('use_cdn', 0)) {
            $bestFilename = match ($size) {
                ImageSize::ORIGINAL => $filename,
                ImageSize::SMALL    => $partialName . '_resizeDown400px175px16' . $extension,
                ImageSize::LARGE    => $partialName . '_resizeDown1200px525px16' . $extension,
            };

            return $cdnUrl . '/cache/fab_image/' . $bestFilename;
        }

        // If I am configured to use a CDN, use the https://extensionscdn.joomla.org CDN
        if ($params->get('use_cdn', 0)) {
            return $cdnUrl . '/cache/' . $bestFilename;
        }

        // No CDN (e.g. local development). Where should I get my image from?
        if (File::exists(JPATH_ROOT . '/' . ltrim($bestFilename, '/\\'))) {
            return Uri::root() . ltrim($bestFilename, '/\\');
        }

        if (File::exists(JPATH_ROOT . '/' . ltrim($filename, '/\\'))) {
            return Uri::root() . ltrim($filename, '/\\');
        }

        if (File::exists(JPATH_ROOT . '/media/com_jed/cache/' . ltrim($bestFilename, '/\\'))) {
            return Uri::root() . 'media/com_jed/' . ltrim($bestFilename, '/\\');
        }

        if (File::exists(JPATH_ROOT . '/media/com_jed/cache/' . ltrim($filename, '/\\'))) {
            return Uri::root() . 'media/com_jed/' . ltrim($filename, '/\\');
        }

        return '';
    }
}
