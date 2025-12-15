<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\CategoryForm;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\CategoryFormModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML Category Form View class for the Content component.
 *
     * @since   __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object.
     *
     * @var    \Joomla\CMS\Form\Form
     * @since   __DEPLOY_VERSION__
     */
    protected $form;

    /**
     * The item being edited.
     *
     * @var    \stdClass
     * @since   __DEPLOY_VERSION__
     */
    protected $item;

    /**
     * The model state.
     *
     * @var    \Joomla\Registry\Registry
     * @since   __DEPLOY_VERSION__
     */
    protected $state;

    /**
     * The page to return to after submission.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $return_page = '';

    /**
     * The page parameters.
     *
     * @var    \Joomla\Registry\Registry|null
     * @since   __DEPLOY_VERSION__
     */
    protected $params = null;

    /**
     * The page class suffix.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $pageclass_sfx = '';

    /**
     * The user object.
     *
     * @var    \Joomla\CMS\User\User|null
     * @since   __DEPLOY_VERSION__
     */
    protected $user = null;

    /**
     * Should we show Save As Copy button?
     *
     * @var    boolean
     * @since   __DEPLOY_VERSION__
     */
    protected $showSaveAsCopy = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     */
    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        /** @var CategoryFormModel $model */
        $model             = $this->getModel();
        $this->state       = $model->getState();
        $this->item        = $model->getItem();
        $this->form        = $model->getForm();
        $this->return_page = $model->getReturnPage();

        if (!\is_object($this->item) || $this->form === false) {
            throw new GenericDataException(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $authorised = !empty($this->item->accessEdit);

        if ($authorised !== true) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        // Create a shortcut to the parameters.
        $params = $this->state->get('params');

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

        $this->params = $params;
        $this->user   = $user;

        Factory::getLanguage()->load('com_categories', JPATH_SITE);
        Factory::getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);
        Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);
        Factory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

        if (!$this->item->id && Multilanguage::isEnabled()) {
            $this->form->setFieldAttribute('language', 'default', $this->getLanguage()->getTag());
        }

        $this->showSaveAsCopy = false;

        // Add form control fields.
        $this->form
            ->addControlField('task', '')
            ->addControlField('return', $this->return_page ?? '');

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        $app           = Factory::getApplication();
        $categoryTitle = $this->item && $this->item->id ? $this->item->title : Text::_('COM_CONTENT_FORM_EDIT_CATEGORY');

        // Force heading/title to the category title to avoid inheriting the active menu (Home) title.
        $this->params->set('page_title', $categoryTitle);
        $this->params->set('page_heading', $categoryTitle);

        $this->setDocumentTitle($categoryTitle);

        // Reset pathway and build the category crumb; the breadcrumbs module prepends Home.
        $pathway = $app->getPathway();
        $pathway->setPathway([]);

        if ($this->item && $this->item->id) {
            $pathway->addItem(
                $categoryTitle,
                Route::_(RouteHelper::getCategoryRoute($this->item->id, $this->item->language))
            );
        } else {
            $pathway->addItem($categoryTitle);
        }

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
