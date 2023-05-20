<?php

/**
 * @package    JED
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Joomla\CMS\MVC\Controller\FormController;

use function defined;

/**
 * Categories class.
 *
 * @since  4.000
 */
class CategoriesController extends FormController
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional
     * @param   array   $config  Configuration array for model. Optional
     *
     * @return object    The model
     *
     * @since    4.0.0
     */
    /*public function getModel($name = 'Categories', $prefix = 'Site', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }*/
}
