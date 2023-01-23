<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Authentication
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Authentication\Ldap;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Authentication\Ldap\Extension\Ldap as LdapPlugin;
use Joomla\Tests\Unit\UnitTestCase;
use Symfony\Component\Ldap\Ldap;

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
    public const LDAPPORT = "1389";
    public const SSLPORT = "1636";

    /**
     * The default options
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $default_options;

    /**
     * The default credentials
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $default_credentials;

    private function getPlugin($options): LdapPlugin
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $dispatcher = new Dispatcher();

        // plugin object: result from DB using PluginHelper::getPlugin
        $pluginObject = [
            'name'   => 'ldap',
            'params' => json_encode($options),
            'type'   => 'authentication'
        ];

        $plugin = new LdapPlugin($dispatcher, $pluginObject);
        $plugin->setApplication($app);

        return $plugin;
    }

    private function acceptCertificates(): void
    {
        ldap_set_option(null, LDAP_OPT_X_TLS_CACERTDIR, JPATH_ROOT . '/tests/Codeception/_data/certs');
        ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, JPATH_ROOT . '/tests/Codeception/_data/certs/CA.crt');
    }

    private function getAdminConnection(array $options): Ldap
    {
        $admin_options = [
            'host' => $options['host'],
            'port' => (int) $options['port'],
            'version' => $options['use_ldapV3'] == '1' ? 3 : 2,
            'referrals'  => (bool) $options['no_referrals'],
            'encryption' => $options['encryption'],
            'debug' => (bool) $options['ldap_debug'],
        ];
        $ldap = Ldap::create(
            'ext_ldap',
            $admin_options
        );
        $ldap->bind("cn=admin,cn=config", "configpassword");
        return $ldap;
    }

    private function requireEncryption($encryption, $options): void
    {
        $ldap = $this->getAdminConnection($options);
        //TODO configure openldap to require the requested encryption
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
            'host' => "openldap",
            'port' => self::LDAPPORT,
            'use_ldapV3' => 1,
            'encryption' => "none",
            'no_referrals' => 0,
            'auth_method' => "bind",
            'base_dn' => "dc=example,dc=org",
            'search_string' => "uid=[search]",
            'users_dn' => "cn=[username],ou=users,dc=example,dc=org",
            'username' => "",
            'password' => "",
            'ldap_fullname' => "cn",
            'ldap_email' => "mail",
            'ldap_uid' => "uid",
            'ldap_debug' => 0
        ];

        $this->default_credentials = [
            'username' => "customuser",
            'password' => "custompassword",
            'secretkey' => null
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
     * @testdox  can perform an authentication using anonymous search
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateAnonymousSearch()
    {
        $options = $this->default_options;
        $options["auth_method"] = "search";
        $options["users_dn"] = "";
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($this->default_credentials, [], $response);
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
        $this->markTestSkipped("Fix provided in PR #37959");

        $plugin = $this->getPlugin($this->default_options);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($this->default_credentials, [], $response);
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
        $plugin = $this->getPlugin($this->default_options);
        $credentials = $this->default_credentials;
        $credentials['password'] = "wrongpassword";

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($credentials, [], $response);
        $this->assertEquals(Authentication::STATUS_FAILURE, $response->status);
    }

    /**
     * @testdox  can perform an authentication using anonymous search
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateAnonymousSearchTLS()
    {
        $options = $this->default_options;
        $options["auth_method"] = "search";
        $options["users_dn"] = "";
        $options["encryption"] = "tls";
        $plugin = $this->getPlugin($options);

        $this->acceptCertificates();
        $this->requireEncryption("tls", $options);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($this->default_credentials, [], $response);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  can perform an authentication using anonymous search
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testOnUserAuthenticateAnonymousSearchSSL()
    {
        $options = $this->default_options;
        $options["auth_method"] = "search";
        $options["users_dn"] = "";
        $options["encryption"] = "ssl";
        $options["port"] = self::SSLPORT;
        $plugin = $this->getPlugin($options);

        $this->acceptCertificates();
        $this->requireEncryption("ssl", $options);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($this->default_credentials, [], $response);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }

    /**
     * @testdox  does log ldap client calls and errors
     * can only be tested if phpunit stderr is redirected/duplicated/configured to a file
     * then, we can check if ldap_ calls are present in that file
     *
     * @return  void
     *
     * @since   4.3.0
     */
    /*
    public function testOnUserAuthenticateWithDebug()
    {
        $options = $this->default_options;
        $options["ldap_debug"] = 1;
        $plugin = $this->getPlugin($options);

        $response = new AuthenticationResponse();
        $plugin->onUserAuthenticate($this->default_credentials, [], $response);
        $this->assertEquals(Authentication::STATUS_SUCCESS, $response->status);
    }
    */
}
