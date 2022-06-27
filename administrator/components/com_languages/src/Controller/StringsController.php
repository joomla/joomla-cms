<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Languages Strings JSON Controller
 *
 * @since  2.5
 */
class StringsController extends AdminController
{
    /**
     * Method for refreshing the cache in the database with the known language strings
     *
     * @return  void
     *
     * @since   2.5
     */
    public function refresh()
    {
        echo new JsonResponse($this->getModel('strings')->refresh());
    }

    /**
     * Method for searching language strings
     *
     * @return  void
     *
     * @since   2.5
     */
    public function search()
    {
        echo new JsonResponse($this->getModel('strings')->search());
    }
}
