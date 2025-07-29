<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\Controller;

use Joomla\CMS\Language\Text;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The healthcheck controller
 *
 * @since  5.4.0
 */
class HealthcheckController extends BaseController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  5.4.0
     */
    protected $contentType = 'healthcheck';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  5.4.0
     */
    protected $default_view = 'healthcheck';

    /**
     * Show the healthcheck information
     *
     * @return HealthcheckController
     *
     * @since 5.4.0
     */
    public function show()
    {
        $this->validateUpdateToken();

        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var JsonApiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        /** @var UpdateModel $model */
        $model = $this->getModel('Update', 'Administrator', ['ignore_request' => true, 'state' => $this->modelState]);

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        // Push the model into the view (as default)
        $view->setModel($model, true);

        $view->setDocument($this->app->getDocument());

        $view->healthCheck();

        // If we reach this point, healthcheck was successful, so update the date in the config
        $model->updateLastHealthCheck();

        return $this;
    }

    /**
     * Basic display of an item view. We don't allow this
     *
     * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   5.4.0
     */
    public function displayItem($id = null)
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * List view amended to add filtering of data. We don't allow this
     *
     * @return  static  A BaseController object to support chaining.
     *
     * @since   5.4.0
     */
    public function displayList()
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * Removes an item.
     *
     * @param   integer  $id  The primary key to delete item.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function delete($id = null)
    {
        throw new \RuntimeException('Not implemented', 501);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * We don't allow editing from API (yet?)
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   5.4.0
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return false;
    }

    /**
     * Method to check if you can add a new record.
     *
     * We don't allow adding from API
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   5.4.0
     */
    protected function allowAdd($data = [])
    {
        return false;
    }
}
