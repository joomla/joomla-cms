<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Reset;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Users\Site\Model\ResetModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Reset view class for Users.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The page parameters
     *
     * @var  \Joomla\Registry\Registry|null
     */
    protected $params;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The page class suffix
     *
     * @var    string
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  The template file to include
     *
     * @return  void
     *
     * @since   1.5
     */
    public function display($tpl = null)
    {
        // This name will be used to get the model
        $name = $this->getLayout();

        /** @var ResetModel $model */
        $model = $this->getModel();

        // Check that the name is valid - has an associated model.
        if (!\in_array($name, ['confirm', 'complete'])) {
            $name = 'default';
        }

        if ('default' === $name) {
            $this->form = $model->getForm();
        } elseif ($name === 'confirm') {
            $this->form = $model->getResetConfirmForm();
        } elseif ($name === 'complete') {
            $this->form = $model->getResetCompleteForm();
        }

        $this->state  = $model->getState();
        $this->params = $this->state->params;

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''), ENT_COMPAT, 'UTF-8');

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function prepareDocument()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_USERS_RESET'));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
