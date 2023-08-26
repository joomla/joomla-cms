<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages Strings Model
 *
 * @since  2.5
 */
class StringsModel extends BaseDatabaseModel
{
    /**
     * Method for refreshing the cache in the database with the known language strings.
     *
     * @return  boolean|\Exception  True on success, \Exception object otherwise.
     *
     * @since       2.5
     */
    public function refresh()
    {
        $app = Factory::getApplication();
        $db  = $this->getDatabase();

        $app->setUserState('com_languages.overrides.cachedtime', null);

        // Empty the database cache first.
        try {
            $db->truncateTable('#__overrider');
        } catch (\RuntimeException $e) {
            return $e;
        }

        // Create the insert query.
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__overrider'))
            ->columns(
                [
                    $db->quoteName('constant'),
                    $db->quoteName('string'),
                    $db->quoteName('file'),
                ]
            );

        // Initialize some variables.
        $client   = $app->getUserState('com_languages.overrides.filter.client', 'site') ? 'administrator' : 'site';
        $language = $app->getUserState('com_languages.overrides.filter.language', 'en-GB');

        $base = constant('JPATH_' . strtoupper($client));
        $path = $base . '/language/' . $language;

        $files = [];

        // Parse common language directory.
        if (is_dir($path)) {
            $files = Folder::files($path, '.*ini$', false, true);
        }

        // Parse language directories of components.
        $files = array_merge($files, Folder::files($base . '/components', '.*ini$', 3, true));

        // Parse language directories of modules.
        $files = array_merge($files, Folder::files($base . '/modules', '.*ini$', 3, true));

        // Parse language directories of templates.
        $files = array_merge($files, Folder::files($base . '/templates', '.*ini$', 3, true));

        // Parse language directories of plugins.
        $files = array_merge($files, Folder::files(JPATH_PLUGINS, '.*ini$', 4, true));

        // Parse all found ini files and add the strings to the database cache.
        foreach ($files as $file) {
            // Only process if language file is for selected language
            if (strpos($file, $language, strlen($base)) === false) {
                continue;
            }

            $strings = LanguageHelper::parseIniFile($file);

            if ($strings) {
                $file = Path::clean($file);

                $query->clear('values')
                    ->clear('bounded');

                foreach ($strings as $key => $string) {
                    $query->values(implode(',', $query->bindArray([$key, $string, $file], ParameterType::STRING)));
                }

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (\RuntimeException $e) {
                    return $e;
                }
            }
        }

        // Update the cached time.
        $app->setUserState('com_languages.overrides.cachedtime.' . $client . '.' . $language, time());

        return true;
    }

    /**
     * Method for searching language strings.
     *
     * @return  array|\Exception  Array of results on success, \Exception object otherwise.
     *
     * @since       2.5
     */
    public function search()
    {
        $results    = [];
        $input      = Factory::getApplication()->getInput();
        $filter     = InputFilter::getInstance();
        $db         = $this->getDatabase();
        $searchTerm = $input->getString('searchstring');

        $limitstart = $input->getInt('more');

        try {
            $searchstring = '%' . $filter->clean($searchTerm, 'TRIM') . '%';

            // Create the search query.
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('constant'),
                        $db->quoteName('string'),
                        $db->quoteName('file'),
                    ]
                )
                ->from($db->quoteName('#__overrider'));

            if ($input->get('searchtype') === 'constant') {
                $query->where($db->quoteName('constant') . ' LIKE :search');
            } else {
                $query->where($db->quoteName('string') . ' LIKE :search');
            }

            $query->bind(':search', $searchstring);

            // Consider the limitstart according to the 'more' parameter and load the results.
            $query->setLimit(10, $limitstart);
            $db->setQuery($query);
            $results['results'] = $db->loadObjectList();

            // Check whether there are more results than already loaded.
            $query->clear('select')
                ->clear('limit')
                ->select('COUNT(' . $db->quoteName('id') . ')');
            $db->setQuery($query);

            if ($db->loadResult() > $limitstart + 10) {
                // If this is set a 'More Results' link will be displayed in the view.
                $results['more'] = $limitstart + 10;
            }
        } catch (\RuntimeException $e) {
            return $e;
        }

        return $results;
    }
}
