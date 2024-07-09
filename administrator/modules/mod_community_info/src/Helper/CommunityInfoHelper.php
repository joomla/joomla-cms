<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CommunityInfo\Administrator\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_community_info
 *
 * @since  4.5.0
 */
class CommunityInfoHelper
{
    /**
     * Module parameters
     *
     * @var Registry
     */
    protected static $params = null;

    /**
     * ID of the current module
     *
     * @var integer
     */
    protected static $moduleId = null;

    /**
     * Initialize the helper variables
     *
     * @param   integer    $id      Id of the current module
     * @param   Registry   $params  Object holding the module parameters
     *
     * @return  void
     *
     * @since   4.5.0
     */
    public static function initialize(int $id, Registry $params)
    {
        self::setID($id);
        self::setParams($params);
    }

    /**
     * Get a list of links from the endpoint given in the module params.
     *
     * @param   Registry   $params   Object holding the module parameters
     *
     * @return  Registry   Object with community links
     *
     * @since   4.5.0
     */
    public static function getLinks(Registry $params)
    {
        self::setParams($params);

        // Load the local default values
        require_once JPATH_ADMINISTRATOR . '/modules/mod_community_info/includes/default_links.php';
        $links = new Registry($defaultLinksArray);

        // Load links from endpoint
        $vars = ['location' => self::getLocation($params, 'geolocation')];
        $url  = $params->get('endpoint', 'http://www.example.com');

        if ($api_link_sets = self::fetchAPI($url, $vars)) {
            // Sort the returned data based on level with descending order
            usort($api_link_sets, fn ($a, $b) => $b['level'] <=> $a['level']);

            // Search for a suitable link value in returned data
            foreach ($links as $k => $link_val) {
                foreach ($api_link_sets as $api_set_k => $api_link_set) {
                    $found = false;

                    foreach ($api_link_set as $api_k => $api_link_val) {
                        if ($k == $api_k) {
                            // As soon as we found a suitable value, we override it with the local default one
                            $links->set($k, $api_link_val);
                            $found = true;

                            break;
                        }
                    }

                    // If we already found a suitable value, we went on to the next link
                    if ($found) {
                        break;
                    }
                }
            }
        }

        return $links;
    }

    /**
     * Get location info
     *
     * @param   Registry   $params  Object holding the module parameters
     * @param   string     $key     The key for the location info
     *
     * @return  string     Location info string
     *
     * @since   4.5.0
     */
    public static function getLocation(Registry $params, string $key = 'geolocation')
    {
        self::setParams($params);

        $location = null;

        // Get the list of countries
        require JPATH_ADMINISTRATOR . '/modules/mod_community_info/includes/country_list.php';

        $matches = [];

        // Startegy 1: Location stored in parameters
        if (\is_null($location) && !empty($params->get('location', 0))) {
            if (key_exists($params->get('location'), $countryListArray)) {
                // Location based on language code
                $location = $countryListArray[$params->get('location')][$key];
            } else {
                $location = $params->get('location');
            }
        }

        // Strategy 2: Location based on current language
        if (\is_null($location) && key_exists($lang, $countryListArray)) {
            $location = $countryListArray[$lang][$key];
        }

        // Strategy 3: Fallback location
        if (\is_null($location)) {
            $location = $countryListArray[$params->get('fallback-location', 'en-GB')][$key];
        }

        if ($key == 'label' && preg_match('/[-]*\d{1,4}\.\d{1,4}\,[ ,-]*\d{1,4}\.\d{1,4}/m', $location, $matches)) {
            // We are asking for a location name. Turn coordinates into location name.
            $coor_arr = explode(',', $matches[0], 2);
            $location = self::resolveLocation($coor_arr[0], $coor_arr[1]);
        }

        return $location;
    }

    /**
     * Get the most recent news articles
     *
     * @param   string   $url   The url of the RSS news feed
     * @param   int      $num   Number of articles to be returned
     *
     * @return  array    List of articles
     *
     * @since   4.5.0
     */
    public static function getNewsFeed(string $url, int $num = 3)
    {
        // Load rss xml from endpoint
        $vars  = [];
        $items = [];

        if ($rss  = self::fetchAPI($url, $vars, 'xml')) {
            foreach ($rss->channel->item as $item) {
                $obj              = new \stdClass();
                $obj->title       = (string) $item->title;
                $obj->link        = (string) $item->link;
                $obj->guid        = (string) $item->guid;
                $obj->description = (string) $item->description;
                $obj->category    = (string) $item->category;
                $obj->pubDate     = (string) $item->pubDate;
                $items[]          = $obj;
            }

            // Sort the items by pubDate in descending order
            usort($items, fn ($a, $b) => strtotime($b->pubDate) <=> strtotime($a->pubDate));

            // Select n most recent items
            $items = \array_slice($items, 0, $num);
        }

        return $items;
    }

    /**
     * Get the next events
     *
     * @param   string    $url     The url of the JSON events feed
     * @param   int       $num     Number of articles to be returned
     *
     * @return  array    List of events
     *
     * @since   4.5.0
     */
    public static function getEventsFeed(string $url, int $num = 3)
    {
        // Load json from endpoint
        $vars           = [];
        $upcomingEvents = [];

        if ($events  = self::fetchAPI($url, $vars, 'json')) {
            // Sort the array by the 'start' property to ensure events are in chronological order
            usort($events, fn ($a, $b) => strtotime($a['start']) <=> strtotime($b['start']));

            // Select the next n upcoming events
            $nextThreeEvents = \array_slice($events, 0, $num);

            // Convert each event to an stdClass object and store them in a new array
            $upcomingEvents = array_map(function ($event) {
                return (object) $event;
            }, $nextThreeEvents);
        }

        return $upcomingEvents;
    }

    /**
     * Replace placeholders in a text string
     *
     * @param   string     $text    The text with placeholders
     * @param   Registry   $links   The links to be inserted
     *
     * @return  string     The replaced text
     *
     * @since   4.5.0
     */
    public static function replaceText(string $text, Registry $links)
    {
        if (preg_match_all('/{(.*?)}/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ($links->exists(strtolower($match[1]))) {
                    // replace with link
                    $output = '<a href="' . $links->get(strtolower($match[1])) . '" target="_blank">' . Text::_('MOD_COMMUNITY_INFO_TERMS_' . strtoupper($match[1])) . '</a>';
                } else {
                    // replace without link
                    $output = Text::_('MOD_COMMUNITY_INFO_TERMS_' . strtoupper($match[1]));
                }

                $text   = str_replace($match[0], $output, $text);
            }
        }

        return $text;
    }

    /**
     * Set location string to module params
     *
     * @return  string  The ajax return message
     *
     * @since   4.5.0
     */
    public static function setLocationAjax()
    {
        $input = Factory::getApplication()->input;

        if ($input->getCmd('option') !== 'com_ajax' || $input->getCmd('module') !== 'community_info') {
            return 'Permission denied!';
        }

        if (!$moduleId = $input->get('module_id', false, 'int')) {
            return 'You must provide a "module_id" variable with the request!';
        }

        if (!$current_location = $input->get('current_location', false, 'string')) {
            return 'You must provide a "current_location" variable with the request!';
        }

        self::setID($moduleId);
        $params           = self::setParams();
        $current_location = self::fixGeolocation($current_location);

        if ($params->get('auto_location', 1) && $params->get('location') != $current_location) {
            // Update location param
            $params->set('location_name', self::resolveLocation(trim($current_location)));
            $params->set('location', trim($current_location));

            // Write updates to db
            try {
                $res = self::writeParams($params);
            } catch (\Exception $e) {
                return Text::_('MOD_COMMUNITY_ERROR_SAVE_LOCATION') . ' ' . $e->getMessage();
            }

            if ($res) {
                return Text::_('MOD_COMMUNITY_SUCCESS_SAVE_LOCATION');
            }
        }

        return Text::_('MOD_COMMUNITY_MSG_SAVE_LOCATION_NOT_NEEDED');
    }

    /**
     * Save a manual location to params
     *
     * @param   string    $task    The task to be executed
     *
     * @return  void
     *
     * @since   4.5.0
     */
    public static function setLocationForm($task = 'saveLocation')
    {
        if (!Session::checkToken('post')) {
            return;
        }

        $params = self::setParams();

        // Get input data
        $input            = Factory::getApplication()->input;
        $jform            = $input->getArray([ 'jform' => ['lat' => 'string', 'lng' => 'string', 'autoloc' => 'bool'] ]);
        $current_location = self::fixGeolocation($jform['jform']['lat'] . ',' . $jform['jform']['lng']);

        // Update module params
        $params->set('location_name', self::resolveLocation(trim($current_location)));
        $params->set('location', trim($current_location));
        $params->set('auto_location', \intval($jform['jform']['autoloc']));

        // Write updates to db
        try {
            $res = self::writeParams($params);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(Text::_('MOD_COMMUNITY_ERROR_SAVE_LOCATION') . ' ' . $e->getMessage(), 'error');
        }

        if ($res) {
            Factory::getApplication()->enqueueMessage(Text::_('MOD_COMMUNITY_SUCCESS_SAVE_LOCATION'), 'success');
        }
    }

    /**
     * Setter for the params
     *
     * @return  Registry  Module parameters
     *
     * @since   4.5.0
     * @throws  \Exception
     */
    protected static function setParams($params = null)
    {
        if (\is_null(self::$params)) {
            if (!\is_null($params)) {
                self::$params = $params;
            } else {
                if (\is_null(self::$moduleId)) {
                    throw new \Exception('Module ID is needed in order to load params from db!', 1);
                }
                self::loadParams();
            }
        }

        return self::$params;
    }

    /**
     * Setter for the moduleId
     *
     * @return  int
     *
     * @since   4.5.0
     */
    protected static function setID(int $id): int
    {
        self::$moduleId = $id;

        return $id;
    }

    /**
     * Load params from database
     *
     * @return  void
     *
     * @since   4.5.0
     * @throws \Exception
     */
    protected static function loadParams()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__modules'))
                    ->where($db->quoteName('id') . ' = ' . self::$moduleId);

        $db->setQuery($query);

        self::$params = new Registry($db->loadResult());
    }

    /**
     * Write params to database
     *
     * @param   Registry   $params   New module parameters
     *
     * @return  mixed      A database cursor resource on success, boolean false on failure.
     *
     * @since   4.5.0
     * @throws \Exception
     */
    protected static function writeParams(Registry $params)
    {
        if (\is_null(self::$moduleId)) {
            throw new \Exception('Module ID is needed in order to write params to db!', 1);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__modules'))
                          ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString('json')))
                          ->where($db->quoteName('id') . ' = ' . self::$moduleId);

        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Get adress based on coordinates
     *
     * @param   string     $lat     Latitude
     * @param   string     $lng     Longitude
     *
     * @return  string|false   Adress on success, false otherwise
     *
     * @since   4.5.0
     */
    protected static function resolveLocation($lat, $lng = '')
    {
        if ($lng == '') {
            $loc_arr = explode(',', $lat, 2);
            $lat     = trim($loc_arr[0]);
            $lng     = trim($loc_arr[1]);
        }

        if (self::$params->get('location', '51.5000,0.0000') == $lat . ',' . $lng) {
            return self::$params->get('location_name', 'London, England, GB');
        }

        $url  = 'https://nominatim.openstreetmap.org/reverse';
        $vars = ['format' => 'jsonv2', 'lat' => trim($lat), 'lon' => trim($lng)];

        if (!$data = self::fetchAPI($url, $vars)) {
            return $lat . ', ' . $lng;
        }

        if ($data && isset($data['address'])) {
            $loc = '';

            // Get town/city
            if (isset($data['address']['city'])) {
                $loc .= $data['address']['city'];
            } elseif (isset($data['address']['town'])) {
                $loc .= $data['address']['town'];
            }

            // Get state
            if (isset($data['address']['state'])) {
                $loc .= empty($loc) ? '' : ', ';
                $loc .= $data['address']['state'];
            }

            // Get country code
            if (isset($data['address']['country_code'])) {
                $loc .= empty($loc) ? '' : ', ';
                $loc .= strtoupper($data['address']['country_code']);
            }

            // Write updates to db
            try {
                self::$params->set('location_name', $loc);
                self::writeParams(self::$params);
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(Text::_('MOD_COMMUNITY_ERROR_SAVE_LOCATION') . ' ' . $e->getMessage(), 'warning');
            }

            return $loc;
        }

        Factory::getApplication()->enqueueMessage(Text::_('MOD_COMMUNITY_ERROR_FETCH_API', $url, 200, 'No data received'), 'warning');

        return $lat . ', ' . $lng;
    }

    /**
     * Returns the Super Users email information
     *
     * @return  array  The list of Super User emails
     *
     * @since   4.5.0
     */
    protected static function getSuperUserMails()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Get a list of groups which have Super User privileges
        $ret = ['info@example.org'];

        try {
            $rootId    = Table::getInstance('Asset')->getRootId();
            $rules     = Access::getAssetRules($rootId)->getData();
            $rawGroups = $rules['core.admin']->getData();
            $groups    = [];

            if (empty($rawGroups)) {
                return $ret;
            }

            foreach ($rawGroups as $g => $enabled) {
                if ($enabled) {
                    $groups[] = $g;
                }
            }

            if (empty($groups)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user IDs of users belonging to the SA groups
        try {
            $query = $db->getQuery(true)
              ->select($db->quoteName('user_id'))
              ->from($db->quoteName('#__user_usergroup_map'))
              ->whereIn($db->quoteName('group_id'), $groups);

            $db->setQuery($query);
            $userIDs = $db->loadColumn(0);

            if (empty($userIDs)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user information for the Super Administrator users
        try {
            $query = $db->getQuery(true)
              ->select('email')
              ->from($db->quoteName('#__users'))
              ->whereIn($db->quoteName('id'), $userIDs);

            $db->setQuery($query);
            $ret = $db->loadColumn();
        } catch (\Exception $exc) {
            return $ret;
        }

        return $ret;
    }

    /**
     * Fix a geolocation string
     *
     * @param   string   $geolocation   Geolocation string
     *
     * @return  string   Fixed string
     *
     * @since   4.5.0
     */
    protected static function fixGeolocation(string $geolocation): string
    {
        $coor_arr = explode(',', $geolocation, 2);

        $lat_arr = explode('.', $coor_arr[0], 2);
        $lng_arr = explode('.', $coor_arr[1], 2);

        // Create the form 51.5000,0.0000
        $geolocation = trim($lat_arr[0]) . '.' . trim(substr($lat_arr[1], 0, 4)) . ',' . trim($lng_arr[0]) . '.' . trim(substr($lng_arr[1], 0, 4));

        return $geolocation;
    }

    /**
     * Fetches data from endpoints
     *
     * @param   string   $url         Request url
     * @param   array    $variables   Request variables
     * @param   string   $format      The expected format of the returned content
     *
     * @return  mixed    The fetched content on success, false otherwise
     *
     * @since   4.5.0
     */
    protected static function fetchAPI(string $url, array $variables, string $format = 'json')
    {
        $domain    = str_replace(Uri::base(true), '', Uri::base());
        $email     = self::getSuperUserMails()[0];
        $target    = $url . '?' . http_build_query($variables);

        // Create options
        $options = new Registry();
        $options->set('userAgent', $email);
        $options->set('headers', ['Referer' => trim($domain)]);

        // Fetch address from joomla.org
        try {
            $response = HttpFactory::getHttp($options)->get($target);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_COMMUNITY_ERROR_FETCH_API', $target, $e->getCode(), $e), 'warning');

            return false;
        }

        if ($response->code != 200) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_COMMUNITY_ERROR_FETCH_API', $target, $response->code, $response->body), 'warning');

            return false;
        }

        // Decode received data
        if ($response->body) {
            try {
                if ($format == 'json') {
                    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
                } elseif ($format == 'xml') {
                    libxml_use_internal_errors(true);
                    $data = simplexml_load_string($response->body);

                    if ($data === false) {
                        $errors                       = libxml_get_errors();
                        [$xml_err_code, $xml_err_msg] = self::xmlError($errors);
                        Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_COMMUNITY_ERROR_FETCH_API', $target, $xml_err_code, 'Invalid XML.' . $xml_err_msg), 'warning');
                        libxml_clear_errors();
                    }
                } else {
                    $data = $response->body;
                }
            } catch (\Exception $e) {
                $data = false;
                Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_COMMUNITY_ERROR_FETCH_API', $target, 200, $e->getMessage()), 'warning');
            }
        }

        return $data;
    }

    /**
     * Adds language constants to JavaScript
     *
     * @since   4.5.0
     */
    public static function addText()
    {
        Text::script('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
        Text::script('MOD_COMMUNITY_SUCCESS_SAVE_LOCATION');
        Text::script('MOD_COMMUNITY_MSG_SAVE_LOCATION_NOT_NEEDED');
        Text::script('MOD_COMMUNITY_ERROR_FETCH_API');
        Text::script('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
        Text::script('MOD_COMMUNITY_MSG_NO_LOCATIONS_FOUND');
        Text::script('MOD_COMMUNITY_MSG_GEOLOCATION_NOT_SUPPORTED');
        Text::script('MOD_COMMUNITY_ERROR_GET_LOCATION');
    }

    /**
     * Extract an error from libxml
     *
     * @param   array    $errors   Errors from libxml_get_errors()
     * @param   integer  $limit    Max number of errors shown
     *
     * @return  array   [Error code, Error message]
     *
     * @since   4.5.0
     */
    protected static function xmlError($errors, $limit = 1)
    {
        $return = '';

        foreach ($errors as $i => $error) {
            $return .= "\n";

            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $return .= "XML Warning $error->code: ";
                    break;
                case LIBXML_ERR_ERROR:
                    $return .= "XML Error $error->code: ";
                    break;
                case LIBXML_ERR_FATAL:
                    $return .= "XML Fatal Error $error->code: ";
                    break;
            }

            $return .= trim($error->message) . " ($error->line, $error->column);";

            if ($i == $limit - 1) {
                // We reached the limit
                break;
            }
        }

        return [$errors[0]->code, $return];
    }
}
