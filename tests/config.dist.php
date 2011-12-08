<?php
/**
 * Distribution configuration class.  If no custom one is created this class will be loaded into the
 * configuration object for running unit tests.  To create a custom configuration class simply copy
 * the contents of this file to 'config.php' in the same folder and modify the public members to
 * accomodate your system.
 *
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @since      11.1
 */
class JTestConfig
{
	public $dbtype		= 'mysqli';
	public $host		= '127.0.0.1';
	public $user		= 'utuser';
	public $password	= 'ut1234';
	public $db			= 'joomla_ut';
	public $dbprefix	= 'jos_';
	public $ftp_host	= '127.0.0.1';
	public $ftp_port	= '21';
	public $ftp_user	= '';
	public $ftp_pass	= '';
	public $ftp_root	= '';
	public $ftp_enable	= 0;
	public $tmp_path	= '/tmp';
	public $log_path	= '/var/logs';
	public $mailer		= 'mail';
	public $mailfrom	= 'admin@localhost.home';
	public $fromname	= '';
	public $sendmail	= '/usr/sbin/sendmail';
	public $smtpauth	= '0';
	public $smtpsecure = 'none';
	public $smtpport	= '25';
	public $smtpuser	= '';
	public $smtppass	= '';
	public $smtphost	= 'localhost';
	public $debug		= 0;
	public $caching		= '0';
	public $cachetime	= '900';
	public $language	= 'en-GB';
	public $secret		= null;
	public $editor		= 'none';
	public $offset		= 0;
	public $lifetime	= 15;
	public $jhttp_stub	= 'http://127.0.0.1/jhttp_stub.php';
}
