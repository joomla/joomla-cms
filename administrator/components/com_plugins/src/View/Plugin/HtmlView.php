<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\View\Plugin;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Plugins\Administrator\Model\PluginModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a plugin.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item object for the newsfeed
     *
     * @var   \stdClass
     */
    protected $item;

    /**
     * The form object for the newsfeed
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The model state of the newsfeed
     *
     * @var   \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $ignore_fieldsets = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var PluginModel $model */
        $model = $this->getModel();

        $this->state = $model->getState();
        $this->item  = $model->getItem();
        $this->form  = $model->getForm();

        if ($this->getLayout() === 'modalreturn') {
            parent::display($tpl);

            return;
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        } else {
            $this->addModalToolbar();
        }

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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $canDo   = ContentHelper::getActions('com_plugins');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::sprintf('COM_PLUGINS_MANAGER_PLUGIN', Text::_($this->item->name)), 'plug plugin');

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            $toolbar->apply('plugin.apply');

            $toolbar->save('plugin.save');
        }

        $toolbar->cancel('plugin.cancel');
        $toolbar->divider();

        // Get the help information for the plugin item.
        $lang = $this->getLanguage();

        /** @var PluginModel $model */
        $model = $this->getModel();
        $help  = $model->getHelp();

        if ($help->url && $lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url   = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = null;
        }

        $toolbar->inlinehelp();
        $toolbar->help($help->key, false, $url);
    }

    /**
     * Add the modal toolbar.
     *
     * @return  void
     *
     * @since   5.1.0
     *
     * @throws  \Exception
     */
    protected function addModalToolbar()
    {
        $canDo   = ContentHelper::getActions('com_plugins');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::sprintf('COM_PLUGINS_MANAGER_PLUGIN', Text::_($this->item->name)), 'plug plugin');

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            $toolbar->apply('plugin.apply');

            $toolbar->save('plugin.save');
        }

        $toolbar->cancel('plugin.cancel');

        $toolbar->inlinehelp();
    }
}
