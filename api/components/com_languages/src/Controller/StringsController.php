<?php

/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Tobscure\JsonApi\Exception\InvalidParameterException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The strings controller
 *
 * @since  4.0.0
 */
class StringsController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'strings';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'strings';

    /**
     * Search by languages constants
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @throws  InvalidParameterException
     * @since   4.0.0
     */
    public function search()
    {
        $data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

        if (!isset($data['searchstring']) || !\is_string($data['searchstring'])) {
            throw new InvalidParameterException("Invalid param 'searchstring'");
        }

        if (!isset($data['searchtype']) || !\in_array($data['searchtype'], ['constant', 'value'])) {
            throw new InvalidParameterException("Invalid param 'searchtype'");
        }

        $this->input->set('searchstring', $data['searchstring']);
        $this->input->set('searchtype', $data['searchtype']);
        $this->input->set('more', 0);

        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var \Joomla\Component\Languages\Api\View\Strings\JsonapiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        /** @var \Joomla\Component\Languages\Administrator\Model\StringsModel $model */
        $model = $this->getModel($this->contentType, '', ['ignore_request' => true]);

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        // Push the model into the view (as default)
        $view->setModel($model, true);

        $view->document = $this->app->getDocument();
        $view->displayList();

        return $this;
    }

    /**
     * Refresh cache
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function refresh()
    {
        /** @var \Joomla\Component\Languages\Administrator\Model\StringsModel $model */
        $model = $this->getModel($this->contentType, '', ['ignore_request' => true]);

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        $result = $model->refresh();

        if ($result instanceof \Exception) {
            throw $result;
        }

        return $this;
    }
}
