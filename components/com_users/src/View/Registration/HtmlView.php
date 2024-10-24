<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Registration;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Users\Site\Model\RegistrationModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registration view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Registration form data
     *
     * @var  \stdClass|false
     */
    protected $data;

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
     * The HtmlDocument instance
     *
     * @var  HtmlDocument
     */
    public $document;

    /**
     * Should we show a captcha form for the submission of the article?
     *
     * @var    boolean
     *
     * @since  3.7.0
     */
    protected $captchaEnabled = false;

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
     * @since   1.6
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        /** @var RegistrationModel $model */
        $model        = $this->getModel();
        $this->form   = $model->getForm();
        $this->data   = $model->getData();
        $this->state  = $model->getState();
        $this->params = $this->state->get('params');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check for layout override
        $active = Factory::getApplication()->getMenu()->getActive();

        if (isset($active->query['layout'])) {
            $this->setLayout($active->query['layout']);
        }

        $captchaSet = $this->params->get('captcha', Factory::getApplication()->get('captcha', '0'));

        foreach (PluginHelper::getPlugins('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                $this->captchaEnabled = true;
                break;
            }
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
            $this->params->def('page_heading', Text::_('COM_USERS_REGISTRATION'));
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
