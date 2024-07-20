<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.httpheaders
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Httpheaders\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin class for HTTP Headers
 *
 * @since  4.0.0
 */
final class Httpheaders extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The generated csp nonce value
     *
     * @var    string
     * @since  4.0.0
     */
    private $cspNonce;

    /**
     * The list of the supported HTTP headers
     *
     * @var    array
     * @since  4.0.0
     */
    private $supportedHttpHeaders = [
        'strict-transport-security',
        'content-security-policy',
        'content-security-policy-report-only',
        'x-frame-options',
        'referrer-policy',
        'expect-ct',
        'feature-policy',
        'cross-origin-opener-policy',
        'report-to',
        'permissions-policy',
        'nel',
    ];

    /**
     * The list of valid directives based on: https://www.w3.org/TR/CSP3/#csp-directives
     *
     * @var    array
     * @since  4.0.0
     */
    private $validDirectives = [
        'child-src',
        'connect-src',
        'default-src',
        'font-src',
        'frame-src',
        'img-src',
        'manifest-src',
        'media-src',
        'prefetch-src',
        'object-src',
        'script-src',
        'script-src-elem',
        'script-src-attr',
        'style-src',
        'style-src-elem',
        'style-src-attr',
        'worker-src',
        'base-uri',
        'plugin-types',
        'sandbox',
        'form-action',
        'frame-ancestors',
        'navigate-to',
        'report-uri',
        'report-to',
        'block-all-mixed-content',
        'upgrade-insecure-requests',
        'require-sri-for',
    ];

    /**
     * The list of directives without a value
     *
     * @var    array
     * @since  4.0.0
     */
    private $noValueDirectives = [
        'block-all-mixed-content',
        'upgrade-insecure-requests',
    ];

    /**
     * The list of directives supporting nonce
     *
     * @var    array
     * @since  4.0.0
     */
    private $nonceDirectives = [
        'script-src',
        'style-src',
    ];

    /**
     * @param   DispatcherInterface      $dispatcher  The object to observe -- event dispatcher.
     * @param   array                    $config      An optional associative array of configuration settings.
     * @param   CMSApplicationInterface  $app         The app
     *
     * @since   4.0.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, CMSApplicationInterface $app)
    {
        parent::__construct($dispatcher, $config);

        $this->setApplication($app);

        $nonceEnabled = (int) $this->params->get('nonce_enabled', 0);

        // Nonce generation when it's enabled
        if ($nonceEnabled) {
            $this->cspNonce = base64_encode(bin2hex(random_bytes(64)));
        }

        // Set the nonce, when not set we set it to NULL which is checked down the line
        $this->getApplication()->set('csp_nonce', $this->cspNonce);
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'setHttpHeaders',
            'onAfterRender'     => 'applyHashesToCspRule',
        ];
    }

    /**
     * The `applyHashesToCspRule` method makes sure the csp hashes are added to the csp header when enabled
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function applyHashesToCspRule(Event $event): void
    {
        // CSP is only relevant on html pages. Let's early exit here.
        if ($this->getApplication()->getDocument()->getType() !== 'html') {
            return;
        }

        $scriptHashesEnabled = (int) $this->params->get('script_hashes_enabled', 0);
        $styleHashesEnabled  = (int) $this->params->get('style_hashes_enabled', 0);

        // Early exit when both options are disabled
        if (!$scriptHashesEnabled && !$styleHashesEnabled) {
            return;
        }

        $headData     = $this->getApplication()->getDocument()->getHeadData();
        $scriptHashes = [];
        $styleHashes  = [];

        if ($scriptHashesEnabled) {
            // Generate the hashes for the script-src
            $inlineScripts = \is_array($headData['script']) ? $headData['script'] : [];

            foreach ($inlineScripts as $type => $scripts) {
                foreach ($scripts as $hash => $scriptContent) {
                    $scriptHashes[] = "'sha256-" . base64_encode(hash('sha256', $scriptContent, true)) . "'";
                }
            }
        }

        if ($styleHashesEnabled) {
            // Generate the hashes for the style-src
            $inlineStyles = \is_array($headData['style']) ? $headData['style'] : [];

            foreach ($inlineStyles as $type => $styles) {
                foreach ($styles as $hash => $styleContent) {
                    $styleHashes[] = "'sha256-" . base64_encode(hash('sha256', $styleContent, true)) . "'";
                }
            }
        }

        // Replace the hashes in the csp header when set.
        $headers = $this->getApplication()->getHeaders();

        foreach ($headers as $id => $headerConfiguration) {
            if (
                strtolower($headerConfiguration['name']) === 'content-security-policy'
                || strtolower($headerConfiguration['name']) === 'content-security-policy-report-only'
            ) {
                $newHeaderValue = $headerConfiguration['value'];

                if (!empty($scriptHashes)) {
                    $newHeaderValue = str_replace('{script-hashes}', implode(' ', $scriptHashes), $newHeaderValue);
                } else {
                    $newHeaderValue = str_replace('{script-hashes}', '', $newHeaderValue);
                }

                if (!empty($styleHashes)) {
                    $newHeaderValue = str_replace('{style-hashes}', implode(' ', $styleHashes), $newHeaderValue);
                } else {
                    $newHeaderValue = str_replace('{style-hashes}', '', $newHeaderValue);
                }

                $this->getApplication()->setHeader($headerConfiguration['name'], $newHeaderValue, true);
            }
        }
    }

    /**
     * The `setHttpHeaders` method handle the setting of the configured HTTP Headers
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setHttpHeaders(Event $event): void
    {
        // Set the default header when they are enabled
        $this->setStaticHeaders();

        // Handle CSP Header configuration
        $cspEnabled = (int) $this->params->get('contentsecuritypolicy', 0);
        $cspClient  = (string) $this->params->get('contentsecuritypolicy_client', 'site');

        // Check whether CSP is enabled and enabled by the current client
        if ($cspEnabled && ($this->getApplication()->isClient($cspClient) || $cspClient === 'both')) {
            $this->setCspHeader();
        }
    }

    /**
     * Set the CSP header when enabled
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function setCspHeader(): void
    {
        $cspReadOnly = (int) $this->params->get('contentsecuritypolicy_report_only', 1);
        $cspHeader   = $cspReadOnly === 0 ? 'content-security-policy' : 'content-security-policy-report-only';

        // In custom mode we compile the header from the values configured
        $cspValues                 = $this->params->get('contentsecuritypolicy_values', []);
        $nonceEnabled              = (int) $this->params->get('nonce_enabled', 0);
        $scriptHashesEnabled       = (int) $this->params->get('script_hashes_enabled', 0);
        $strictDynamicEnabled      = (int) $this->params->get('strict_dynamic_enabled', 0);
        $styleHashesEnabled        = (int) $this->params->get('style_hashes_enabled', 0);
        $frameAncestorsSelfEnabled = (int) $this->params->get('frame_ancestors_self_enabled', 1);
        $frameAncestorsSet         = false;

        foreach ($cspValues as $cspValue) {
            // Handle the client settings foreach header
            if (!$this->getApplication()->isClient($cspValue->client) && $cspValue->client != 'both') {
                continue;
            }

            // Handle non value directives
            if (\in_array($cspValue->directive, $this->noValueDirectives)) {
                $newCspValues[] = trim($cspValue->directive);

                continue;
            }

            // We can only use this if this is a valid entry
            if (
                \in_array($cspValue->directive, $this->validDirectives)
                && !empty($cspValue->value)
            ) {
                if (\in_array($cspValue->directive, $this->nonceDirectives) && $nonceEnabled) {
                    /**
                     * That line is for B/C we do no longer require to add the nonce tag
                     * but add it once the setting is enabled so this line here is needed
                     * to remove the outdated tag that was required until 4.2.0
                     */
                    $cspValue->value = str_replace('{nonce}', '', $cspValue->value);

                    // Append the nonce when the nonce setting is enabled
                    $cspValue->value = "'nonce-" . $this->cspNonce . "' " . $cspValue->value;
                }

                // Append the script hashes placeholder
                if ($scriptHashesEnabled && strpos($cspValue->directive, 'script-src') === 0) {
                    $cspValue->value = '{script-hashes} ' . $cspValue->value;
                }

                // Append the style hashes placeholder
                if ($styleHashesEnabled && strpos($cspValue->directive, 'style-src') === 0) {
                    $cspValue->value = '{style-hashes} ' . $cspValue->value;
                }

                if ($cspValue->directive === 'frame-ancestors') {
                    $frameAncestorsSet = true;
                }

                // Add strict-dynamic to the script-src directive when enabled
                if (
                    $strictDynamicEnabled
                    && $cspValue->directive === 'script-src'
                    && strpos($cspValue->value, 'strict-dynamic') === false
                ) {
                    $cspValue->value = "'strict-dynamic' " . $cspValue->value;
                }

                $newCspValues[] = trim($cspValue->directive) . ' ' . trim($cspValue->value);
            }
        }

        if ($frameAncestorsSelfEnabled && !$frameAncestorsSet) {
            $newCspValues[] = "frame-ancestors 'self'";
        }

        if (empty($newCspValues)) {
            return;
        }

        $this->getApplication()->setHeader($cspHeader, trim(implode('; ', $newCspValues)));
    }

    /**
     * Get the configured static headers.
     *
     * @return  array  We return the array of static headers with its values.
     *
     * @since   4.0.0
     */
    private function getStaticHeaderConfiguration(): array
    {
        $staticHeaderConfiguration = [];

        // X-frame-options
        if ($this->params->get('xframeoptions', 1) === 1) {
            $staticHeaderConfiguration['x-frame-options#both'] = 'SAMEORIGIN';
        }

        // Referrer-policy
        $referrerPolicy = (string) $this->params->get('referrerpolicy', 'strict-origin-when-cross-origin');

        if ($referrerPolicy !== 'disabled') {
            $staticHeaderConfiguration['referrer-policy#both'] = $referrerPolicy;
        }

        // Cross-Origin-Opener-Policy
        $coop = (string) $this->params->get('coop', 'same-origin');

        if ($coop !== 'disabled') {
            $staticHeaderConfiguration['cross-origin-opener-policy#both'] = $coop;
        }

        // Generate the strict-transport-security header and make sure the site is SSL
        if ($this->params->get('hsts', 0) === 1 && Uri::getInstance()->isSsl() === true) {
            $hstsOptions   = [];
            $hstsOptions[] = 'max-age=' . (int) $this->params->get('hsts_maxage', 31536000);

            if ($this->params->get('hsts_subdomains', 0) === 1) {
                $hstsOptions[] = 'includeSubDomains';
            }

            if ($this->params->get('hsts_preload', 0) === 1) {
                $hstsOptions[] = 'preload';
            }

            $staticHeaderConfiguration['strict-transport-security#both'] = implode('; ', $hstsOptions);
        }

        // Generate the additional headers
        $additionalHttpHeaders = $this->params->get('additional_httpheader', []);

        foreach ($additionalHttpHeaders as $additionalHttpHeader) {
            // Make sure we have a key and a value
            if (empty($additionalHttpHeader->key) || empty($additionalHttpHeader->value)) {
                continue;
            }

            // Make sure the header is a valid and supported header
            if (!\in_array(strtolower($additionalHttpHeader->key), $this->supportedHttpHeaders)) {
                continue;
            }

            // Make sure we do not add one header twice but we support to set a different header per client.
            if (
                isset($staticHeaderConfiguration[$additionalHttpHeader->key . '#' . $additionalHttpHeader->client])
                || isset($staticHeaderConfiguration[$additionalHttpHeader->key . '#both'])
            ) {
                continue;
            }

            // Allow the custom csp headers to use the random $cspNonce in the rules
            if (\in_array(strtolower($additionalHttpHeader->key), ['content-security-policy', 'content-security-policy-report-only'])) {
                $additionalHttpHeader->value = str_replace('{nonce}', "'nonce-" . $this->cspNonce . "'", $additionalHttpHeader->value);
            }

            $staticHeaderConfiguration[$additionalHttpHeader->key . '#' . $additionalHttpHeader->client] = $additionalHttpHeader->value;
        }

        return $staticHeaderConfiguration;
    }

    /**
     * Set the static headers when enabled
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function setStaticHeaders(): void
    {
        $staticHeaderConfiguration = $this->getStaticHeaderConfiguration();

        if (empty($staticHeaderConfiguration)) {
            return;
        }

        foreach ($staticHeaderConfiguration as $headerAndClient => $value) {
            $headerAndClient = explode('#', $headerAndClient);
            $header          = $headerAndClient[0];
            $client          = $headerAndClient[1] ?? 'both';

            if (!$this->getApplication()->isClient($client) && $client != 'both') {
                continue;
            }

            $this->getApplication()->setHeader($header, $value, true);
        }
    }
}
