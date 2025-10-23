<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\View\Link;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Redirect\Administrator\Model\LinkModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a redirect link.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The model state
     *
     * @var   \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        /** @var LinkModel $model */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = ($this->item->id == 0);
        $canDo   = ContentHelper::getActions('com_redirect');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title($isNew ? Text::_('COM_REDIRECT_MANAGER_LINK_NEW') : Text::_('COM_REDIRECT_MANAGER_LINK_EDIT'), 'map-signs redirect');

        if ($canDo->get('core.edit')) {
            $toolbar->apply('link.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');

        $saveGroup->configure(
            function (Toolbar $childBar) use ($canDo) {
                // If not checked out, can save the item.
                if ($canDo->get('core.edit')) {
                    $childBar->save('link.save');
                }

                /**
                 * This component does not support Save as Copy due to uniqueness checks.
                 * While it can be done, it causes too much confusion if the user does
                 * not change the Old URL.
                 */
                if ($canDo->get('core.edit') && $canDo->get('core.create')) {
                    $childBar->save2new('link.save2new');
                }
            }
        );

        if (empty($this->item->id)) {
            $toolbar->cancel('link.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $toolbar->cancel('link.cancel');
        }

        $toolbar->help('Redirects:_New_or_Edit');
    }
}
