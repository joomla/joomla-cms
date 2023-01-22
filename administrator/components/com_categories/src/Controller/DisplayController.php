<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories view class for the Category package.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'categories';

    /**
     * The extension for which the categories apply.
     *
     * @var    string
     * @since  1.6
     */
    protected $extension;

    /**
     * Constructor.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface|null  $factory  The factory.
     * @param   CMSApplication|null       $app      The Application for the dispatcher
     * @param   Input|null                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Guess the Text message prefix. Defaults to the option.
        if (empty($this->extension)) {
            $this->extension = $this->input->get('extension', 'com_content');
        }
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static|boolean  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', 'categories');
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');
        $id      = $this->input->getInt('id');

        // Check for edit form.
        if ($vName == 'category' && $lName == 'edit' && !$this->checkEditId('com_categories.edit.category', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_categories&view=categories&extension=' . $this->extension, false));

            return false;
        }

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            // Get the model for the view.
            $model = $this->getModel($vName, 'Administrator', ['name' => $vName . '.' . substr($this->extension, 4)]);

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;
            $view->display();
        }

        return $this;
    }
}
