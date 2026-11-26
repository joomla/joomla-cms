<?php

/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Api\Controller;

use Joomla\CMS\Categories\CategoryChangeTrait;
use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The feeds controller
 *
 * @since  4.0.0
 */
class FeedsController extends ApiController
{
    use CategoryChangeTrait;

    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'newsfeeds';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'feeds';

    /**
     * Method to check if you can add a new record.
     *
     * Like in the administrator, the create permission is checked in the target category.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   6.2.1
     */
    protected function allowAdd($data = [])
    {
        if (!$this->allowCategoryAdd($data)) {
            return false;
        }

        return parent::allowAdd($data);
    }

    /**
     * Method to check if you can edit an existing record.
     *
     * Like in the administrator, the edit permission is checked in the category of the record, because there is no
     * asset tracking. Moving the record to another category requires the create permission in the target category.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   6.2.1
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        if (!parent::allowEdit($data, $key)) {
            return false;
        }

        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;

        if (!$recordId) {
            return true;
        }

        $table = $this->getModel('newsfeed')->getTable();

        if (!$table->load($recordId)) {
            return false;
        }

        $user     = $this->app->getIdentity();
        $category = $this->option . '.category.' . (int) $table->catid;

        if (!$user->authorise('core.edit', $category) && !($user->authorise('core.edit.own', $category) && $table->created_by == $user->id)) {
            return false;
        }

        return $this->allowCategoryChange(
            $table,
            $recordId,
            $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array')
        );
    }
}
