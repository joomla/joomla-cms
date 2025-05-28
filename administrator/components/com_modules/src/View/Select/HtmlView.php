<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\View\Select;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Modules\Administrator\Model\SelectModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Modules component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * A suffix for links for modal use
     *
     * @var  string
     */
    protected $modalLink;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var SelectModel $model */
        $model = $this->getModel();

        $this->state     = $model->getState();
        $this->items     = $model->getItems();
        $this->modalLink = '';

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $state    = $this->state;
        $clientId = (int) $state->get('client_id', 0);
        $toolbar  = $this->getDocument()->getToolbar();

        // Add page title
        ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_SITE'), 'cube module');

        if ($clientId === 1) {
            ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_ADMIN'), 'cube module');
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('toolbar.cancelselect');

        $toolbar->customButton('new')
            ->html($layout->render(['client_id' => $clientId]));
    }
}
