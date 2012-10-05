JControllerBase
===============

Construction
------------

The constructor for JControllerBase takes an optional JInput object and
an optional JApplciationBase object. If either is omitted, the
constructor defers to the protected loadInput and loadApplication
methods respectively. These methods can be overriden in derived classes
if the default application and request input is not appropriate.

Usage
-----

The JControllerBase class is abstract so cannot be used directly. The
derived class must implement the execute method to satisfy the interface
requirements. Note that the execute method no longer takes a "task"
argument as each controller class. Multi-task controllers are still
possible by overriding the execute method in derived classes. Each
controller class should do just one sort of 'thing', such as saving,
deleting, checking in, checking out and so on. However, controllers, or
even models and views, have the liberty of invoking other controllers to
allow for HMVC architectures.

    /**
     * My custom controller.
     *
     * @package  Examples
     *
     * @since   12.1
     */
    class MyController extends JControllerBase
    {
        /**
         * Method to execute the controller.
         *
         * @return  void
         *
         * @since   12.1
         * @throws  RuntimeException
         */
        public function execute()
        {
            echo time();
        }
    }

    // Instantiate the controller.
    $controller = new MyController;

    // Print the time.
    $controller->execute();

Serialization
-------------

The JControllerBase class implements Serializable. When serializing,
only the input property is serialized. When unserializing, the input
variable is unserialized and the internal application property is loaded
at runtime.
