<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\View\Modules;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\View\ListView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of modules.
 *
 * @since  1.6
 */
class HtmlView extends ListView
{
    /**
     * The client ID for the modules we're showing
     *
     * @var int
     *
     * @since 6.0.0
     */
    protected $clientId;

    /**
     * The help link for the view
     *
     * @var string
     */
    protected $helpLink = 'Modules';

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_modules';
        }

        $config['toolbar_icon']   = 'cube module';
        $config['supports_batch'] = true;

        parent::__construct($config);
    }

    /**
     * Prepare view data
     *
     * @return  void
     */
    protected function initializeView()
    {
        parent::initializeView();

        /**
         * @var ListModel
         */
        $model = $this->getModel();

        $this->total         = $model->getTotal();
        $this->clientId      = (int) $this->state->get('client_id', 0);

        $this->canDo = ContentHelper::getActions('com_modules');

        $this->toolbarTitle = $this->clientId == 1 ? 'COM_MODULES_MANAGER_MODULES_ADMIN' : 'COM_MODULES_MANAGER_MODULES_SITE';

        /**
         * The code below make sure the remembered position will be available from filter dropdown even if there are no
         * modules available for this position. This will make the UI less confusing for users in case there is only one
         * module in the selected position and user:
         * 1. Edit the module, change it to new position, save it and come back to Modules Management Screen
         * 2. Or move that module to new position using Batch action
         */
        if (\count($this->items) === 0 && $this->state->get('filter.position')) {
            $selectedPosition = $this->state->get('filter.position');
            $positionField    = $this->filterForm->getField('position', 'filter');

            $positionExists = false;

            foreach ($positionField->getOptions() as $option) {
                if ($option->value === $selectedPosition) {
                    $positionExists = true;
                    break;
                }
            }

            if ($positionExists === false) {
                $positionField->addOption($selectedPosition, ['value' => $selectedPosition]);
            }
        }

        // We do not need the Language filter when modules are not filtered
        if ($this->clientId == 1 && !ModuleHelper::isAdminMultilang()) {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }

        // We don't need the toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } else {
            // If in modal layout.
            // Client id selector should not exist.
            $this->filterForm->removeField('client_id', '');

            // If in the frontend state and language should not activate the search tools.
            if (Factory::getApplication()->isClient('site')) {
                unset($this->activeFilters['state'], $this->activeFilters['language']);
            }
        }
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
        $canDo = $this->canDo;

        $canCreate = $canDo->get('core.create');

        if ($canCreate) {
            $this->getDocument()->getToolbar()->standardButton('new', 'JTOOLBAR_NEW')
                ->onclick("location.href='index.php?option=com_modules&amp;view=select&amp;client_id=" . $this->clientId . "'");
        }

        // Prevent showing default add button
        $canDo->set('core.create', false);

        parent::addToolbar();

        $canDo->set('core.create', $canCreate);

        // We add the duplicate button if there is the default dropdown
        if ($canCreate) {

            /**
             * @var \Joomla\CMS\Toolbar\Toolbar $toolbar
             */
            $toolbar = $this->getDocument()->getToolbar();

            $buttons = $toolbar->getItems();

            foreach ($buttons as $button) {
                if ($button->getName() === 'status-group') {
                    $childBar = $button->getChildToolbar();

                    $childBar->standardButton('copy', 'JTOOLBAR_DUPLICATE', 'modules.duplicate')
                        ->listCheck(true);

                    break;
                }
            }
        }
    }
}
