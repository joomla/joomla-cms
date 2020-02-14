<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 3 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
// no direct access
defined('JPATH_PLATFORM') or die;

JLoader::import('joomla.application.component.controller');

/**
 * JCE Component Controller.
 *
 * @since 1.5
 */
class JceController extends JControllerLegacy
{
    /**
     * @var string The extension for which the categories apply
     *
     * @since  1.6
     */
    protected $extension;

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings
     *
     * @see     JController
     * @since   1.5
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Guess the JText message prefix. Defaults to the option.
        if (empty($this->extension)) {
            $this->extension = $this->input->get('extension', 'com_jce');
        }
    }

    /**
     * Method to display a view.
     *
     * @param bool  $cachable  If true, the view output will be cached
     * @param array $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
     *
     * @return JController This object to support chaining
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Get the document object.
        $document = JFactory::getDocument();
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        JFactory::getLanguage()->load('com_jce', JPATH_ADMINISTRATOR);

        // Set the default view name and format from the Request.
        $vName = $app->input->get('view', 'cpanel');
        $vFormat = $document->getType();
        $lName = $app->input->get('layout', 'default');

        // legacy front-end popup view
        if ($vName === "popup") {
            // add a view path
            $this->addViewPath(JPATH_SITE . '/components/com_jce/views');
            $view = $this->getView($vName, $vFormat);

            if ($view) {
                $view->display();
            }

            return $this;
        }

        $adminViews = array('config', 'profiles', 'profile', 'mediabox');

        if (in_array($vName, $adminViews) && !$user->authorise('core.manage', 'com_jce')) {
            throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // create view
        $view = $this->getView($vName, $vFormat);

        // Get and render the view.
        if ($view) {

            if ($vName !== "cpanel" && !$user->authorise('jce.' . $vName, 'com_jce')) {
                throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
            }

            // Get the model for the view.
            $model = $this->getModel($vName, 'JceModel', array('name' => $vName));

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            // Load the submenu.
            require_once JPATH_ADMINISTRATOR . '/components/com_jce/helpers/admin.php';

            JceHelperAdmin::addSubmenu($vName);

            $document->addStyleSheet('components/com_jce/media/css/global.min.css?' . WF_VERSION);

            $view->display();
        }

        return $this;
    }
}
