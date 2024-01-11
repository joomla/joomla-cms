<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Adapter;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Updater\UpdateAdapter;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension class for updater
 *
 * @since  1.7.0
 */
class ExtensionAdapter extends UpdateAdapter
{
    protected $currentUpdate;
    protected $latest;

    /**
     * Start element parser callback.
     *
     * @param   object  $parser  The parser object.
     * @param   string  $name    The name of the element.
     * @param   array   $attrs   The attributes of the element.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    protected function _startElement($parser, $name, $attrs = [])
    {
        $this->stack[] = $name;
        $tag           = $this->_getStackLocation();

        // Reset the data
        if (isset($this->$tag)) {
            $this->$tag->_data = '';
        }

        switch ($name) {
            case 'UPDATE':
                $this->currentUpdate                 = Table::getInstance('update');
                $this->currentUpdate->update_site_id = $this->updateSiteId;
                $this->currentUpdate->detailsurl     = $this->_url;
                $this->currentUpdate->folder         = '';
                $this->currentUpdate->client_id      = 1;
                $this->currentUpdate->infourl        = '';
                break;

            case 'UPDATES':
                // Don't do anything
                break;

            default:
                if (\in_array($name, $this->updatecols)) {
                    $name                       = strtolower($name);
                    $this->currentUpdate->$name = '';
                }

                if ($name === 'TARGETPLATFORM') {
                    $this->currentUpdate->targetplatform = $attrs;
                }

                if ($name === 'PHP_MINIMUM') {
                    $this->currentUpdate->php_minimum = '';
                }

                if ($name === 'SUPPORTED_DATABASES') {
                    $this->currentUpdate->supported_databases = $attrs;
                }
                break;
        }
    }

    /**
     * Character Parser Function
     *
     * @param   object  $parser  Parser object.
     * @param   object  $name    The name of the element.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    protected function _endElement($parser, $name)
    {
        array_pop($this->stack);

        switch ($name) {
            case 'UPDATE':
                // Lower case and remove the exclamation mark
                $product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));

                // Check that the product matches and that the version matches (optionally a regexp)
                if (
                    $product == $this->currentUpdate->targetplatform['NAME']
                    && preg_match('/^' . $this->currentUpdate->targetplatform['VERSION'] . '/', JVERSION)
                ) {
                    // Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
                    if (!isset($this->currentUpdate->php_minimum) || version_compare(PHP_VERSION, $this->currentUpdate->php_minimum, '>=')) {
                        $phpMatch = true;
                    } else {
                        // Notify the user of the potential update
                        $msg = Text::sprintf(
                            'JLIB_INSTALLER_AVAILABLE_UPDATE_PHP_VERSION',
                            $this->currentUpdate->name,
                            $this->currentUpdate->version,
                            $this->currentUpdate->php_minimum,
                            PHP_VERSION
                        );

                        Factory::getApplication()->enqueueMessage($msg, 'warning');

                        $phpMatch = false;
                    }

                    $dbMatch = false;

                    // Check if DB & version is supported via <supported_databases> tag, assume supported if tag isn't present
                    if (isset($this->currentUpdate->supported_databases)) {
                        $db           = Factory::getDbo();
                        $dbType       = strtolower($db->getServerType());
                        $dbVersion    = $db->getVersion();
                        $supportedDbs = $this->currentUpdate->supported_databases;

                        // MySQL and MariaDB use the same database driver but not the same version numbers
                        if ($dbType === 'mysql') {
                            // Check whether we have a MariaDB version string and extract the proper version from it
                            if (stripos($dbVersion, 'mariadb') !== false) {
                                // MariaDB: Strip off any leading '5.5.5-', if present
                                $dbVersion = preg_replace('/^5\.5\.5-/', '', $dbVersion);
                                $dbType    = 'mariadb';
                            }
                        }

                        // $supportedDbs has uppercase keys because they are XML attribute names
                        $dbTypeUcase = strtoupper($dbType);

                        // Do we have an entry for the database?
                        if (\array_key_exists($dbTypeUcase, $supportedDbs)) {
                            $minimumVersion = $supportedDbs[$dbTypeUcase];
                            $dbMatch        = version_compare($dbVersion, $minimumVersion, '>=');

                            if (!$dbMatch) {
                                // Notify the user of the potential update
                                $dbMsg = Text::sprintf(
                                    'JLIB_INSTALLER_AVAILABLE_UPDATE_DB_MINIMUM',
                                    $this->currentUpdate->name,
                                    $this->currentUpdate->version,
                                    Text::_('JLIB_DB_SERVER_TYPE_' . $dbTypeUcase),
                                    $dbVersion,
                                    $minimumVersion
                                );

                                Factory::getApplication()->enqueueMessage($dbMsg, 'warning');
                            }
                        } else {
                            // Notify the user of the potential update
                            $dbMsg = Text::sprintf(
                                'JLIB_INSTALLER_AVAILABLE_UPDATE_DB_TYPE',
                                $this->currentUpdate->name,
                                $this->currentUpdate->version,
                                Text::_('JLIB_DB_SERVER_TYPE_' . $dbTypeUcase)
                            );

                            Factory::getApplication()->enqueueMessage($dbMsg, 'warning');
                        }
                    } else {
                        // Set to true if the <supported_databases> tag is not set
                        $dbMatch = true;
                    }

                    // Check minimum stability
                    $stabilityMatch = true;

                    if (isset($this->currentUpdate->stability) && ($this->currentUpdate->stability < $this->minimum_stability)) {
                        $stabilityMatch = false;
                    }

                    // Some properties aren't valid fields in the update table so unset them to prevent J! from trying to store them
                    unset($this->currentUpdate->targetplatform);

                    if (isset($this->currentUpdate->php_minimum)) {
                        unset($this->currentUpdate->php_minimum);
                    }

                    if (isset($this->currentUpdate->supported_databases)) {
                        unset($this->currentUpdate->supported_databases);
                    }

                    if (isset($this->currentUpdate->stability)) {
                        unset($this->currentUpdate->stability);
                    }

                    // If the PHP version and minimum stability checks pass, consider this version as a possible update
                    if ($phpMatch && $stabilityMatch && $dbMatch) {
                        if (isset($this->latest)) {
                            // We already have a possible update. Check the version.
                            if (version_compare($this->currentUpdate->version, $this->latest->version, '>') == 1) {
                                $this->latest = $this->currentUpdate;
                            }
                        } else {
                            // We don't have any possible updates yet, assume this is an available update.
                            $this->latest = $this->currentUpdate;
                        }
                    }
                }
                break;

            case 'UPDATES':
                // :D
                break;
        }
    }

    /**
     * Character Parser Function
     *
     * @param   object  $parser  Parser object.
     * @param   object  $data    The data.
     *
     * @return  void
     *
     * @note    This is public because its called externally.
     * @since   1.7.0
     */
    protected function _characterData($parser, $data)
    {
        $tag = $this->_getLastTag();

        if (\in_array($tag, $this->updatecols)) {
            $tag = strtolower($tag);
            $this->currentUpdate->$tag .= $data;
        }

        if ($tag === 'PHP_MINIMUM') {
            $this->currentUpdate->php_minimum = $data;
        }

        if ($tag === 'TAG') {
            $this->currentUpdate->stability = $this->stabilityTagToInteger((string) $data);
        }
    }

    /**
     * Finds an update.
     *
     * @param   array  $options  Update options.
     *
     * @return  array|boolean  Array containing the array of update sites and array of updates. False on failure
     *
     * @since   1.7.0
     */
    public function findUpdate($options)
    {
        $response = $this->getUpdateSiteResponse($options);

        if ($response === false) {
            return false;
        }

        if (\array_key_exists('minimum_stability', $options)) {
            $this->minimum_stability = $options['minimum_stability'];
        }

        $this->xmlParser = xml_parser_create('');
        xml_set_object($this->xmlParser, $this);
        xml_set_element_handler($this->xmlParser, '_startElement', '_endElement');
        xml_set_character_data_handler($this->xmlParser, '_characterData');

        if (!xml_parse($this->xmlParser, $response->body)) {
            // If the URL is missing the .xml extension, try appending it and retry loading the update
            if (!$this->appendExtension && (substr($this->_url, -4) !== '.xml')) {
                $options['append_extension'] = true;

                return $this->findUpdate($options);
            }

            $app = Factory::getApplication();
            $app->getLogger()->warning("Error parsing url: {$this->_url}", ['category' => 'updater']);
            $app->enqueueMessage(Text::sprintf('JLIB_UPDATER_ERROR_EXTENSION_PARSE_URL', $this->_url), 'warning');

            return false;
        }

        xml_parser_free($this->xmlParser);

        if (isset($this->latest)) {
            if (isset($this->latest->client) && \strlen($this->latest->client)) {
                /**
                 * The client_id in the update XML manifest can be either an integer (backwards
                 * compatible with Joomla 1.6–3.10) or a string. Backwards compatibility with the
                 * integer key is provided as update servers with the legacy, numeric IDs cause PHP notices
                 * during update retrieval. The proper string key is one of 'site' or 'administrator'.
                 */
                $this->latest->client_id = is_numeric($this->latest->client) ? $this->latest->client
                    : ApplicationHelper::getClientInfo($this->latest->client, true)->id;

                unset($this->latest->client);
            }

            $updates = [$this->latest];
        } else {
            $updates = [];
        }

        return ['update_sites' => [], 'updates' => $updates];
    }

    /**
     * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
     * dev, alpha, beta, rc, stable) it is ignored.
     *
     * @param   string  $tag  The tag string, e.g. dev, alpha, beta, rc, stable
     *
     * @return  integer
     *
     * @since   3.4
     */
    protected function stabilityTagToInteger($tag)
    {
        $constant = '\\Joomla\\CMS\\Updater\\Updater::STABILITY_' . strtoupper($tag);

        if (\defined($constant)) {
            return \constant($constant);
        }

        return Updater::STABILITY_STABLE;
    }
}
