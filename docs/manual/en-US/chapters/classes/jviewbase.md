JViewBase
=========

Construction
------------

The contructor for JViewBase takes a mandatory JModel parameter.

Note that JModel is an interface so the actual object passed does
necessarily have to extend from JModelBase class. Given that, the view
should only rely on the API that is exposed by the interface and not
concrete classes unless the contructor is changed in a derived class to
take more explicit classes or interaces as required by the developer.

Usage
-----

The JViewBase class is abstract so cannot be used directly. It forms a
simple base for rendering any kind of data. The class already implements
the escape method so only a render method need to be added. Views
derived from this class would be used to support very simple cases, well
suited to supporting web services returning JSON, XML or possibly binary
data types. This class does not support layouts.

    /**
     * My custom view.
     *
     * @package  Examples
     *
     * @since   12.1
     */
    class MyView extends JViewBase
    {
        /**
         * Render some data
         *
         * @return  string  The rendered view.
         *
         * @since   12.1
         * @throws  RuntimeException on database error.
         */
        public function render()
        {
            // Prepare some data from the model.
            $data = array(
                'count' => $this->model->getCount()
            );

            // Convert the data to JSON format.
            return json_encode($data);
        }
    }

    try
    {
        $view = new MyView(new MyDatabaseModel);
        echo $view->render();
    }
    catch (RuntimeException $e)
    {
        // Handle database error.
    }
