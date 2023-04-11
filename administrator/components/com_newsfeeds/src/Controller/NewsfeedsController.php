<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeeds list controller class.
 *
 * @since  1.6
 */
class NewsfeedsController extends AdminController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Newsfeed', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the number of published newsfeeds for quickicons
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('newsfeeds');

        $model->setState('filter.published', 1);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_NEWSFEEDS_N_QUICKICON_SRONLY', $amount);
        $result['name']   = Text::plural('COM_NEWSFEEDS_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }
}
