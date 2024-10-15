<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\View\History;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\Component\Contenthistory\Administrator\Model\HistoryModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of contenthistory.
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The model state
     *
     * @var  Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The toolbar for the history modal. Note this is rendered inside the modal rather than using the regular module
     *
     * @var  Toolbar
     */
    protected $toolbar;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   3.2
     */
    public function display($tpl = null)
    {
        /** @var HistoryModel $model */
        $model = $this->getModel();

        $this->state      = $model->getState();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->toolbar = $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page toolbar.
     *
     * @return  Toolbar
     *
     * @since  4.0.0
     */
    protected function addToolbar(): Toolbar
    {
        /** @var Toolbar $toolbar */
        $toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');

        // Cache a session token for reuse throughout.
        $token = Session::getFormToken();

        // Clean up input to ensure a clean url.
        $filter     = InputFilter::getInstance();
        $aliasArray = explode('.', $this->state->item_id);

        if ($aliasArray[1] === 'category') {
            $option = 'com_categories';
            $append = '&amp;extension=' . $filter->clean($aliasArray[0], 'cmd');
        } else {
            $option = $aliasArray[0];
            $append = '';
        }

        $task       = $filter->clean($aliasArray[1], 'cmd') . '.loadhistory';

        // Build the final urls.
        $loadUrl    = Route::_('index.php?option=' . $filter->clean($option, 'cmd') . $append . '&amp;task=' . $task . '&amp;' . $token . '=1');
        $previewUrl = Route::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . $token . '=1');
        $compareUrl = Route::_('index.php?option=com_contenthistory&view=compare&layout=compare&tmpl=component&' . $token . '=1');

        $toolbar->basicButton('load', 'COM_CONTENTHISTORY_BUTTON_LOAD')
            ->attributes(['data-url' => $loadUrl])
            ->icon('icon-upload')
            ->buttonClass('btn btn-success')
            ->listCheck(true);

        $toolbar->basicButton('preview', 'COM_CONTENTHISTORY_BUTTON_PREVIEW')
            ->attributes(['data-url' => $previewUrl])
            ->icon('icon-search')
            ->listCheck(true);

        $toolbar->basicButton('compare', 'COM_CONTENTHISTORY_BUTTON_COMPARE')
            ->attributes(['data-url' => $compareUrl])
            ->icon('icon-search-plus')
            ->listCheck(true);

        $toolbar->basicButton('keep', 'COM_CONTENTHISTORY_BUTTON_KEEP', 'history.keep')
            ->icon('icon-lock')
            ->listCheck(true);

        $toolbar->basicButton('delete', 'COM_CONTENTHISTORY_BUTTON_DELETE', 'history.delete')
            ->buttonClass('btn btn-danger')
            ->icon('icon-times')
            ->listCheck(true);

        return $toolbar;
    }
}
