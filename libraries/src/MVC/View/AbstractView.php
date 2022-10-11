<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
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
abstract class AbstractView implements ViewInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * The active document object
     *
     * @var    Document
     * @since  3.0
     */
    private $document;

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
     * @var    array
     * @since  3.0
     */
    protected $_name = null;

    /**
     * Registered models
     *
     * @var    array
     * @since  3.0
     */
    protected $_models = array();

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
     * @param   array  $cfg  The document object to inject into the
     *
     * @since   3.0
     */
    public function __construct($config = array())
    {
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

        if (isset($this->{$property})) {
            @trigger_error("Class properties should be retrieved directly through \$this->$property in version 6.0", E_USER_DEPRECATED);
            return $this->{$property};
        }

        return $default;
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
        $name = strtolower($model->getName());
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
                throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
            }
        }

        return $this->_name;
    }

    /**
     * Method to get the component name
     *
     * @return  string  The name of the component
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function getComponentName()
    {
        if (empty($this->option)) {
            $this->option = ApplicationHelper::getComponentName();
        }

        return $this->option;
    }

    /**
     * Method to get the document object
     *
     * @return  Document  The name of the component
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Method to set the document object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \InvalidArgumentException
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Magic method to access properties of the application.
     *
     * @param   string  $name  The name of the property.
     *
     * @return  mixed   A value if the property name is valid, null otherwise.
     *
     * @since       4.0.0
     * @deprecated  6.0  This is a B/C proxy for deprecated read accesses
     */
    public function __get($name)
    {
        switch ($name) {
            case 'document':
                @trigger_error(
                    'Accessing the document property of the view is deprecated, use the getDocument() method instead.',
                    E_USER_DEPRECATED
                );

                return $this->getDocument();

            default:
                $trace = debug_backtrace();
                trigger_error(
                    sprintf(
                        'Undefined property via __get(): %1$s in %2$s on line %3$s',
                        $name,
                        $trace[0]['file'],
                        $trace[0]['line']
                    ),
                    E_USER_NOTICE
                );
        }
    }

    /**
     * Magic method to access properties of the application.
     *
     * @param   string  $name  The name of the property.
     *
     * @return  void
     *
     * @since       4.0.0
     * @deprecated  5.0  This is a B/C proxy for deprecated read accesses
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'document':
                @trigger_error(
                    'Setting the document property of the view is deprecated, use the setDocument() method instead.',
                    E_USER_DEPRECATED
                );

                $this->setDocument($value);
                break;

            default:
                $trace = debug_backtrace();
                trigger_error(
                    sprintf(
                        'Undefined property via __set(): %1$s in %2$s on line %3$s',
                        $name,
                        $trace[0]['file'],
                        $trace[0]['line']
                    ),
                    E_USER_NOTICE
                );
        }
    }

    /**
     * Dispatches the given event on the internal dispatcher, does a fallback to the global one.
     *
     * @param   EventInterface  $event  The event
     *
     * @return  EventInterface  The event returned from the dispatch call
     *
     * @since   4.1.0
     */
    protected function dispatchEvent(EventInterface $event)
    {
        try {
            $result = $this->getDispatcher()->dispatch($event->getName(), $event);
        } catch (\UnexpectedValueException $e) {
            $result = Factory::getContainer()->get(DispatcherInterface::class)->dispatch($event->getName(), $event);
        }

        return $result;
    }
}
