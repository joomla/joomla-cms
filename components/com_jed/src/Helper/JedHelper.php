<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\MediaHandling\ImageSize;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User;

/**
 * JED Helper
 *
 * @package   JED
 * @since     4.0.0
 */
class JedHelper
{
    /**
     * Gets the current User .
     *
     * @return User\User
     *
     * @since    4.0.0
     */
    public static function getUser(): User\User
    {
        //$user   = Factory::getUser();
        $app = null;
        try {
            $app = Factory::getApplication();
        } catch (Exception $e) {
        }
        return $app->getSession()->get('user');
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
        //$user   = Factory::getUser();

        try {
            $container   = Factory::getContainer();
            $userFactory = $container->get('user.factory');

            return $userFactory->loadUserById($userId);
        } catch (Exception $e) {
            return new User\User();
        }
    }



    /**
     * For a new review this creates a corresponding Ticket
     *
     * @param   int  $item_id      Reference for stored report
     *
     * @return  array  Ticket Template
     * @since 4.0.0
     *
     * @throws Exception
     */

    public static function CreateReviewTicket(int $item_id): array
    {

        $db = Factory::getContainer()->get('DatabaseDriver');

        $ticket = [];

        $user = JedHelper::getUser();

        $ticket['id']               = 0;
        $ticket['created_by']       = $user->id;
        $ticket['modified_by']      = $user->id;
        $ticket['created_on']       = 'now()';
        $ticket['modified_on']      = 'now()';
        $ticket['state']            = 0;
        $ticket['ordering']         = 0;
        $ticket['checked_out']      = 0;
        $ticket['checked_out_time'] = '0000-00-00 00:00:00';
        $ticket['ticket_origin']    = 0; //Registered User


        $ticket['ticket_category_type'] = 3;
        $ticket['ticket_subject']       = "A new Review";
        $ticket['linked_item_type']     = 3;     //    Review


        /*
            Ticket Category type

           <option value="1">Unknown</option>
           <option value="2">Extension</option>
           <option value="3">Review</option>
           <option value="4">Joomla Site Issue</option>
           <option value="5">New Listing Support</option>
           <option value="6">Current Listing Support</option>
           <option value="7">Site Technical Issues</option>
           <option value="8">Unpublished Support</option>
           <option value="9">Reported Review</option>
           <option value="10">Reported Extension</option>
           <option value="11">Vulnerable Item Report</option>
           <option value="12">VEL Developer Update</option>
           <option value="13">VEL Abandonware Report</option>*/


        $ticket['allocated_group'] = 4; //Assign to review Team
        /* Alloc Groups
            1 - Any
            2 - Team Leadership
            3 - Listing Specialist
            4 - Review Specialist
            5 - Support Specialist
            6 - VEL Specialist */

        $ticket['linked_item_id'] = $item_id;

        /* Linked Item Types
         <option value="1" selected="selected">Unknown</option>
         <option value="2">Extension</option>
         <option value="3">Review</option>
         <option value="4">Vulnerable Item Initial Report</option>
         <option value="5">Vulnerable Item Developer Update</option>
         <option value="6">Abandonware Report</option>
//       <option value="7">Vulnerable Item Email Correspondence</option> */


        $ticket['ticket_status'] = 0; //New
        /*
            <option value="0" selected="selected">New</option>
            <option value="1">Awaiting User</option>
            <option value="2">Awaiting JED</option>
            <option value="3">Resolved</option>
            <option value="4">Closed</option>
            <option value="5">Updated</option>

        */
        $ticket['ticket_text']    = '<p>Please see linked review</p>';
        $ticket['internal_notes'] = '';

        $ticket['uploaded_files_preview']  = '';
        $ticket['uploaded_files_location'] = '';
        $ticket['allocated_to']            = 0;
        $ticket['parent_id']               = -1;


        foreach ($ticket as $k => $v) {
            $columns[] = $k;
            if (str_ends_with($k, "_on")) {
                $values[] = $v;
            } else {
                $values[] = $db->quote($v);
            }
        }

        return $ticket;
    }

    /**
     * When a VEL is reported or a Developer Update or Abandoned Item reported  this creates a corresponding Ticket
     *
     * @param   int  $report_type  1 for VEL REPORT, 2 for DEVELOPER UPDATE, 3 for ABANDONWARE REPORT
     * @param   int  $item_id      Reference for stored report
     *
     * @return  array  Ticket Template
     * @since 4.0.0
     *
     * @throws Exception
     */

    public static function CreateVELTicket(int $report_type, int $item_id): array
    {

        $db = Factory::getContainer()->get('DatabaseDriver');

        $ticket = [];

        $user = JedHelper::getUser();

        $ticket['id']               = 0;
        $ticket['created_by']       = $user->id;
        $ticket['modified_by']      = $user->id;
        $ticket['created_on']       = 'now()';
        $ticket['modified_on']      = 'now()';
        $ticket['state']            = 0;
        $ticket['ordering']         = 0;
        $ticket['checked_out']      = 0;
        $ticket['checked_out_time'] = '0000-00-00 00:00:00';
        $ticket['ticket_origin']    = 0; //Registered User

        switch ($report_type) {
            case 1: // VEL REPORT
                $ticket['ticket_category_type'] = 11;
                $ticket['ticket_subject']       = "A new Vulnerable Item Report";
                $ticket['linked_item_type']     = 4;     //    Vulnerable Item Initial Report
                break;
            case 2: // DEVELOPER UPDATE
                $ticket['ticket_category_type'] = 12;
                $ticket['ticket_subject']       = "A new VEL Developer Update";
                $ticket['linked_item_type']     = 5;     //    Vulnerable Item Developer Update
                break;

            case 3: // ABANDONWARE REPORT
                $ticket['ticket_category_type'] = 13;
                $ticket['ticket_subject']       = "A new VEL Abandonware Report";
                $ticket['linked_item_type']     = 6;     //    Vulnerable Item Abandonware Report
                break;
        }

        /*
            Ticket Category type

           <option value="1">Unknown</option>
           <option value="2">Extension</option>
           <option value="3">Review</option>
           <option value="4">Joomla Site Issue</option>
           <option value="5">New Listing Support</option>
           <option value="6">Current Listing Support</option>
           <option value="7">Site Technical Issues</option>
           <option value="8">Unpublished Support</option>
           <option value="9">Reported Review</option>
           <option value="10">Reported Extension</option>
           <option value="11">Vulnerable Item Report</option>
           <option value="12">VEL Developer Update</option>
           <option value="13">VEL Abandonware Report</option>*/


        $ticket['allocated_group'] = 6; //These are VEL subjects
        /* Alloc Groups
            1 - Any
            2 - Team Leadership
            3 - Listing Specialist
            4 - Review Specialist
            5 - Support Specialist
            6 - VEL Specialist */

        $ticket['linked_item_id'] = $item_id;

        /* Linked Item Types
         <option value="1" selected="selected">Unknown</option>
         <option value="2">Extension</option>
         <option value="3">Review</option>
         <option value="4">Vulnerable Item Initial Report</option>
         <option value="5">Vulnerable Item Developer Update</option>
         <option value="6">Abandonware Report</option>
//       <option value="7">Vulnerable Item Email Correspondence</option> */


        $ticket['ticket_status'] = 0; //New
        /*
            <option value="0" selected="selected">New</option>
            <option value="1">Awaiting User</option>
            <option value="2">Awaiting JED</option>
            <option value="3">Resolved</option>
            <option value="4">Closed</option>
            <option value="5">Updated</option>

        */
        $ticket['ticket_text']    = '<p>Please see linked report</p>';
        $ticket['internal_notes'] = '';

        $ticket['uploaded_files_preview']  = '';
        $ticket['uploaded_files_location'] = '';
        $ticket['allocated_to']            = 0;
        $ticket['parent_id']               = -1;


        foreach ($ticket as $k => $v) {
            $columns[] = $k;
            if (str_ends_with($k, "_on")) {
                $values[] = $v;
            } else {
                $values[] = $db->quote($v);
            }
        }

        return $ticket;
    }

    /**
     * Create Empty Ticket for VEL
     *
     * @return array
     *
     * @since 4.0.0
     */
    public static function CreateEmptyTicketMessage(): array
    {
        $user                               = JedHelper::getUser();
        $ticket_message                     = [];
        $ticket_message['id']               = 0;
        $ticket_message['created_by']       = $user->id;
        $ticket_message['modified_by']      = $user->id;
        $ticket_message['created_on']       = 'now()';
        $ticket_message['state']            = 0;
        $ticket_message['ordering']         = 0;
        $ticket_message['checked_out']      = 0;
        $ticket_message['checked_out_time'] = '0000-00-00 00:00:00';

        return $ticket_message;
    }

    /**
     * Get Message Template from Database and return
     *
     * @param   int  $template_id
     *
     * @return object
     *
     * @since version
     */
    public static function GetMessageTemplate(int $template_id): object
    {
        // Create a new query object.
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from($db->quoteName('#__jed_message_templates'))
            ->where('id=' . $template_id);
        // Reset the query using our newly populated query object.
        $db->setQuery($query);

        // Load the results as a stdClass object.
        return $db->loadObject();
    }

    /**
     * IsLoggedIn
     *
     * Returns if user is logged in
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public static function IsLoggedIn(): bool
    {

        $user = JedHelper::getUser();
        if ($user->id > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets the edit permission for a user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     *
     * @since   4.0.0
     */
    public static function canUserEdit($item): bool
    {

        $permission = false;
        $user       = JedHelper::getUser();

        if ($user->authorise('core.edit', 'com_jed')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
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

    /**
     * Returns URL for user login
     *
     * @return string
     *
     * @since 4.0.0
     */
    public static function getLoginlink(): string
    {
        $redirectUrl    = '&return=' . urlencode(base64_encode(Uri::getInstance()->toString()));
        $joomlaLoginUrl = 'index.php?option=com_users&view=login';

        return $joomlaLoginUrl . $redirectUrl;
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
            $user = JedHelper::getUser();

            return in_array("8", $user->groups) || in_array("7", $user->groups);
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string  $date  Date to be checked
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public static function isValidDate(string $date): bool
    {
        $date = str_replace('/', '-', $date);

        return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : false;
    }

    /**
     * is_blank
     *
     * isEmpty sees a value of 0 as being empty which means that using it to test database option values fails with entries of 0
     *
     * @param $value
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public static function is_blank($value): bool
    {
        return empty($value) && !is_numeric($value);
    }

    /**
     * reformatTitle
     *
     * A lot of the restored JED 3 titles have extra spacing or missing punctuation. This fixes that for display.
     *
     * @param $l_str
     *
     * @return string
     *
     * @since 4.0.0
     */
    public static function reformatTitle($l_str): string
    {

        $loc = str_replace(',', ', ', $l_str);
        $loc = str_replace(' ,', ',', $loc);
        $loc = str_replace('  ', ' ', $loc);

        return trim($loc);
    }

    /**
     * This method advises if the $id of the item belongs to the current user
     *
     * @param   integer  $id     The id of the item
     * @param   string   $table  The name of the table
     *
     * @return  boolean             true if the user is the owner of the row, false if not.
     * @since   4.0.0
     */
    public static function userIDItem(int $id, string $table): bool
    {
        try {
            $user = JedHelper::getUser();
            $db   = Factory::getContainer()->get('DatabaseDriver');

            $query = $db->getQuery(true);
            $query->select("id")
                ->from($db->quoteName($table))
                ->where("id = " . $db->escape($id))
                ->where("created_by = " . $user->id);

            $db->setQuery($query);

            $results = $db->loadObject();
            if ($results) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * This method returns whether an alias is available for the view
     *
     *  @param   string   $view  The name of the view
     *
     * @return  string
     * @since   4.0.0
     */
    public static function getAliasFieldNameByView(string $view): string
    {
        switch ($view) {
            case 'extension':
            case 'extensionform':
                return 'alias';
                break;
            case 'review':
            case 'reviewform':
                return 'alias';
                break;
        }
        return "";
    }
}
