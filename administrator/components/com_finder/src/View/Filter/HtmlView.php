<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Filter;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Filter view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The filter object
     *
     * @var    \Joomla\Component\Finder\Administrator\Table\FilterTable
     *
     * @since  3.6.2
     */
    protected $filter;

    /**
     * The Form object
     *
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  3.6.2
     */
    protected $form;

    /**
     * The active item
     *
     * @var    \stdClass
     *
     * @since  3.6.2
     */
    protected $item;

    /**
     * The model state
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  3.6.2
     */
    protected $state;

    /**
     * The total indexed items
     *
     * @var    integer
     *
     * @since  3.8.0
     */
    protected $total;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public $ignore_fieldsets = [];

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    public function display($tpl = null)
    {
        // Load the view data.
        $this->filter = $this->get('Filter');
        $this->item   = $this->get('Item');
        $this->form   = $this->get('Form');
        $this->state  = $this->get('State');
        $this->total  = $this->get('Total');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Configure the toolbar.
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Method to configure the toolbar for this view.
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew      = ($this->item->filter_id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $this->getCurrentUser()->id);
        $canDo      = ContentHelper::getActions('com_finder');
        $toolbar    = $this->getDocument()->getToolbar();

        // Configure the toolbar.
        ToolbarHelper::title(
            $isNew ? Text::_('COM_FINDER_FILTER_NEW_TOOLBAR_TITLE') : Text::_('COM_FINDER_FILTER_EDIT_TOOLBAR_TITLE'),
            'zoom-in finder'
        );

        // Set the actions for new and existing records.
        if ($isNew) {
            // For new records, check the create permission.
            if ($canDo->get('core.create')) {
                $toolbar->apply('filter.apply');
                $saveGroup = $toolbar->dropdownButton('save-group');
                $saveGroup->configure(
                    function (Toolbar $childBar) {
                        $childBar->save('filter.save');
                        $childBar->save2new('filter.save2new');
                    }
                );
            }

            $toolbar->cancel('filter.cancel', 'JTOOLBAR_CANCEL');
        } else {
            // Can't save the record if it's checked out.
            // Since it's an existing record, check the edit permission.
            if (!$checkedOut && $canDo->get('core.edit')) {
                $toolbar->apply('filter.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $canDo) {
                    // Can't save the record if it's checked out.
                    // Since it's an existing record, check the edit permission.
                    if (!$checkedOut && $canDo->get('core.edit')) {
                        $childBar->save('filter.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new('filter.save2new');
                        }
                    }

                    // If an existing item, can save as a copy
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('filter.save2copy');
                    }
                }
            );

            $toolbar->cancel('filter.cancel');
        }

        $toolbar->divider();
        $toolbar->help('Smart_Search:_New_or_Edit_Filter');
    }
}
