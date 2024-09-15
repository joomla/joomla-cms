<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Authentication\Ldap\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Authentication\Ldap\Factory\LdapFactoryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\LdapInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * LDAP Authentication Plugin
 *
 * @since  1.5
 */
final class Ldap extends CMSPlugin implements SubscriberInterface
{
    /**
     * The ldap factory
     *
     * @var    LdapFactoryInterface
     * @since  4.3.0
     */
    private $factory;

    /**
     * Constructor
     *
     * @param   LdapFactoryInterface  $factory     The Ldap factory
     * @param   DispatcherInterface   $dispatcher  The object to observe
     * @param   array                 $config      An optional associative array of configuration settings.
     *                                             Recognized key values include 'name', 'group', 'params', 'language'
     *                                             (this list is not meant to be comprehensive).
     *
     * @since   4.3.0
     */
    public function __construct(LdapFactoryInterface $factory, DispatcherInterface $dispatcher, array $config = [])
    {
        parent::__construct($dispatcher, $config);

        $this->factory = $factory;
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onUserAuthenticate' => 'onUserAuthenticate'];
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   AuthenticationEvent  $event    Authentication event
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserAuthenticate(AuthenticationEvent $event): void
    {
        // If LDAP not correctly configured then bail early.
        if (!$this->params->get('host', '')) {
            return;
        }

        $credentials = $event->getCredentials();
        $response    = $event->getAuthenticationResponse();

        // For JLog
        $logcategory    = 'ldap';
        $response->type = $logcategory;

        // Strip null bytes from the password
        $credentials['password'] = str_replace(\chr(0), '', $credentials['password']);

        // LDAP does not like Blank passwords (tries to Anon Bind which is bad)
        if (empty($credentials['password'])) {
            $response->status        = Authentication::STATUS_FAILURE;
            $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

            return;
        }

        // Load plugin params info
        $ldap_email    = $this->params->get('ldap_email', '');
        $ldap_fullname = $this->params->get('ldap_fullname', '');
        $ldap_uid      = $this->params->get('ldap_uid', '');
        $auth_method   = $this->params->get('auth_method', '');
        // Load certificate info
        $ignore_reqcert_tls = (bool) $this->params->get('ignore_reqcert_tls', '1');
        $cacert             = $this->params->get('cacert', '');

        // getting certificate file and certificate directory options (both need to be set)
        if (!$ignore_reqcert_tls && !empty($cacert)) {
            if (is_dir($cacert)) {
                $cacertdir  = $cacert;
                $cacertfile = "";
            } elseif (is_file($cacert)) {
                $cacertfile = $cacert;
                $cacertdir  = \dirname($cacert);
            } else {
                $cacertfile = $cacert;
                $cacertdir  = $cacert;
                Log::add(\sprintf('Certificate path for LDAP client is neither an existing file nor directory: "%s"', $cacert), Log::ERROR, $logcategory);
            }
        } else {
            Log::add(\sprintf('Not setting any LDAP TLS CA certificate options because %s, system wide settings are used', $ignore_reqcert_tls ? "certificate is ignored" : "no certificate location is configured"), Log::DEBUG, $logcategory);
        }

        $options = [
            'host'       => $this->params->get('host', ''),
            'port'       => (int) $this->params->get('port', ''),
            'version'    => $this->params->get('use_ldapV3', '1') == '1' ? 3 : 2,
            'referrals'  => (bool) $this->params->get('no_referrals', '0'),
            'encryption' => $this->params->get('encryption', 'none'),
            'debug'      => (bool) $this->params->get('ldap_debug', '0'),
            'options'    => [
                'x_tls_require_cert' => $ignore_reqcert_tls ? LDAP_OPT_X_TLS_NEVER : LDAP_OPT_X_TLS_DEMAND,
            ],
        ];
        // if these are not set, the system defaults are used
        if (isset($cacertdir) && isset($cacertfile)) {
            $options['options']['x_tls_cacertdir']  = $cacertdir;
            $options['options']['x_tls_cacertfile'] = $cacertfile;
        }

        Log::add(\sprintf('Creating LDAP session with options: %s', json_encode($options)), Log::DEBUG, $logcategory);
        $connection_string = \sprintf('ldap%s://%s:%s', 'ssl' === $options['encryption'] ? 's' : '', $options['host'], $options['port']);
        Log::add(\sprintf('Creating LDAP session to connect to "%s" while binding', $connection_string), Log::DEBUG, $logcategory);
        $ldap = $this->factory->createLdap($options);

        switch ($auth_method) {
            case 'search':
                try {
                    $dn = $this->params->get('username', '');
                    Log::add(\sprintf('Binding to LDAP server with administrative dn "%s" and given administrative password (anonymous if user dn is blank)', $dn), Log::DEBUG, $logcategory);
                    $ldap->bind($dn, $this->params->get('password', ''));
                } catch (ConnectionException | LdapException $exception) {
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_NOT_CONNECT');
                    Log::add($exception->getMessage(), Log::ERROR, $logcategory);

                    return;
                }

                // Search for users DN
                try {
                    $searchstring = str_replace(
                        '[search]',
                        str_replace(';', '\3b', $ldap->escape($credentials['username'], '', LDAP_ESCAPE_FILTER)),
                        $this->params->get('search_string', '')
                    );
                    Log::add(\sprintf('Searching LDAP entry with filter: "%s"', $searchstring), Log::DEBUG, $logcategory);
                    $entry = $this->searchByString($searchstring, $ldap);
                } catch (LdapException $exception) {
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
                    Log::add($exception->getMessage(), Log::ERROR, $logcategory);

                    return;
                }

                if (!$entry) {
                    // we did not find the login in LDAP
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_NO_USER');
                    Log::add($this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_USER_NOT_FOUND'), Log::ERROR, $logcategory);

                    return;
                }

                Log::add(\sprintf('LDAP entry found at "%s"', $entry->getDn()), Log::DEBUG, $logcategory);

                try {
                    // Verify Users Credentials
                    Log::add(\sprintf('Binding to LDAP server with found user dn "%s" and user entered password', $entry->getDn()), Log::DEBUG, $logcategory);
                    $ldap->bind($entry->getDn(), $credentials['password']);
                } catch (ConnectionException $exception) {
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_INVALID_PASS');
                    Log::add($exception->getMessage(), Log::ERROR, $logcategory);

                    return;
                }

                break;

            case 'bind':
                // We just accept the result here
                try {
                    if ($this->params->get('users_dn', '') == '') {
                        $dn = $credentials['username'];
                    } else {
                        $dn = str_replace(
                            '[username]',
                            $ldap->escape($credentials['username'], '', LDAP_ESCAPE_DN),
                            $this->params->get('users_dn', '')
                        );
                    }

                    Log::add(\sprintf('Direct binding to LDAP server with entered user dn "%s" and user entered password', $dn), Log::DEBUG, $logcategory);
                    $ldap->bind($dn, $credentials['password']);
                } catch (ConnectionException | LdapException $exception) {
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_INVALID_PASS');
                    Log::add($exception->getMessage(), Log::ERROR, $logcategory);

                    return;
                }

                try {
                    $searchstring = str_replace(
                        '[search]',
                        str_replace(';', '\3b', $ldap->escape($credentials['username'], '', LDAP_ESCAPE_FILTER)),
                        $this->params->get('search_string', '')
                    );
                    Log::add(\sprintf('Searching LDAP entry with filter: "%s"', $searchstring), Log::DEBUG, $logcategory);
                    $entry = $this->searchByString($searchstring, $ldap);
                } catch (LdapException $exception) {
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
                    Log::add($exception->getMessage(), Log::ERROR, $logcategory);

                    return;
                }

                if (!$entry) {
                    // we did not find the login in LDAP
                    $response->status        = Authentication::STATUS_FAILURE;
                    $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_NO_USER');
                    Log::add($this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_USER_NOT_FOUND'), Log::ERROR, $logcategory);

                    return;
                }

                Log::add(\sprintf('LDAP entry found at "%s"', $entry->getDn()), Log::DEBUG, $logcategory);

                break;

            default:
                // Unsupported configuration
                $response->status        = Authentication::STATUS_FAILURE;
                $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
                Log::add($response->error_message, Log::ERROR, $logcategory);

                return;
        }

        // Grab some details from LDAP and return them
        $response->username = $entry->getAttribute($ldap_uid)[0] ?? false;
        $response->email    = $entry->getAttribute($ldap_email)[0] ?? false;
        $response->fullname = $entry->getAttribute($ldap_fullname)[0] ?? $credentials['username'];

        // Were good - So say so.
        Log::add(\sprintf('LDAP login succeeded; username: "%s", email: "%s", fullname: "%s"', $response->username, $response->email, $response->fullname), Log::DEBUG, $logcategory);
        $response->status        = Authentication::STATUS_SUCCESS;
        $response->error_message = '';

        // Stop event propagation when status is STATUS_SUCCESS
        $event->stopPropagation();

        // The connection is no longer needed, destroy the object to close it
        unset($ldap);
    }

    /**
     * Shortcut method to perform a LDAP search based on a semicolon separated string
     *
     * Note that this method requires that semicolons which should be part of the search term to be escaped
     * to correctly split the search string into separate lookups
     *
     * @param   string         $search  search string of search values
     * @param   LdapInterface  $ldap    The LDAP client
     *
     * @return  Entry|null The search result entry if a matching record was found
     *
     * @since   3.8.2
     */
    private function searchByString(string $search, LdapInterface $ldap)
    {
        $dn = $this->params->get('base_dn', '');

        // We return the first entry from the first search result which contains data
        foreach (explode(';', $search) as $key => $result) {
            $results = $ldap->query($dn, '(' . str_replace('\3b', ';', $result) . ')')->execute();

            if (\count($results)) {
                return $results[0];
            }
        }
    }
}
