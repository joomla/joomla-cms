<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\View\Style;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a template style.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The CMSObject (on success, false on failure)
     *
     * @var   CMSObject
     */
    protected $item;

    /**
     * The form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The model state
     *
     * @var  CMSObject
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var    CMSObject
     *
     * @since  4.0.0
     */
    protected $canDo;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');
        $this->canDo = ContentHelper::getActions('com_templates');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
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
        Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);
        $canDo = $this->canDo;

        ToolbarHelper::title(
            $isNew ? Text::_('COM_TEMPLATES_MANAGER_ADD_STYLE')
            : Text::_('COM_TEMPLATES_MANAGER_EDIT_STYLE'),
            'paint-brush thememanager'
        );

        $toolbarButtons = [];

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            ToolbarHelper::apply('style.apply');
            $toolbarButtons[] = ['save', 'style.save'];
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            $toolbarButtons[] = ['save2copy', 'style.save2copy'];
        }

        ToolbarHelper::saveGroup(
            $toolbarButtons,
            'btn-success'
        );

        if (empty($this->item->id)) {
            ToolbarHelper::cancel('style.cancel');
        } else {
            ToolbarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();

        // Get the help information for the template item.
        $lang = Factory::getLanguage();
        $help = $this->get('Help');

        if ($lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = null;
        }

        ToolbarHelper::help($help->key, false, $url);
    }
}
