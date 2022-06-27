<?php

/**
 * @package     Joomla.API
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Privacy\Api\View\Requests\JsonapiView;

/**
 * The requests controller
 *
 * @since  4.0.0
 */
class RequestsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'requests';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'requests';

    /**
     * Export request data
     *
     * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   4.0.0
     */
    public function export($id = null)
    {
        if ($id === null) {
            $id = $this->input->get('id', 0, 'int');
        }

        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var JsonapiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $model = $this->getModel('export');

        try {
            $modelName = $model->getName();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $model->setState($modelName . '.request_id', $id);

        $view->setModel($model, true);

        $view->document = $this->app->getDocument();
        $view->export();

        return $this;
    }
}
