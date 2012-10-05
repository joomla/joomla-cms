JViewHtml
=========

Construction
------------

JViewHtml is extended from JViewBase. The constructor, in addition to
the required model argument, take an optional SplPriorityQueue object
that serves as a lookup for layouts. If omitted, the view defers to the
protected loadPaths method.

Usage
-----

The JViewHtml class is abstract so cannot be used directly. This view
class implements render. It will try to find the layout, include it
using output buffering and return the result. The following examples
show a layout file that is assumed to be stored in a generic layout
folder not stored under the web-server root.

    <?php
    /**
     * Example layout "layouts/count.php".
     *
     * @package  Examples
     * @since    12.1
     */

    // Declare variables to support type hinting.

    /** @var $this MyHtmlView */
    ?>

    <dl>
        <dt>Count</dt>
        <dd><?php echo $this->model->getCount(); ?></dd>
    </dl>

    /**
     * My custom HTML view.
     *
     * @package  Examples
     * @since    12.1
     */
    class MyHtmlView extends JViewHtml
    {
        /**
         * Redefine the model so the correct type hinting is available in the layout.
         *
         * @var     MyDatabaseModel
         * @since   12.1
         */
        protected $model;
    }

    try
    {
        $paths = new SplPriorityQueue;
        $paths->insert(__DIR__ . '/layouts');

        $view = new MyView(new MyDatabaseModel, $paths);
        $view->setLayout('count');
        echo $view->render();

        // Alternative approach.
        $view = new MyView(new MyDatabaseModel);

        // Use some chaining.
        $view->setPaths($paths)
            ->setLayout('count');

        // Take advantage of the magic __toString method.
        echo $view;
    }
    catch (RuntimeException $e)
    {
        // Handle database error.
    }
