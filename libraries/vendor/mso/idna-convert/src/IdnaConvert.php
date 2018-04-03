<?php

// {{{ license

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
//
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 51 Franklin St, Boston, MA 02110, United States    |
// +----------------------------------------------------------------------+
//
// }}}

/**
 * Encode/decode Internationalized Domain Names.
 *
 * The class allows to convert internationalized domain names
 * (see RFC 3490 for details) as they can be used with various registries worldwide
 * to be translated between their original (localized) form and their encoded form
 * as it will be used in the DNS (Domain Name System).
 *
 * The class provides two public methods, encode() and decode(), which do exactly
 * what you would expect them to do. You are allowed to use complete domain names,
 * simple strings and complete email addresses as well. That means, that you might
 * use any of the following notations:
 *
 * - www.nörgler.com
 * - xn--nrgler-wxa
 * - xn--brse-5qa.xn--knrz-1ra.info
 *
 * Unicode input might be given as either UTF-8 string, UCS-4 string or UCS-4 array.
 * Unicode output is available in the same formats.
 * You can select your preferred format via {@link set_paramter()}.
 *
 * ACE input and output is always expected to be ASCII.
 *
 * @author  Matthias Sommerfeld <mso@phlylabs.de>
 * @copyright 2004-2016 phlyLabs Berlin, http://phlylabs.de
 * @version 1.0.1-dev 2016-01-12
 */

namespace Mso\IdnaConvert;

class IdnaConvert {

    const Version = '1.1.0';
    const SubVersion = 'main';

    // Internal settings, do not touch!
    protected $encoding = 'utf8';          // Default input charset is UTF-8
    protected $strictMode = false;         // Behave strict or not
    protected $idnVersion = '2008';          // Can be either 2003 (old) or 2008 (default)

    protected $NamePrepData = null;
    protected $UnicodeTranscoder = null;

    /**
     * the constructor
     *
     * @param array|null $params Parameters to control the class' behaviour
     * @since 0.5.2
     */
    public function __construct($params = null)
    {
        $this->UnicodeTranscoder = new UnicodeTranscoder();

        // Kept for backwarsds compatibility. Consider using the setter methods instead.
        if (!empty($params) && is_array($params)) {
            if (isset($params['encoding'])) {
                $this->setEncoding($params['encoding']);
            }

            if (isset($params['idn_version'])) {
                $this->setIdnVersion($params['idn_version']);
            }

            if (isset($params['strict_mode'])) {
                $this->setStrictMode($params['strict_mode']);
            }
        }

        $this->setIdnVersion($this->idnVersion);
    }

    public function getClassVersion()
    {
        return self::Version.'-'.self::SubVersion;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        switch ($encoding) {
            case 'utf8':
            case 'ucs4_string':
            case 'ucs4_array':
                $this->encoding = $encoding;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid encoding %s', $encoding));
        }
    }

    /**
     * @return boolean
     */
    public function isStrictMode()
    {
        return $this->strictMode;
    }

    /**
     * @param boolean $strictMode
     */
    public function setStrictMode($strictMode)
    {
        $this->strictMode = ($strictMode) ? true : false;
    }

    /**
     * @return int
     */
    public function getIdnVersion()
    {
        return $this->idnVersion;
    }

    /**
     * @param int $idnVersion
     */
    public function setIdnVersion($idnVersion)
    {
        if (in_array($idnVersion, ['2003', '2008'])) {
            if (is_null($this->NamePrepData) || $idnVersion != $this->idnVersion) {
                $this->NamePrepData = null; // Ought to destroy the object's reference
                // Re-instantiate with different data set
                $this->NamePrepData = ($idnVersion == 2003)
                        ? new NamePrepData2003()
                        : new NamePrepData();
            }

            $this->idnVersion = $idnVersion;

        } else {
            throw new \InvalidArgumentException(sprintf('Invalid IDN version %d', $idnVersion));
        }
    }

    /**
     * Decode a given ACE domain name
     * @param string $input  Domain name (ACE string)
     * [@param string $one_time_encoding  Desired output encoding]
     * @return string  Decoded Domain name (UTF-8 or UCS-4)
     */
    public function decode($input, $one_time_encoding = null)
    {
        $punyCode = $this->punycodeFactory();

        // Optionally set
        if ($one_time_encoding) {
            switch ($one_time_encoding) {
                case 'utf8':
                case 'ucs4_string':
                case 'ucs4_array':
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Invalid encoding %s', $one_time_encoding));
            }
        }
        // Make sure to drop any newline characters around
        $input = trim($input);

        // Negotiate input and try to determine, whether it is a plain string,
        // an email address or something like a complete URL
        if (strpos($input, '@')) { // Maybe it is an email address
            // No no in strict mode
            if ($this->strictMode) {
                throw new \InvalidArgumentException('Only individual domain name parts can be handled in strict mode');
            }
            list ($email_pref, $input) = explode('@', $input, 2);
            $arr = explode('.', $input);
            foreach ($arr as $k => $v) {
                $conv = $punyCode->decode($v);
                if ($conv) {
                    $arr[$k] = $conv;
                }
            }
            $input = join('.', $arr);
            $arr = explode('.', $email_pref);
            foreach ($arr as $k => $v) {
                $conv = $punyCode->decode($v);
                if ($conv) {
                    $arr[$k] = $conv;
                }
            }
            $email_pref = join('.', $arr);
            $return = $email_pref . '@' . $input;
        } elseif (preg_match('![:\./]!', $input)) { // Or a complete domain name (with or without paths / parameters)
            // No no in strict mode
            if ($this->strictMode) {
                throw new \InvalidArgumentException('Only individual domain name parts can be handled in strict mode');
            }
            $parsed = parse_url($input);
            if (isset($parsed['host'])) {
                $arr = explode('.', $parsed['host']);
                foreach ($arr as $k => $v) {
                    $conv = $punyCode->decode($v);
                    if ($conv) {
                        $arr[$k] = $conv;
                    }
                }
                $parsed['host'] = join('.', $arr);
                $return = (empty($parsed['scheme']) ? '' : $parsed['scheme'] . (strtolower($parsed['scheme']) == 'mailto' ? ':' : '://')).
                        (empty($parsed['user']) ? '' : $parsed['user'] . (empty($parsed['pass']) ? '' : ':' . $parsed['pass']) . '@').
                        $parsed['host'].
                        (empty($parsed['port']) ? '' : ':' . $parsed['port']).
                        (empty($parsed['path']) ? '' : $parsed['path']).
                        (empty($parsed['query']) ? '' : '?' . $parsed['query']).
                        (empty($parsed['fragment']) ? '' : '#' . $parsed['fragment']);
            } else { // parse_url seems to have failed, try without it
                $arr = explode('.', $input);
                foreach ($arr as $k => $v) {
                    $conv = $punyCode->decode($v);
                    if ($conv) {
                        $arr[$k] = $conv;
                    }
                }
                $return = join('.', $arr);
            }
        } else { // Otherwise we consider it being a pure domain name string
            $return = $punyCode->decode($input);
            if (!$return) {
                $return = $input;
            }
        }
        // The output is UTF-8 by default, other output formats need conversion here
        // If one time encoding is given, use this, else the objects property
        $outputEncoding = ($one_time_encoding) ? $one_time_encoding : $this->encoding;
        switch ($outputEncoding) {
            case 'utf8':
                return $return; // break;
            case 'ucs4_string':
                return $this->UnicodeTranscoder->convert($return, 'utf8', 'ucs4');  // break;
            case 'ucs4_array':
                return $this->UnicodeTranscoder->convert($return, 'utf8', 'ucs4array');  // break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported output encoding %s', $outputEncoding));
        }
    }

    /**
     * Encode a given UTF-8 domain name
     * @param string $decoded  Domain name (UTF-8 or UCS-4)
     * [@param boolean  $one_time_encoding  Desired input encoding, see {@link set_parameter}]
     * @return string   Encoded Domain name (ACE string)
     */
    public function encode($decoded, $one_time_encoding = false)
    {
        // Forcing conversion of input to UCS4 array
        // If one time encoding is given, use this, else the objects property
        $inputEncoding = $one_time_encoding ? $one_time_encoding : $this->encoding;
        switch ($inputEncoding) {
            case 'utf8':
                $decoded = $this->UnicodeTranscoder->convert($decoded, 'utf8', 'ucs4array');
                break;
            case 'ucs4_string':
                $decoded = $this->UnicodeTranscoder->convert($decoded, 'ucs4', 'ucs4array');
                break;
            case 'ucs4_array':
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported input encoding %s', $inputEncoding));
        }

        // No input, no output, what else did you expect?
        if (empty($decoded)) {
            return '';
        }

        $punyCode = $this->punycodeFactory();

        // Anchors for iteration
        $last_begin = 0;
        // Output string
        $output = '';
        foreach ($decoded as $k => $v) {
            // Make sure to use just the plain dot
            switch ($v) {
                case 0x3002:
                case 0xFF0E:
                case 0xFF61:
                    $decoded[$k] = 0x2E;
                    // Right, no break here, the above are converted to dots anyway
                // Stumbling across an anchoring character
                case 0x2E:
                case 0x2F:
                case 0x3A:
                case 0x3F:
                case 0x40:
                    // Neither email addresses nor URLs allowed in strict mode
                    if ($this->strictMode) {
                        throw new \InvalidArgumentException('Neither email addresses nor URLs are allowed in strict mode.');
                    } else {
                        // Skip first char
                        if ($k) {
                            $encoded = $punyCode->encode(array_slice($decoded, $last_begin, (($k) - $last_begin)));
                            if ($encoded) {
                                $output .= $encoded;
                            } else {
                                $output .= $this->UnicodeTranscoder->convert(array_slice($decoded, $last_begin, (($k) - $last_begin)), 'ucs4array', 'utf8');
                            }
                            $output .= chr($decoded[$k]);
                        }
                        $last_begin = $k + 1;
                    }
            }
        }
        // Catch the rest of the string
        if ($last_begin) {
            $inp_len = sizeof($decoded);
            $encoded = $punyCode->encode(array_slice($decoded, $last_begin, (($inp_len) - $last_begin)));
            if ($encoded) {
                $output .= $encoded;
            } else {
                $output .= $this->UnicodeTranscoder->convert(array_slice($decoded, $last_begin, (($inp_len) - $last_begin)), 'ucs4array', 'utf8');
            }
            return $output;
        } else {
            if (false !== ($output = $punyCode->encode($decoded))) {
                return $output;
            } else {
                return $this->UnicodeTranscoder->convert($decoded, 'ucs4array', 'utf8');
            }
        }
    }

    /**
     * Mitigates a weakness of encode(), which cannot properly handle URIs but instead encodes their
     * path or query components, too.
     * @param string  $uri  Expects the URI as a UTF-8 (or ASCII) string
     * @return  string  The URI encoded to Punycode, everything but the host component is left alone
     * @since 0.6.4
     */
    public function encodeUri($uri)
    {
        $parsed = parse_url($uri);
        if (!isset($parsed['host'])) {
            throw new \InvalidArgumentException('The given string does not look like a URI');
        }
        $arr = explode('.', $parsed['host']);
        foreach ($arr as $k => $v) {
            $conv = $this->encode($v, 'utf8');
            if ($conv) {
                $arr[$k] = $conv;
            }
        }
        $parsed['host'] = join('.', $arr);
        $return = (empty($parsed['scheme']) ? '' : $parsed['scheme'] . (strtolower($parsed['scheme']) == 'mailto' ? ':' : '://')).
                (empty($parsed['user']) ? '' : $parsed['user'] . (empty($parsed['pass']) ? '' : ':' . $parsed['pass']) . '@').
                $parsed['host'].
                (empty($parsed['port']) ? '' : ':' . $parsed['port']).
                (empty($parsed['path']) ? '' : $parsed['path']).
                (empty($parsed['query']) ? '' : '?' . $parsed['query']).
                (empty($parsed['fragment']) ? '' : '#' . $parsed['fragment']);
        return $return;
    }

    /**
     * The actual punycode class is rather costly, as well as passing the huge nameprep database around.
     * This factory method allows to ease the burden when dealing with multiple IDN versions.
     *
     * @return \Mso\IdnaConvert\Punycode
     */
    protected function punycodeFactory()
    {
        static $instances = [];

        if (!isset($instances[$this->idnVersion])) {
            $instances[$this->idnVersion] = new Punycode($this->NamePrepData, $this->UnicodeTranscoder);
        }
        return $instances[$this->idnVersion];
    }

}
