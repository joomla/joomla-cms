<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Select;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Fields\Administrator\Model\SelectModel;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field type selection view.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $state;

    /**
     * An array of field types
     *
     * @var  \stdClass[]
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $items;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        /** @var SelectModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->state = $model->getState();
        $this->items = $model->getItems();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function addToolbar()
    {
        $component = $this->state->get('filter.component');
        $section   = $this->state->get('filter.section');
        $toolbar   = $this->getDocument()->getToolbar();

        // Avoid nonsense situation.
        if ($component == 'com_fields') {
            return;
        }

        // Load component language file
        $lang = $this->getLanguage();
        $lang->load($component, JPATH_ADMINISTRATOR)
        || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

        // Add page title
        ToolbarHelper::title(
            Text::sprintf('COM_FIELDS_VIEW_FIELD_ADD_TITLE', Text::_(strtoupper($component))),
            'puzzle field-add ' . substr($component, 4) . ($section ? "-$section" : '') . '-field-add'
        );

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('toolbar.cancelselect');

        $toolbar->customButton('new')
            ->html($layout->render(['context' => $this->state->get('filter.context')]));
    }
}
