<?php

/**
 * @package        JED
 *
 * @copyright      (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Review controller class.
 *
 * @since  4.0.0
 */
class ReviewController extends FormController
{
    protected $view_list = 'reviews';


    /**
     * setPublished
     *
     * function for ajax setting a review's published status
     *
     * @since 4.0.0
     * @throws Exception
     */

    public function setPublished()
    {
        //  Session::checkToken('post') or die;
        $app       = Factory::getApplication();
        $review_id = $app->input->get('itemId', 0, 'int');

        $option_id = $app->input->get('optionId', 0, 'int');
        $db        = Factory::getContainer()->get('DatabaseDriver');

        $fields     = [$db->quoteName('published') . ' = ' . $db->quote($option_id)];
        $conditions = [$db->quoteName('id') . ' = ' . $db->quote($review_id)];


        $queryUpdate = $db->getQuery(true)
            ->update($db->quoteName('#__jed_reviews'))->set($fields)->where($conditions);
        $db->setQuery($queryUpdate);
        $db->execute();
    }
}
