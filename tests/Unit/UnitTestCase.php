<?php

/**
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

namespace Joomla\Tests\Unit;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\QueryInterface;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\Stub;

/**
 * Base Unit Test case for common behaviour across unit tests
 *
 * @since   4.0.0
 */
abstract class UnitTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * The Factory static properties which are snapshotted before, and restored after, every test.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const FACTORY_STATE_PROPERTIES = [
        'application',
        'cache',
        'config',
        'container',
        'database',
        'dates',
        'document',
        'language',
        'mailer',
        'session',
    ];

    /**
     * The values of the Factory static properties as they were before the current test started,
     * or null when no snapshot has been taken.
     *
     * @var    array|null
     * @since  __DEPLOY_VERSION__
     */
    private $factoryStateSnapshot;

    /**
     * The static properties changed through setStaticProperty(), and the values they held before.
     *
     * Each entry is [class, property, previous value], in the order the changes were made.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $staticPropertySnapshots = [];

    /**
     * Records the global Factory state before the test runs.
     *
     * This is deliberately a #[Before] hook rather than setUp(), so that it also runs for the test
     * cases which override setUp() without calling parent::setUp().
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[Before]
    protected function snapshotFactoryState(): void
    {
        $snapshot = [];

        foreach (self::FACTORY_STATE_PROPERTIES as $property) {
            $snapshot[$property] = Factory::$$property;
        }

        $this->factoryStateSnapshot = $snapshot;
    }

    /**
     * Puts the global Factory state back the way the test found it.
     *
     * This is deliberately a #[After] hook rather than tearDown(), so that it also runs for the test
     * cases which override tearDown() without calling parent::tearDown(). It is a no-op when no
     * snapshot was taken.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[After]
    protected function restoreFactoryState(): void
    {
        if ($this->factoryStateSnapshot === null) {
            return;
        }

        foreach ($this->factoryStateSnapshot as $property => $value) {
            Factory::$$property = $value;
        }

        $this->factoryStateSnapshot = null;
    }

    /**
     * Puts back the static properties which were changed through setStaticProperty().
     *
     * The changes are undone in reverse order, so that repeated changes to the same property end up
     * back at the value it held before the first one.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[After]
    protected function restoreStaticProperties(): void
    {
        foreach (array_reverse($this->staticPropertySnapshots) as [$class, $property, $value]) {
            $reflectionProperty = new \ReflectionProperty($class, $property);
            $reflectionProperty->setValue(null, $value);
        }

        $this->staticPropertySnapshots = [];
    }

    /**
     * Sets a static property of a class, remembering the previous value so that it can be put back
     * after the test.
     *
     * Use it for the static caches which the CMS does not expose a reset method for, for example
     * ComponentHelper::$components or HTMLHelper::$registry.
     *
     * @param   string  $class     The class holding the property
     * @param   string  $property  The name of the static property
     * @param   mixed   $value     The value to set
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setStaticProperty(string $class, string $property, $value): void
    {
        $reflectionProperty = new \ReflectionProperty($class, $property);

        $this->staticPropertySnapshots[] = [$class, $property, $reflectionProperty->getValue()];

        $reflectionProperty->setValue(null, $value);
    }

    /**
     * Returns a minimal dependency injection container, pre-populated with the services which CMS
     * classes most commonly resolve through Factory::getContainer().
     *
     * The container is not installed into Factory::$container, call setUpContainer() for that.
     *
     * @param   array  $services  Additional services, keyed by container key. They are merged over
     *                            the defaults, so passing an existing key replaces that default.
     *
     * @return  Container
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createContainer(array $services = []): Container
    {
        $services = array_merge(
            [
                'config'                 => new Registry(),
                DatabaseInterface::class => $this->createStub(DatabaseInterface::class),
                Language::class          => $this->createLanguageStub(),
            ],
            $services
        );

        $container = new Container();

        foreach ($services as $key => $service) {
            // Share, so that the container hands back the very same instance rather than a clone of it
            $container->share($key, $service);
        }

        return $container;
    }

    /**
     * Creates a minimal dependency injection container and installs it as the global container.
     *
     * The previous container is restored after the test by restoreFactoryState().
     *
     * @param   array  $services  Additional services, see createContainer()
     *
     * @return  Container  The container which was installed
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUpContainer(array $services = []): Container
    {
        Factory::$container = $this->createContainer($services);

        return Factory::$container;
    }

    /**
     * Returns a Language stub whose translation methods hand the language key back unchanged.
     *
     * Assertions can therefore be written against the untranslated key, which keeps them independent
     * of the contents of the language ini files.
     *
     * @param   array  $strings  Optional map of language key to translation, for the keys which the
     *                           test does care about. The keys are upper cased, as Language does.
     *
     * @return  Language&Stub
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createLanguageStub(array $strings = []): Language
    {
        $strings = array_change_key_case($strings, CASE_UPPER);

        $language = $this->createStub(Language::class);

        $language->method('_')
            ->willReturnCallback(
                static function ($string) use ($strings) {
                    return $strings[strtoupper($string)] ?? $string;
                }
            );

        $language->method('hasKey')
            ->willReturnCallback(
                static function ($string) use ($strings) {
                    return isset($strings[strtoupper($string)]);
                }
            );

        $language->method('getTag')->willReturn('en-GB');
        $language->method('getName')->willReturn('English (en-GB)');

        return $language;
    }

    /**
     * Installs a Language stub, so that the static calls to Text::_(), Text::sprintf() and friends
     * return something assertable.
     *
     * Text resolves the language through Factory::getLanguage(), which hands back Factory::$language
     * when it is set, so setting that property is enough. It is restored after the test by
     * restoreFactoryState().
     *
     * @param   array  $strings  Optional map of language key to translation, see createLanguageStub()
     *
     * @return  Language&Stub  The language which was installed
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUpLanguage(array $strings = []): Language
    {
        Factory::$language = $this->createLanguageStub($strings);

        return Factory::$language;
    }

    /**
     * Returns a database query instance.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @return  QueryInterface
     *
     * @since   4.2.0
     */
    protected function getQueryStub(DatabaseInterface $db): QueryInterface
    {
        return new class ($db) extends DatabaseQuery {
            public function groupConcat($expression, $separator = ',')
            {
            }

            public function processLimit($query, $limit, $offset = 0)
            {
            }
        };
    }
}
