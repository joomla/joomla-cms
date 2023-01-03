<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Language;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Languages component.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The active item
     *
     * @var  object
     */
    public $item;

    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $form;

    /**
     * The model state
     *
     * @var  CMSObject
     */
    public $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var    CMSObject
     *
     * @since  4.0.0
     */
    protected $canDo;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        $this->state = $this->get('State');
        $this->canDo = ContentHelper::getActions('com_languages');

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
        Factory::getApplication()->getInput()->set('hidemainmenu', 1);
        $isNew   = empty($this->item->lang_id);
        $canDo   = $this->canDo;
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_NEW_TITLE' : 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_EDIT_TITLE'),
            'comments-2 langmanager'
        );

        if (($isNew && $canDo->get('core.create')) || (!$isNew && $canDo->get('core.edit'))) {
            $toolbar->apply('language.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');

        $saveGroup->configure(
            function (Toolbar $childBar) use ($canDo, $isNew) {
                if (($isNew && $canDo->get('core.create')) || (!$isNew && $canDo->get('core.edit'))) {
                    $childBar->save('language.save');
                }

                // If an existing item, can save to a copy only if we have create rights.
                if ($canDo->get('core.create')) {
                    $childBar->save2new('language.save2new');
                }
            }
        );

        if ($isNew) {
            $toolbar->cancel('language.cancel');
        } else {
            $toolbar->cancel('language.cancel');
        }

        $toolbar->divider();
        $toolbar->help('Languages:_Edit_Content_Language');
    }
}
