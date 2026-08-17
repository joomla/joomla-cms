<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\LanguageFactoryAwareInterface;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryAwareInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Controller\ControllerInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Router\SiteRouterAwareInterface;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\AbstractAutowireInterface;
use Joomla\DI\ConstructorAutowireInterface;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory to create MVC objects based on a namespace and with autowiring.
 */
final class AutowireFactory implements MVCFactoryInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The namespace to create the objects from.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $namespace;

    /**
     * The namespace must be like:
     * Joomla\Component\Content
     *
     * @param   Container  $container  The container
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container);
        $this->namespace = $container->get('scalar.namespace');
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
     * @return  \Joomla\CMS\MVC\Controller\ControllerInterface|null
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function createController($name, $prefix, array $config, CMSApplicationInterface $app, Input $input): ControllerInterface
    {
        // Clean the parameters
        $name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
        $prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

        $className = $this->getClassName('Controller\\' . ucfirst($name) . 'Controller', $prefix);

        if (!$className) {
            throw new \InvalidArgumentException('Class ' . $name . ' with prefix ' . $prefix . ' not found.');
        }

        $reflection = new \ReflectionClass($className);

        $classInterfaces = $reflection->getInterfaceNames();

        if (array_intersect([ConstructorAutowireInterface::class, AbstractAutowireInterface::class], $classInterfaces)) {

            $localContainer = $this->getContainer()->createChild();
            $localContainer->set('scalar.config', $config);
            $localContainer->set(CMSWebApplicationInterface::class, $app);
            $localContainer->set(Input::class, $input);

            /** @var ControllerInterface */
            return $localContainer->buildObject($className);
        }

        // todo This is a b/c fallback if we load objects not prepared for autowiring

        $controller = new $className($config, $this, $app, $input);
        $this->setFormFactoryOnObject($controller);
        $this->setDispatcherOnObject($controller);
        $this->setRouterOnObject($controller);
        $this->setCacheControllerOnObject($controller);
        $this->setUserFactoryOnObject($controller);
        $this->setMailerFactoryOnObject($controller);
        $this->setLanguageFactoryOnObject($controller);
        $this->setLoggerOnObject($controller);

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
    public function createModel($name, $prefix = 'Administrator', array $config = []): ModelInterface
    {
        // Clean the parameters
        $name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
        $prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

        if (!$prefix) {
            throw new \InvalidArgumentException('Prefix must be set for ' . __METHOD__ . '()');
        }

        $className = $this->getClassName('Model\\' . ucfirst($name) . 'Model', $prefix);

        if (!$className) {
            throw new \InvalidArgumentException('Class ' . $name . ' with prefix ' . $prefix . ' not found.');
        }

        $reflection = new \ReflectionClass($className);

        $classInterfaces = $reflection->getInterfaceNames();

        if (array_intersect([ConstructorAutowireInterface::class, AbstractAutowireInterface::class], $classInterfaces)) {
            $localContainer = $this->getContainer()->createChild();
            $localContainer->set('scalar.config', $config);

            /** @var ModelInterface */
            return $localContainer->buildObject($className);
        }

        // todo This is a b/c fallback if we load objects not prepared for autowiring

        $model = new $className($config, $this);
        $this->setFormFactoryOnObject($model);
        $this->setDispatcherOnObject($model);
        $this->setRouterOnObject($model);
        $this->setCacheControllerOnObject($model);
        $this->setUserFactoryOnObject($model);
        $this->setMailerFactoryOnObject($model);
        $this->setLanguageFactoryOnObject($model);
        $this->setDatabaseOnObject($model);

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
     * @since   3.10.0
     * @throws  \Exception
     */
    public function createView($name, $prefix = 'Administrator', $type = '', array $config = []): ViewInterface
    {
        // Clean the parameters
        $name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
        $prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
        $type   = preg_replace('/[^A-Z0-9_]/i', '', $type);

        if (!$prefix) {
            throw new \InvalidArgumentException('Prefix must be set for ' . __METHOD__ . '()');
        }

        $className = $this->getClassName('View\\' . ucfirst($name) . '\\' . ucfirst($type) . 'View', $prefix);

        if (!$className) {
            throw new \InvalidArgumentException('Class ' . $name . ' with prefix ' . $prefix . ' not found.');
        }

        $reflection = new \ReflectionClass($className);

        $classInterfaces = $reflection->getInterfaceNames();

        if (array_intersect([ConstructorAutowireInterface::class, AbstractAutowireInterface::class], $classInterfaces)) {
            $localContainer = $this->getContainer()->createChild();
            $localContainer->set('scalar.config', $config);

            /** @var ViewInterface */
            return $localContainer->buildObject($className);
        }

        $view = new $className($config);
        $this->setFormFactoryOnObject($view);
        $this->setDispatcherOnObject($view);
        $this->setRouterOnObject($view);
        $this->setCacheControllerOnObject($view);
        $this->setUserFactoryOnObject($view);

        return $view;
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
    public function createTable($name, $prefix = 'Administrator', array $config = []): TableInterface
    {
        // Clean the parameters
        $name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
        $prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

        if (!$prefix) {
            // throw new \InvalidArgumentException('Prefix must be set for ' . __METHOD__ . '()');
            // todo Joomla MVC it self never sets the prefix, this needs to be fixed
            $prefix = 'Administrator';
        }

        // todo discuss if prefix for a table needs to be configurable, per convention this is always Administrator
        $className = $this->getClassName('Table\\' . ucfirst($name) . 'Table', 'Administrator');

        if (!$className) {
            throw new \InvalidArgumentException('Class ' . $name . ' with prefix ' . $prefix . ' not found.');
        }

        $localContainer = $this->getContainer()->createChild();

        if (\array_key_exists('dbo', $config)) {
            // DatabaseInterface::class is a shared object and needs to stay a shared object
            $localContainer->set(DatabaseInterface::class, $config['dbo'], true);
        }

        $reflection = new \ReflectionClass($className);

        $classInterfaces = $reflection->getInterfaceNames();

        if (array_intersect([ConstructorAutowireInterface::class, AbstractAutowireInterface::class], $classInterfaces)) {
            /** @var TableInterface */
            return $localContainer->buildObject($className);
        }

        $table = new $className($localContainer->get(DatabaseInterface::class));

        // todo This is a b/c fallback since tables may need a user factory
        $this->setUserFactoryOnObject($table);

        return $table;
    }

    /**
     * Returns a standard classname, if the class doesn't exist null is returned.
     *
     * @param   string  $suffix  The suffix
     * @param   string  $prefix  The prefix
     *
     * @return  string|null  The class name
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getClassName(string $suffix, string $prefix)
    {
        $className = trim($this->namespace, '\\') . '\\' . ucfirst($prefix) . '\\' . $suffix;

        if (!class_exists($className)) {
            return null;
        }

        return $className;
    }


    /***** DEPRECATED CODE MIGHT BE REMOVED SOON *****/


    /**
     * Sets the database object on the given object.
     */
    private function setLoggerOnObject(object $object): void
    {
        if (!$object instanceof LoggerAwareInterface) {
            return;
        }

        try {
            $object->setLogger($this->getContainer()->get(LoggerInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the database object on the given object.
     */
    private function setDatabaseOnObject(object $object): void
    {
        if (!$object instanceof DatabaseAwareInterface) {
            return;
        }

        try {
            $object->setDatabase($this->getContainer()->get(DatabaseInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal form factory on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setFormFactoryOnObject(object $object): void
    {
        if (!$object instanceof FormFactoryAwareInterface) {
            return;
        }

        try {
            $object->setFormFactory($this->getContainer()->get(FormFactoryInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal event dispatcher on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setDispatcherOnObject(object $object): void
    {
        if (!$object instanceof DispatcherAwareInterface) {
            return;
        }

        try {
            $object->setDispatcher($this->getContainer()->get(DispatcherInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal router on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setRouterOnObject(object $object): void
    {
        if (!$object instanceof SiteRouterAwareInterface) {
            return;
        }

        try {
            $object->setSiteRouter($this->getContainer()->get(SiteRouter::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal cache controller on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setCacheControllerOnObject(object $object): void
    {
        if (!$object instanceof CacheControllerFactoryAwareInterface) {
            return;
        }

        try {
            $object->setCacheControllerFactory($this->getContainer()->get(CacheControllerFactoryInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal user factory on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setUserFactoryOnObject(object $object): void
    {
        if (!$object instanceof UserFactoryAwareInterface) {
            return;
        }

        try {
            $object->setUserFactory($this->getContainer()->get(UserFactoryInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal mailer factory on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setMailerFactoryOnObject(object $object): void
    {
        if (!$object instanceof MailerFactoryAwareInterface) {
            return;
        }

        try {
            $object->setMailerFactory($this->getContainer()->get(MailerFactoryInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }

    /**
     * Sets the internal mailer factory on the given object.
     *
     * @param   object  $object  The object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setLanguageFactoryOnObject($object): void
    {
        if (!$object instanceof LanguageFactoryAwareInterface) {
            return;
        }

        try {
            $object->setLanguageFactory($this->getContainer()->get(LanguageFactoryInterface::class));
        } catch (\UnexpectedValueException) {
            // Ignore it
        }
    }
}
