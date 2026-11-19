<?php

/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\Controller;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\SecondaryCategoriesHelper;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Utilities\ArrayHelper;

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
     * Method to preprocess data before saving it.
     *
     * @param   array  $data  The data to be processed.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function preprocessSaveData(array $data): array
    {
        if (($this->input->getMethod() === 'PATCH') && !(\array_key_exists('secondary_categories', $data))) {
            $helper                       = new SecondaryCategoriesHelper('com_banners.banner');
            $data['secondary_categories'] = $helper->getCurrentSecondaryCategoriesByItem((int) $data['id']);
        }

        if (\array_key_exists('secondary_categories', $data)) {
            $data['secondary_categories'] = array_values(array_filter(ArrayHelper::toInteger((array) $data['secondary_categories'])));
        }

        return parent::preprocessSaveData($data);
    }
}
