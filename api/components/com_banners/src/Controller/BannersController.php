<?php

/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\Controller;

use Joomla\CMS\Categories\CategoryChangeTrait;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\ApiController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The banners controller
 *
 * @since  4.0.0
 */
class BannersController extends ApiController
{
    use CategoryChangeTrait;

    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'banners';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'banners';

    /**
     * Banner list view amended to add filtering of data
     *
     * @return  static  A BaseController object to support chaining.
     *
     * @since   6.1.0
     */
    public function displayList()
    {
        $apiFilterInfo = $this->input->get('filter', [], 'array');
        $filter        = InputFilter::getInstance();

        if (\array_key_exists('state', $apiFilterInfo)) {
            $this->modelState->set('filter.published', $filter->clean($apiFilterInfo['state'], 'INT'));
        }

        return parent::displayList();
    }

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

        $table = $this->getModel('banner')->getTable();

        if (!$table->load($recordId)) {
            return false;
        }

        $user     = $this->app->getIdentity();
        $category = $this->option . '.category.' . (int) $table->catid;

        if (!$user->authorise('core.edit', $category)) {
            return false;
        }

        return $this->allowCategoryChange(
            $table,
            $recordId,
            $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array')
        );
    }
}
