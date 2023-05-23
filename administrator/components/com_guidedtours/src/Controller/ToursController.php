<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guidedtours list controller class.
 *
 * @since 4.3.0
 */

class ToursController extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param   string $name   The model name. Optional.
     * @param   string $prefix The class prefix. Optional.
     * @param   array  $config The array of possible config values. Optional.
     *
     * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since 4.3.0
     */
    public function getModel($name = 'Tour', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to duplicate tours
     *
     * @return  void
     *
     * @since   4.3.0
     *
     * @throws  Exception
     */
    public function duplicate()
    {
        // Check for request forgeries.
        $this->checkToken();

        $pks = (array) $this->input->post->get('cid', [], 'int');
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURS_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $model = $this->getModel();
            $model->duplicate($pks);

            $this->setMessage(Text::plural('COM_GUIDEDTOURS_TOURS_DUPLICATED', count($pks)));
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
        $this->setRedirect(Route::_('index.php?option=com_guidedtours&view=tours' . $this->getRedirectToListAppend(), false));
    }

    /**
     * Method to export tours
     *
     * @return  void
     *
     * @since   5.0.0
     *
     * @throws  Exception
     */
    public function export()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Access checks.
        if (!$this->app->getIdentity()->authorise('core.create', 'com_guidedtours')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        $pks = (array) $this->input->post->get('cid', [], 'int');
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURS_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $model = $this->getModel();

            $data = [];

            foreach ($pks as $pk) {
                // Get the tour data.
                $tour = $model->getItem($pk);

                $tour_data = [
                    'title' => $tour->title,
                    'description' => $tour->description,
                    'extensions' => $tour->extensions,
                    'url' => $tour->url,
                    'published' => $tour->published,
                    'language' => $tour->language,
                    'note' => $tour->note,
                    'access' => $tour->access,
                ];

                // Get the steps data.
                $steps = $model->getSteps($pk);

                $steps_data = [];

                foreach ($steps as $step) {
                    $step_data = [
                        'title' => $step->title,
                        'description' => $step->description,
                        'position' => $step->position,
                        'target' => $step->target,
                        'type' => $step->type,
                        'interactive_type' => $step->interactive_type,
                        'url' => $step->url,
                        'published' => $step->published,
                        'language' => $step->language,
                        'note' => $step->note,
                    ];

                    $steps_data[] = $step_data;
                }

                $tour_data['steps'] = $steps_data;

                $data[$pk] = $tour_data;
            }

            $this->app->setHeader('Content-Type', 'application/json', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $this->input->getCmd('view', 'joomla') . '.json"', true)
                ->setHeader('Cache-Control', 'must-revalidate', true)
                ->sendHeaders();

            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $this->app->close();

        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_guidedtours&view=tours' . $this->getRedirectToListAppend(), false));
        }
    }

    /**
     * Method to import tours through the path of a .json file
     *
     * @param   string $filePath   The path to a .json file
     *
     * @return  boolean|integer
     *
     * @since   5.0.0
     *
     * @throws  Exception
     */
    public function importFromFilePath($filePath)
    {
        if (!File::exists($filePath)) {
            return false;
        }

        // Check if the file has the right file type.
        if (strtolower(File::getExt($filePath)) !== 'json') {
            return false;
        }

        // Load the file data.
        $data = file_get_contents($filePath);

        if ($data === false) {
            return false;
        }

        $model = $this->getModel();

        return $model->import($data);
    }

    /**
     * Method to import tours through a .json file
     *
     * @return  void
     *
     * @since   5.0.0
     *
     * @throws  Exception
     */
    public function import()
    {
        // Access checks.
        if (!$this->app->getIdentity()->authorise('core.create', 'com_guidedtours')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        // Check for request forgeries.
        $this->checkToken();

        $file     = $this->input->files->get('importfile', [], 'array');
        $redirect = Route::_('index.php?option=com_guidedtours&view=tours' . $this->getRedirectToListAppend(), false);

        // Check if the file exists.
        if (!isset($file['name'])) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_INVALID_REQUEST'), 'error');
            return;
        }

        // Check if there was a problem uploading the file.
        if ($file['error']) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_FILE_ERROR'), 'error');
            return;
        }

        // Check if the file has the right file type.
        if ($file['type'] !== 'application/json') {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_WRONG_FILE_TYPE_ERROR'), 'error');
            return;
        }

        if (!File::exists($file['tmp_name'])) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_MISSING_FILE_ERROR'), 'error');
            return;
        }

        // Load the file data.
        $data = file_get_contents($file['tmp_name']);

        if ($data === false) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_PARSING_FILE_ERROR'), 'error');
            return;
        }

        $model = $this->getModel();

        try {
            // Set default message on error - overwrite if successful
            $this->setMessage(Text::_('COM_GUIDEDTOURS_TOURS_IMPORT_NO_TOUR_IMPORTED'), 'error');

            if ($count = $model->import($data)) {
                    $this->setMessage(Text::plural('COM_GUIDEDTOURS_TOURS_IMPORTED', $count));
            }
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect($redirect);
    }
}
