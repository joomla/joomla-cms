<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Controller;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;

/**
 * Content article class.
 *
 * @since  1.6.0
 */
class ArticleController extends FormController
{
    use VersionableControllerTrait;

    /**
     * The URL view item variable.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_item = 'form';

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_list = 'categories';

    /**
     * The URL edit variable.
     *
     * @var    string
     * @since  3.2
     */
    protected $urlVar = 'a.id';

    /**
     * Method to add a new record.
     *
     * @return  mixed  True if the record can be added, an error object if not.
     *
     * @since   1.6
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());

            return;
        }

        // Redirect to the edit screen.
        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item . '&a_id=0'
                . $this->getRedirectToItemAppend(),
                false
            )
        );

        return true;
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = array())
    {
        $user       = $this->app->getIdentity();
        $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int');
        $allow      = null;

        if ($categoryId) {
            // If the category has been passed in the data or URL check it.
            $allow = $user->authorise('core.create', 'com_content.category.' . $categoryId);
        }

        if ($allow === null) {
            // In the absence of better information, revert to the component permissions.
            return parent::allowAdd();
        } else {
            return $allow;
        }
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user = $this->app->getIdentity();

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_content.article.' . $recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_content.article.' . $recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->get('id') == $record->created_by;
        }

        return false;
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   1.6
     */
    public function cancel($key = 'a_id')
    {
        $result = parent::cancel($key);

        /** @var SiteApplication $app */
        $app = $this->app;

        // Load the parameters.
        $params = $app->getParams();

        $customCancelRedir = (bool) $params->get('custom_cancel_redirect');

        if ($customCancelRedir) {
            $cancelMenuitemId = (int) $params->get('cancel_redirect_menuitem');

            if ($cancelMenuitemId > 0) {
                $item = $app->getMenu()->getItem($cancelMenuitemId);
                $lang = '';

                if (Multilanguage::isEnabled()) {
                    $lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
                }

                // Redirect to the user specified return page.
                $redirlink = $item->link . $lang . '&Itemid=' . $cancelMenuitemId;
            } else {
                // Redirect to the same article submission form (clean form).
                $redirlink = $app->getMenu()->getActive()->link . '&Itemid=' . $app->getMenu()->getActive()->id;
            }
        } else {
            $menuitemId = (int) $params->get('redirect_menuitem');

            if ($menuitemId > 0) {
                $lang = '';
                $item = $app->getMenu()->getItem($menuitemId);

                if (Multilanguage::isEnabled()) {
                    $lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
                }

                // Redirect to the general (redirect_menuitem) user specified return page.
                $redirlink = $item->link . $lang . '&Itemid=' . $menuitemId;
            } else {
                // Redirect to the return page.
                $redirlink = $this->getReturnPage();
            }
        }

        $this->setRedirect(Route::_($redirlink, false));

        return $result;
    }

    /**
     * Method to edit an existing record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     * (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if access level check and checkout passes, false otherwise.
     *
     * @since   1.6
     */
    public function edit($key = null, $urlVar = 'a_id')
    {
        $result = parent::edit($key, $urlVar);

        if (!$result) {
            $this->setRedirect(Route::_($this->getReturnPage(), false));
        }

        return $result;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.5
     */
    public function getModel($name = 'Form', $prefix = 'Site', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   1.6
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
    {
        // Need to override the parent method completely.
        $tmpl   = $this->input->get('tmpl');

        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        // @todo This is a bandaid, not a long term solution.
        /**
         * if ($layout)
         * {
         *  $append .= '&layout=' . $layout;
         * }
         */

        $append .= '&layout=edit';

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }

        $itemId = $this->input->getInt('Itemid');
        $return = $this->getReturnPage();
        $catId  = $this->input->getInt('catid');

        if ($itemId) {
            $append .= '&Itemid=' . $itemId;
        }

        if ($catId) {
            $append .= '&catid=' . $catId;
        }

        if ($return) {
            $append .= '&return=' . base64_encode($return);
        }

        return $append;
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return  string  The return URL.
     *
     * @since   1.6
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base();
        } else {
            return base64_decode($return);
        }
    }

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = 'a_id')
    {
        $result    = parent::save($key, $urlVar);

        if (\in_array($this->getTask(), ['save2copy', 'apply'], true)) {
            return $result;
        }

        $app       = $this->app;
        $articleId = $app->input->getInt('a_id');

        // Load the parameters.
        $params   = $app->getParams();
        $menuitem = (int) $params->get('redirect_menuitem');

        // Check for redirection after submission when creating a new article only
        if ($menuitem > 0 && $articleId == 0) {
            $lang = '';

            if (Multilanguage::isEnabled()) {
                $item = $app->getMenu()->getItem($menuitem);
                $lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
            }

            // If ok, redirect to the return page.
            if ($result) {
                $this->setRedirect(Route::_('index.php?Itemid=' . $menuitem . $lang, false));
            }
        } elseif ($this->getTask() === 'save2copy') {
            // Redirect to the article page, use the redirect url set from parent controller
        } else {
            // If ok, redirect to the return page.
            if ($result) {
                $this->setRedirect(Route::_($this->getReturnPage(), false));
            }
        }

        return $result;
    }

    /**
     * Method to reload a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  void
     *
     * @since   3.8.0
     */
    public function reload($key = null, $urlVar = 'a_id')
    {
        parent::reload($key, $urlVar);
    }

    /**
     * Method to save a vote.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function vote()
    {
        // Check for request forgeries.
        $this->checkToken();

        $user_rating = $this->input->getInt('user_rating', -1);

        if ($user_rating > -1) {
            $url = $this->input->getString('url', '');
            $id = $this->input->getInt('id', 0);
            $viewName = $this->input->getString('view', $this->default_view);
            $model = $this->getModel($viewName);

            // Don't redirect to an external URL.
            if (!Uri::isInternal($url)) {
                $url = Route::_('index.php');
            }

            if ($model->storeVote($id, $user_rating)) {
                $this->setRedirect($url, Text::_('COM_CONTENT_ARTICLE_VOTE_SUCCESS'));
            } else {
                $this->setRedirect($url, Text::_('COM_CONTENT_ARTICLE_VOTE_FAILURE'));
            }
        }
    }
}
