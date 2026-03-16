<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Banners;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\ListView;
use Joomla\Component\Banners\Administrator\Model\BannersModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of banners.
 *
 * @since  1.6
 */
class HtmlView extends ListView
{
    /**
     * Category data
     *
     * @var    array
     * @since  1.6
     */
    protected $categories = [];

    /**
     * The help link for the view
     *
     * @var string
     */
    protected $helpLink = 'Banners';

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since 6.0.0
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_banners';
        }

        $config['toolbar_icon']   = 'bookmark banners';
        $config['supports_batch'] = true;
        $config['category']       = 'com_banners';

        parent::__construct($config);
    }

    /**
     * Prepare view data
     *
     * @return  void
     *
     * @since 6.0.0
     */
    protected function initializeView()
    {
        parent::initializeView();

        /** @var BannersModel $model */
        $model            = $this->getModel();

        $this->categories = $model->getCategoryOrders();
        $this->canDo      = ContentHelper::getActions('com_banners', 'category', $this->state->get('filter.category_id'));

        // We do not need to filter by language when multilingual is disabled
        if (!Multilanguage::isEnabled()) {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }
    }
}
