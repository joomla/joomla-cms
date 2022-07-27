<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Postinstall display controller
 *
 * @since  3.6
 */

class DisplayController extends BaseController
{
    /**
     * @var     string  The default view.
     * @since   1.6
     */
    protected $default_view = 'messages';

    /**
     * Provide the data for a badge in a menu item via JSON
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getMenuBadgeData()
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_postinstall')) {
            throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        $model = $this->getModel('Messages');

        echo new JsonResponse($model->getItemsCount());
    }
}
