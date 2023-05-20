<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;

use function defined;

/**
 * Jed master display controller.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $default_view = 'jedtickets';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  DisplayController
     *
     * @since   3.9.0
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = []): DisplayController
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', $this->default_view);
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            $model = $this->getModel($vName);
            $view->setModel($model, true);

            if ($vName === 'extension') {
                // We need to add extra views
                // For the default layout, we need to also push the action logs model into the view
                $extensionimagesModel      = $this->getModel('Extensionimages');
                $extensionscoresModel      = $this->getModel('Extensionscores');
                $extensionvarieddataModel  = $this->getModel('Extensionvarieddata');
                $extensionimageModel       = $this->getModel('Extensionimage');
                $extensionscoreModel       = $this->getModel('Extensionscore');
                $extensionvarieddatumModel = $this->getModel('Extensionvarieddatum');


                // And push the model into the view
                $view->setModel($extensionimagesModel, false);
                $view->setModel($extensionscoresModel, false);
                $view->setModel($extensionvarieddataModel, false);
                $view->setModel($extensionimageModel, false);
                $view->setModel($extensionscoreModel, false);
                $view->setModel($extensionvarieddatumModel, false);
            }

            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            $view->display();
        }

        return $this;
    }
}
