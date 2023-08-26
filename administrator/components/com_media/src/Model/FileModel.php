<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * File Model
 *
 * @since  4.0.0
 */
class FileModel extends FormModel
{
    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
     *
     * @since   4.0.0
     */
    public function getForm($data = [], $loadData = true)
    {
        PluginHelper::importPlugin('media-action');

        // Load backend forms in frontend.
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_media/forms');

        // Get the form.
        $form = $this->loadForm('com_media.file', 'file', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the file information for the given path. Path must be
     * in the format: adapter:path/to/file.extension
     *
     * @param   string  $path  The path to get the information from.
     *
     * @return  \stdClass  An object with file information
     *
     * @since   4.0.0
     * @see     ApiModel::getFile()
     */
    public function getFileInformation($path)
    {
        list($adapter, $path) = explode(':', $path, 2);

        return $this->bootComponent('com_media')->getMVCFactory()->createModel('Api', 'Administrator')
            ->getFile($adapter, $path, ['url' => true, 'content' => true]);
    }
}
