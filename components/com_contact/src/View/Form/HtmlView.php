<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\View\Form;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Contact\Administrator\Helper\ContactHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML Contact View class for the Contact component
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var    \Joomla\CMS\Form\Form
     * @since  4.0.0
     */
    protected $form;

    /**
     * @var    object
     * @since  4.0.0
     */
    protected $item;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $return_page;

    /**
     * @var    string
     * @since  4.0.0
     */
    protected $pageclass_sfx;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $state;

    /**
     * @var    \Joomla\Registry\Registry
     * @since  4.0.0
     */
    protected $params;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     *
     * @throws \Exception
     * @since  4.0.0
     */
    public function display($tpl = null)
    {
        $user = $this->getCurrentUser();
        $app  = Factory::getApplication();

        // Get model data.
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');

        if (empty($this->item->id)) {
            $authorised = $user->authorise('core.create', 'com_contact') || count($user->getAuthorisedCategories('com_contact', 'core.create'));
        } else {
            // Since we don't track these assets at the item level, use the category id.
            $canDo      = ContactHelper::getActions('com_contact', 'category', $this->item->catid);
            $authorised = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by === $user->id);
        }

        if ($authorised !== true) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        $this->item->tags = new TagsHelper();

        if (!empty($this->item->id)) {
            $this->item->tags->getItemTags('com_contact.contact', $this->item->id);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            $app->enqueueMessage(implode("\n", $errors), 'error');

            return false;
        }

        // Create a shortcut to the parameters.
        $this->params = $this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

        // Override global params with contact specific params
        $this->params->merge($this->item->params);

        // Propose current language as default when creating new contact
        if (empty($this->item->id) && Multilanguage::isEnabled()) {
            $lang = Factory::getLanguage()->getTag();
            $this->form->setFieldAttribute('language', 'default', $lang);
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @throws \Exception
     *
     * @since  4.0.0
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $app->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_CONTACT_FORM_EDIT_CONTACT'));
        }

        $title = $this->params->def('page_title', Text::_('COM_CONTACT_FORM_EDIT_CONTACT'));

        $this->setDocumentTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
