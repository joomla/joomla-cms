<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Suggestions \JSON controller for Finder.
 *
 * @since  2.5
 */
class SuggestionsController extends BaseController
{
    /**
     * Method to find search query suggestions. Uses awesomplete
     *
     * @return  void
     *
     * @since   3.4
     */
    public function suggest()
    {
        $app = $this->app;
        $app->mimeType = 'application/json';

        // Ensure caching is disabled as it depends on the query param in the model
        $app->allowCache(false);

        $suggestions = $this->getSuggestions();

        // Send the response.
        $app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
        $app->sendHeaders();
        echo '{ "suggestions": ' . json_encode($suggestions) . ' }';
    }

    /**
     * Method to find search query suggestions for OpenSearch
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function opensearchsuggest()
    {
        $app = $this->app;
        $app->mimeType = 'application/json';
        $result = array();
        $result[] = $app->input->request->get('q', '', 'string');

        $result[] = $this->getSuggestions();

        // Ensure caching is disabled as it depends on the query param in the model
        $app->allowCache(false);

        // Send the response.
        $app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
        $app->sendHeaders();
        echo json_encode($result);
    }

    /**
     * Method to retrieve the data from the database
     *
     * @return  array  The suggested words
     *
     * @since   3.4
     */
    protected function getSuggestions()
    {
        $return = array();

        $params = ComponentHelper::getParams('com_finder');

        if ($params->get('show_autosuggest', 1)) {
            // Get the suggestions.
            $model = $this->getModel('Suggestions');
            $return = $model->getItems();
        }

        // Check the data.
        if (empty($return)) {
            $return = array();
        }

        return $return;
    }
}
