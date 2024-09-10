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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a template style.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item
     *
     * @var   \stdClass
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
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  4.0.0
     */
    protected $canDo;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $ignore_fieldsets = [];

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
        if (\count($errors = $this->get('Errors'))) {
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = ($this->item->id == 0);
        $canDo   = $this->canDo;
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            $isNew ? Text::_('COM_TEMPLATES_MANAGER_ADD_STYLE')
            : Text::_('COM_TEMPLATES_MANAGER_EDIT_STYLE'),
            'paint-brush thememanager'
        );

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            $toolbar->apply('style.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');

        $saveGroup->configure(
            function (Toolbar $childBar) use ($canDo, $isNew) {
                // If not checked out, can save the item.
                if ($canDo->get('core.edit')) {
                    $childBar->save('style.save');
                }

                // If an existing item, can save to a copy.
                if (!$isNew && $canDo->get('core.create')) {
                    $childBar->save2copy('style.save2copy');
                }
            }
        );

        if (empty($this->item->id)) {
            $toolbar->cancel('style.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $toolbar->cancel('style.cancel');
        }

        $toolbar->divider();

        // Get the help information for the template item.
        $lang = $this->getLanguage();
        $help = $this->get('Help');

        if ($lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url   = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = null;
        }

        $toolbar->help($help->key, false, $url);
    }
}
