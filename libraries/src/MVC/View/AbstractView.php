<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\DocumentAwareInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageAwareInterface;
use Joomla\CMS\Language\LanguageAwareTrait;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\EventInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
abstract class AbstractView extends CMSObject implements ViewInterface, DispatcherAwareInterface, DocumentAwareInterface, LanguageAwareInterface
{
    use DispatcherAwareTrait;
    use LanguageAwareTrait;

    /**
     * The active document object
     *
     * @var    Document
     * @since  3.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *             Use $this->getDocument() instead
     */
    public $document;

    /**
     * The URL option for the component. It is usually passed by controller while it creates the view
     *
     * @var    string
     * @since  3.0
     */
    protected $option = null;

    /**
     * The name of the view
     *
     * @var    string
     * @since  3.0
     */
    protected $_name = null;

    /**
     * Registered models
     *
     * @var    array
     * @since  3.0
     */
    protected $_models = [];

    /**
     * The default model
     *
     * @var    string
     * @since  3.0
     */
    protected $_defaultModel = null;

    /**
     * Constructor
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          name: the name (optional) of the view (defaults to the view class name suffix).
     *                          charset: the character set to use for display
     *                          escape: the name (optional) of the function to use for escaping strings
     *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
     *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
     *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
     *                          layout: the layout (optional) to use to display the view
     *
     * @since   3.0
     */
    public function __construct($config = [])
    {
        // Set the view name
        if (empty($this->_name)) {
            if (\array_key_exists('name', $config)) {
                $this->_name = $config['name'];
            } else {
                $this->_name = $this->getName();
            }
        }

        // Set the component name if passed
        if (!empty($config['option'])) {
            $this->option = $config['option'];
        }
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.0
     */
    abstract public function display($tpl = null);

    /**
     * Method to get data from a registered model or a property of the view
     *
     * @param   string  $property  The name of the method to call on the model or the property to get
     * @param   string  $default   The name of the model to reference or the default value [optional]
     *
     * @return  mixed  The return value of the method
     *
     * @since   3.0
     */
    public function get($property, $default = null)
    {
        // If $model is null we use the default model
        if ($default === null) {
            $model = $this->_defaultModel;
        } else {
            $model = strtolower($default);
        }

        // First check to make sure the model requested exists
        if (isset($this->_models[$model])) {
            // Model exists, let's build the method name
            $method = 'get' . ucfirst($property);

            // Does the method exist?
            if (method_exists($this->_models[$model], $method)) {
                // The method exists, let's call it and return what we get
                return $this->_models[$model]->$method();
            }
        }

        // Degrade to CMSObject::get
        return parent::get($property, $default);
    }

    /**
     * Method to get the model object
     *
     * @param   string  $name  The name of the model (optional)
     *
     * @return  BaseDatabaseModel  The model object
     *
     * @since   3.0
     */
    public function getModel($name = null)
    {
        if ($name === null) {
            $name = $this->_defaultModel;
        }

        return $this->_models[strtolower($name)];
    }

    /**
     * Method to add a model to the view.  We support a multiple model single
     * view system by which models are referenced by classname.  A caveat to the
     * classname referencing is that any classname prepended by \JModel will be
     * referenced by the name without \JModel, eg. \JModelCategory is just
     * Category.
     *
     * @param   BaseDatabaseModel  $model    The model to add to the view.
     * @param   boolean            $default  Is this the default model?
     *
     * @return  BaseDatabaseModel  The added model.
     *
     * @since   3.0
     */
    public function setModel($model, $default = false)
    {
        $name                 = strtolower($model->getName());
        $this->_models[$name] = $model;

        if ($default) {
            $this->_defaultModel = $name;
        }

        return $model;
    }

    /**
     * Method to get the view name
     *
     * The model name by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @return  string  The name of the model
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function getName()
    {
        if (empty($this->_name)) {
            $reflection = new \ReflectionClass($this);

            if ($viewNamespace = $reflection->getNamespaceName()) {
                $pos = strrpos($viewNamespace, '\\');

                if ($pos !== false) {
                    $this->_name = strtolower(substr($viewNamespace, $pos + 1));
                }
            } else {
                $className = \get_class($this);
                $viewPos   = strpos($className, 'View');

                if ($viewPos != false) {
                    $this->_name = strtolower(substr($className, $viewPos + 4));
                }
            }

            if (empty($this->_name)) {
                throw new \Exception(sprintf($this->text('JLIB_APPLICATION_ERROR_GET_NAME'), __METHOD__), 500);
            }
        }

        return $this->_name;
    }

    /**
     * Get the Document.
     *
     * @return  Document
     *
     * @since   4.4.0
     * @throws  \UnexpectedValueException May be thrown if the document has not been set.
     */
    protected function getDocument(): Document
    {
        if ($this->document) {
            return $this->document;
        }

        throw new \UnexpectedValueException('Document not set in ' . __CLASS__);
    }

    /**
     * Set the document to use.
     *
     * @param   Document  $document  The document to use
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }

    /**
     * Get the event dispatcher.
     *
     * The override was made to keep a backward compatibility for legacy component.
     * TODO: Remove the override in 6.0
     *
     * @return  DispatcherInterface
     *
     * @since   4.4.0
     * @throws  \UnexpectedValueException May be thrown if the dispatcher has not been set.
     */
    public function getDispatcher()
    {
        if (!$this->dispatcher) {
            @trigger_error(
                sprintf('Dispatcher for %s should be set through MVC factory. It will throw an exception in 6.0', __CLASS__),
                E_USER_DEPRECATED
            );

            return Factory::getContainer()->get(DispatcherInterface::class);
        }

        return $this->dispatcher;
    }

    /**
     * Dispatches the given event on the internal dispatcher, does a fallback to the global one.
     *
     * @param   EventInterface  $event  The event
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @deprecated 4.4 will be removed in 6.0. Use $this->getDispatcher() directly.
     */
    protected function dispatchEvent(EventInterface $event)
    {
        $this->getDispatcher()->dispatch($event->getName(), $event);

        @trigger_error(
            sprintf(
                'Method %s is deprecated and will be removed in 6.0. Use getDispatcher()->dispatch() directly.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
    }
}
