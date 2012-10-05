JModelBase
==========

Construction
------------

The contructor for JModelBase takes an optional JRegistry object that
defines the state of the model. If omitted, the contructor defers to the
protected loadState method. This method can be overriden in a derived
class and takes the place of the populateState method used in the legacy
model class.

Usage
-----

The JModelBase class is abstract so cannot be used directly. All
requirements of the interface are already satisfied by the base class.

    /**
     * My custom model.
     *
     * @pacakge  Examples
     *
     * @since   12.1
     */
    class MyModel extends JModelBase
    {
        /**
         * Get the time.
         *
         * @return  integer
         *
         * @since   12.1
         */
        public function getTime()
        {
            return time();
        }
    }
