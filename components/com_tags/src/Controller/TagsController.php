<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Controller;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * The Tags List Controller
 *
 * @since  3.1
 */
class TagsController extends BaseController
{
    /**
     * Method to search tags with AJAX
     *
     * @return  void
     */
    public function searchAjax()
    {
        $user = $this->app->getIdentity();

        // Receive request data
        $filters = array(
            'like'      => trim($this->input->get('like', null, 'string')),
            'title'     => trim($this->input->get('title', null, 'string')),
            'flanguage' => $this->input->get('flanguage', null, 'word'),
            'published' => $this->input->get('published', 1, 'int'),
            'parent_id' => $this->input->get('parent_id', 0, 'int'),
            'access'    => $user->getAuthorisedViewLevels(),
        );

        if ((!$user->authorise('core.edit.state', 'com_tags')) && (!$user->authorise('core.edit', 'com_tags'))) {
            // Filter on published for those who do not have edit or edit.state rights.
            $filters['published'] = 1;
        }

        $results = TagsHelper::searchTags($filters);

        if ($results) {
            // Output a JSON object
            echo json_encode($results);
        }

        $this->app->close();
    }
}
