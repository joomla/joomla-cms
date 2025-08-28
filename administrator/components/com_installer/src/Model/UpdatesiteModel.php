<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Item Model for an update site.
 *
 * @since  4.0.0
 */
class UpdatesiteModel extends AdminModel
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  4.0.0
     */
    public $typeAlias = 'com_installer.updatesite';

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @throws  \Exception
     *
     * @since   4.0.0
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_installer.updatesite', 'updatesite', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   4.0.0
     */
    protected function loadFormData()
    {
        $data = $this->getItem();
        $this->preprocessData('com_installer.updatesite', $data);

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  \stdClass|boolean  Object on success, false on failure.
     *
     * @since   4.0.0
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        $db           = $this->getDatabase();
        $updateSiteId = (int) $item->update_site_id ?? 0;
        $query        = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        'update_sites.extra_query',
                        'extensions.type',
                        'extensions.element',
                        'extensions.folder',
                        'extensions.client_id',
                        'extensions.checked_out',
                    ]
                )
            )
            ->from($db->quoteName('#__update_sites', 'update_sites'))
            ->join(
                'INNER',
                $db->quoteName('#__update_sites_extensions', 'update_sites_extensions'),
                $db->quoteName('update_sites_extensions.update_site_id') . ' = ' . $db->quoteName('update_sites.update_site_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__extensions', 'extensions'),
                $db->quoteName('extensions.extension_id') . ' = ' . $db->quoteName('update_sites_extensions.extension_id')
            )
            ->where($db->quoteName('update_sites.update_site_id') . ' = :updatesiteid')
            ->bind(':updatesiteid', $updateSiteId, ParameterType::INTEGER);

        $db->setQuery($query);
        $extension = $db->loadObject();

        $downloadKey = InstallerHelper::getDownloadKey($extension);

        $item->extra_query      = $downloadKey['value'] ?? '';
        $item->downloadIdPrefix = $downloadKey['prefix'] ?? '';
        $item->downloadIdSuffix = $downloadKey['suffix'] ?? '';

        return $item;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   4.0.0
     */
    public function save($data): bool
    {
        // Apply the extra_query. Always empty when saving a free extension's update site.
        if (isset($data['extra_query'])) {
            $data['extra_query'] = $data['downloadIdPrefix'] . $data['extra_query'] . $data['downloadIdSuffix'];
        }

        // Force Joomla to recheck for updates
        $data['last_check_timestamp'] = 0;

        $result = parent::save($data);

        if (!$result) {
            return $result;
        }

        // Delete update records forcing Joomla to fetch them again, applying the new extra_query.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__updates'))
            ->where($db->quoteName('update_site_id') . ' = :updateSiteId');
        $query->bind(':updateSiteId', $data['update_site_id'], ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
        } catch (\Exception) {
            // No problem if this fails for any reason.
        }

        return true;
    }
}
