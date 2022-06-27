<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Banners Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * Method when a banner is clicked on.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function click()
    {
        $id = $this->input->getInt('id', 0);

        if ($id) {
            /** @var \Joomla\Component\Banners\Site\Model\BannerModel $model */
            $model = $this->getModel('Banner', 'Site', array('ignore_request' => true));
            $model->setState('banner.id', $id);
            $model->click();
            $this->setRedirect($model->getUrl());
        }
    }
}
