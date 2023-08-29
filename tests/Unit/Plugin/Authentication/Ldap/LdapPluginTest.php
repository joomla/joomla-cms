<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Authentication
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Authentication\Ldap;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Authentication\Ldap\Extension\Ldap;
use Joomla\Plugin\Authentication\Ldap\Factory\LdapFactoryInterface;
use Joomla\Tests\Unit\UnitTestCase;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\LdapInterface;

/**
 * Test class for Ldap plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Ldap
 *
 * @testdox     The Ldap plugin
 *
 * @since       4.3.0
 */
class LdapPluginTest extends UnitTestCase
{
    /**
     * @testdox  when no host is set
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testNoHost()
    {
        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => []]);
        $plugin->setApplication($this->createStub(CMSApplicationInterface::class));

        $response = new AuthenticationResponse();
        $result   = $plugin->onUserAuthenticate([], [], $response);

        $this->assertFalse($result);
        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  when no credentials are set
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testNoCredentials()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => ['host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $result   = $plugin->onUserAuthenticate(['password' => ''], [], $response);

        $this->assertFalse($result);
        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform no authentication
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testNoAuthenticationMethod()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => ['host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform an authentication using search
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSearchAuthenticationMethod()
    {
        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => ['auth_method' => 'search', 'host' => 'test']]);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication using search when no entry is found
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSearchAuthenticationMethodNoEntry()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(false, false, false), new Dispatcher(), ['params' => ['auth_method' => 'search', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can not perform an authentication using search when bind fails
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSearchAuthenticationMethodWithBindException()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(true, false), new Dispatcher(), ['params' => ['auth_method' => 'search', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can not perform an authentication using search when query fails
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSearchAuthenticationMethodWithQueryException()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(false, true), new Dispatcher(), ['params' => ['auth_method' => 'search', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform an authentication using bind
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testBindAuthenticationMethod()
    {
        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => ['auth_method' => 'bind', 'host' => 'test']]);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication using bind when no entry is found
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testBindAuthenticationMethodNoEntry()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(false, false, false), new Dispatcher(), ['params' => ['auth_method' => 'bind', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform an authentication using bind with a DN
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testBindAuthenticationMethodWithDN()
    {
        $plugin = new Ldap($this->createFactory(), new Dispatcher(), ['params' => ['auth_method' => 'bind', 'users_dn' => 'test', 'host' => 'test']]);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can not perform an authentication using bind when bind fails
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testBindAuthenticationMethodWithBindException()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(true, false), new Dispatcher(), ['params' => ['auth_method' => 'bind', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can not perform an authentication using bind when query fails
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testBindAuthenticationMethodWithQueryException()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new Ldap($this->createFactory(false, true), new Dispatcher(), ['params' => ['auth_method' => 'bind', 'host' => 'test']]);
        $plugin->setApplication($app);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate(['username' => 'unit', 'password' => 'test'], [], $response);

        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * Creates a dummy Ldap factory.
     *
     * @return  LdapFactoryInterface
     *
     * @since   4.3.0
     */
    private function createFactory(bool $failBind = false, bool $failQuery = false, bool $hasEntry = true): LdapFactoryInterface
    {
        return new class ($failBind, $failQuery, $hasEntry) implements LdapFactoryInterface {
            private $failBind  = false;
            private $failQuery = false;
            private $hasEntry  = false;

            public function __construct(bool $failBind, bool $failQuery, bool $hasEntry)
            {
                $this->failBind  = $failBind;
                $this->failQuery = $failQuery;
                $this->hasEntry  = $hasEntry;
            }

            public function createLdap(array $config): LdapInterface
            {
                return new class ($this->failBind, $this->failQuery, $this->hasEntry) implements LdapInterface {
                    private $failBind  = false;
                    private $failQuery = false;
                    private $hasEntry  = false;

                    public function __construct(bool $failBind, bool $failQuery, bool $hasEntry)
                    {
                        $this->failBind  = $failBind;
                        $this->failQuery = $failQuery;
                        $this->hasEntry  = $hasEntry;
                    }

                    public function bind(string $dn = null, string $password = null)
                    {
                        if ($this->failBind) {
                            throw new LdapException();
                        }
                    }

                    public function query(string $dn, string $query, array $options = [])
                    {
                        if ($this->failQuery) {
                            throw new LdapException();
                        }

                        return new class ($this->hasEntry) implements QueryInterface {
                            private $hasEntry = false;

                            public function __construct(bool $hasEntry)
                            {
                                $this->hasEntry = $hasEntry;
                            }

                            public function execute()
                            {
                                if (!$this->hasEntry) {
                                    return [];
                                }

                                return [new Entry('')];
                            }
                        };
                    }

                    public function getEntryManager()
                    {
                    }

                    public function escape(string $subject, string $ignore = '', int $flags = 0)
                    {
                        return $subject;
                    }
                };
            }
        };
    }
}
