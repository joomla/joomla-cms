<?php

namespace Joomla\CMS\MVC\Factory;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\Input\Input;

class MVCFactoryWrapper implements MVCFactoryInterface
{
    use MVCFactoryAwareTrait;

    /**
     * Public constructor. Wraps an existing MVCFactory so it can be extended.
     *
     * @param   MVCFactoryInterface  $factory
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(MVCFactoryInterface $factory)
    {
        $this->setMVCFactory($factory);
    }

    /**
     * Method to load and return a controller object.
     *
     * @param   string                   $name    The name of the controller
     * @param   string                   $prefix  The controller prefix
     * @param   array                    $config  The configuration array for the controller
     * @param   CMSApplicationInterface  $app     The app
     * @param   Input                    $input   The input
     *
     * @return  \Joomla\CMS\MVC\Controller\ControllerInterface
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function createController($name, $prefix, array $config, CMSApplicationInterface $app, Input $input)
    {
        $controller = $this->getMVCFactory()->createController($name, $prefix, $config, $app, $input);

        $controller->setMVCFactory($this);

        return $controller;
    }

    /**
     * Method to load and return a model object.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  Optional model prefix.
     * @param   array   $config  Optional configuration array for the model.
     *
     * @return  ModelInterface  The model object
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function createModel($name, $prefix = '', array $config = [])
    {
        $model = $this->getMVCFactory()->createModel($name, $prefix, $config);

        if (method_exists($model, 'setMVCFactory')) {
            $model->setMVCFactory($this);
        }

        return $model;
    }

    /**
     * Method to load and return a view object.
     *
     * @param   string  $name    The name of the view.
     * @param   string  $prefix  Optional view prefix.
     * @param   string  $type    Optional type of view.
     * @param   array   $config  Optional configuration array for the view.
     *
     * @return  \Joomla\CMS\MVC\View\ViewInterface  The view object
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function createView($name, $prefix = '', $type = '', array $config = [])
    {
        return $this->getMVCFactory()->createView($name, $prefix, $type, $config);
    }

    /**
     * Method to load and return a table object.
     *
     * @param   string  $name    The name of the table.
     * @param   string  $prefix  Optional table prefix.
     * @param   array   $config  Optional configuration array for the table.
     *
     * @return  \Joomla\CMS\Table\Table  The table object
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function createTable($name, $prefix = '', array $config = [])
    {
        return $this->getMVCFactory()->createTable($name, $prefix, $config);
    }
}
