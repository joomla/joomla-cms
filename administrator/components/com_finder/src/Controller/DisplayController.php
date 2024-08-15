<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base controller class for Finder.
 *
 * @since  2.5
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  2.5
     */
    protected $default_view = 'index';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static|boolean   A Controller object to support chaining or false on failure.
     *
     * @since   2.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        $view     = $this->input->get('view', 'index', 'word');
        $layout   = $this->input->get('layout', 'index', 'word');
        $filterId = $this->input->get('filter_id', null, 'int');

        // Check for edit form.
        if ($view === 'filter' && $layout === 'edit' && !$this->checkEditId('com_finder.edit.filter', $filterId)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $filterId), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_finder&view=filters', false));

            return false;
        }

        return parent::display();
    }
}
