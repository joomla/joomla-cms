<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Overrides;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View for language overrides list.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The items to list.
     *
     * @var     array
     * @since   2.5
     */
    protected $items;

    /**
     * The pagination object.
     *
     * @var     object
     * @since   2.5
     */
    protected $pagination;

    /**
     * The model state.
     *
     * @var     object
     * @since   2.5
     */
    protected $state;

    /**
     * An array containing all frontend and backend languages
     *
     * @var    array
     * @since  4.0.0
     */
    protected $languages;

    /**
     * Displays the view.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function display($tpl = null)
    {
        $this->state         = $this->get('State');
        $this->items         = $this->get('Overrides');
        $this->languages     = $this->get('Languages');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors));
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Adds the page title and toolbar.
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function addToolbar()
    {
        // Get the results for each action
        $canDo = ContentHelper::getActions('com_languages');

        ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_OVERRIDES_TITLE'), 'comments langmanager');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('override.add');
        }

        if ($canDo->get('core.delete') && $this->pagination->total) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'overrides.delete', 'JTOOLBAR_DELETE');
        }

        if ($this->getCurrentUser()->authorise('core.admin')) {
            ToolbarHelper::custom('overrides.purge', 'refresh', '', 'COM_LANGUAGES_VIEW_OVERRIDES_PURGE', false);
        }

        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_languages');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('Languages:_Overrides');
    }
}
