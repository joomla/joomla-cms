<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

global $bpOptions;



$bpOptions['apiKey'] = '';

$bpOptions['verifyPos'] = true;

$bpOptions['notificationEmail'] = '';

# example: $bpNotificationUrl = 'http://www.example.com/callback.php';
$bpOptions['notificationURL'] = '';

# example: $bpNotificationUrl = 'http://www.example.com/confirmation.php';
$bpOptions['redirectURL'] = '';

$bpOptions['currency'] = 'BTC';

$bpOptions['physical'] = 'true';

$bpOptions['fullNotifications'] = 'true';

$bpOptions['transactionSpeed'] = 'low'; 

?>
