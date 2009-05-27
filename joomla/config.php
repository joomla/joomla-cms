<?php
class JFrameworkConfig
{
	var $dbtype 	= 'mysql';
	var $host 		= 'localhost';
	var $user 		= '';
	var $password 	= '';
	var $db 		= '';
	var $dbprefix 	= 'jos_';
	var $ftp_host 	= '127.0.0.1';
	var $ftp_port 	= '21';
	var $ftp_user 	= '';
	var $ftp_pass 	= '';
	var $ftp_root 	= '';
	var $ftp_enable = 0;
	var $tmp_path	= '/tmp';
	var $log_path	= '/var/logs';
	var $mailer 	= 'mail';
	var $mailfrom 	= 'admin@localhost.home';
	var $fromname 	= '';
	var $sendmail 	= '/usr/sbin/sendmail';
	var $smtpauth 	= '0';
	var $smtpuser 	= '';
	var $smtppass 	= '';
	var $smtphost 	= 'localhost';
	var $debug 		= 0;
	var $caching 	= '0';
	var $cachetime	= '900';
	var $language  	= 'en-GB';
	var $secret		= null;
	var $editor		= 'none';
	var $offset		= 0;
	var $lifetime	= 15;
}