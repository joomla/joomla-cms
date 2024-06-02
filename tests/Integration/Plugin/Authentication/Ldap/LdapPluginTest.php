<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Authentication
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Plugin\Authentication\Ldap;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Authentication\Ldap\Extension\Ldap as LdapPlugin;
use Joomla\Plugin\Authentication\Ldap\Factory\LdapFactory;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Test class for Ldap plugin
 *
 * Not testing for:
 * * different certificate options
 *   these can't be properly automatically tested as the LDAP_OPT_X_ settings can only be set once in a running process
 * * working ldap debug option.
 *   this can only be tested if phpunit stderr is redirected/duplicated/configured to a file
 *
 * @package     Joomla.IntegrationTest
 * @subpackage  Ldap
 *
 * @testdox     The Ldap plugin
 *
 * @since       4.3.0
 */
class LdapPluginTest extends IntegrationTestCase
{
    public const LDAPPORT = JTEST_LDAP_PORT;
    public const SSLPORT  = JTEST_LDAP_PORT_SSL;

    /**
     * The default options
     *
     * @var    array
     * @since  4.3.0
     */
    private $default_options;

    /**
     * The default credentials
     *
     * @var    array
     * @since  4.3.0
     */
    private $default_credentials;

    private function getPlugin($options): LdapPlugin
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        // plugin object: result from DB using PluginHelper::getPlugin
        $pluginObject = [
            'name'   => 'ldap',
            'params' => json_encode($options),
            'type'   => 'authentication',
        ];

        $plugin = new LdapPlugin(new LdapFactory(), new Dispatcher(), $pluginObject);
        $plugin->setApplication($app);

        return $plugin;
    }

    private function skipIfAskedFor($options): void
    {
        if (empty($options["host"])) {
            $this->markTestSkipped("No LDAP host provided, skipping test against LDAP server.");
        }
    }

    /**
     * Setup
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function setUp(): void
    {
        // tests are executed in parallel as root
        // setUp is executed before every test
        $this->default_options = [
            /* fixed options for all tests */
            'host'          => JTEST_LDAP_HOST,
            'use_ldapV3'    => JTEST_LDAP_USEV3,
            'no_referrals'  => JTEST_LDAP_NOREFERRALS,
            'base_dn'       => JTEST_LDAP_BASE,
            'search_string' => JTEST_LDAP_SEARCH,
            'users_dn'      => JTEST_LDAP_DIRECT_USERDN,
            'username'      => JTEST_LDAP_SEARCH_DN,
            'password'      => JTEST_LDAP_SEARCH_PASSWORD,
            'ldap_fullname' => JTEST_LDAP_FULLNAME,
            'ldap_email'    => JTEST_LDAP_EMAIL,
            'ldap_uid'      => JTEST_LDAP_UID,
            'ldap_debug'    => 0,
            /* the security options can only be set once, these are the best practice settings */
            'ignore_reqcert_tls' => 0,
            'cacert'             => JPATH_ROOT . '/' . JTEST_LDAP_CACERTFILE,
            /* changing options to test all code */
            'port'        => self::LDAPPORT,
            'encryption'  => "none",
            'auth_method' => "bind",
        ];

        $this->default_credentials = [
            'username'  => JTEST_LDAP_TESTUSER,
            'password'  => JTEST_LDAP_TESTPASSWORD,
            'secretkey' => null,
        ];
    }

    /**
     * Cleanup
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function tearDown(): void
    {
    }

    /**
     * @testdox  can perform an authentication using bind and search
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateBindAndSearch()
    {
        $options                = $this->default_options;
        $options["auth_method"] = "search";
        $this->skipIfAskedFor($options);
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $event    = new AuthenticationEvent('onUserAuthenticate', ['credentials' => $this->default_credentials, 'options' => [], 'subject' => $response]);
        $plugin->onUserAuthenticate($event);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication using direct bind
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateDirect()
    {
        $options                = $this->default_options;
        $options["auth_method"] = "bind";
        $this->skipIfAskedFor($options);
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $event    = new AuthenticationEvent('onUserAuthenticate', ['credentials' => $this->default_credentials, 'options' => [], 'subject' => $response]);
        $plugin->onUserAuthenticate($event);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication using direct bind with bad credentials
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testInvalidOnUserAuthenticateDirect()
    {
        $options                = $this->default_options;
        $options["auth_method"] = "bind";
        // this one should have the same result with or without LDAP server running
        $plugin = $this->getPlugin($options);

        $credentials             = $this->default_credentials;
        $credentials['password'] = "arandomverywrongpassword_à!joqf";

        $response = new AuthenticationResponse();
        $event    = new AuthenticationEvent('onUserAuthenticate', ['credentials' => $credentials, 'options' => [], 'subject' => $response]);
        $plugin->onUserAuthenticate($event);
        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform an authentication on STARTTLS encrypted connection (using bind and search)
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateBindAndSearchTLS()
    {
        $options                = $this->default_options;
        $options["auth_method"] = "search";
        $options["encryption"]  = "tls";
        $this->skipIfAskedFor($options);
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $event    = new AuthenticationEvent('onUserAuthenticate', ['credentials' => $this->default_credentials, 'options' => [], 'subject' => $response]);
        $plugin->onUserAuthenticate($event);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication on SSL/TLS encrypted connection (using bind and search)
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateBindAndSearchSSL()
    {
        $options                = $this->default_options;
        $options["auth_method"] = "search";
        $options["encryption"]  = "ssl";
        $options["port"]        = self::SSLPORT;
        $this->skipIfAskedFor($options);
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $event    = new AuthenticationEvent('onUserAuthenticate', ['credentials' => $this->default_credentials, 'options' => [], 'subject' => $response]);
        $plugin->onUserAuthenticate($event);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }
}
