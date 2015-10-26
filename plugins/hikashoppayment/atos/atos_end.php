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
$usefullVars = array(
	'address' => $this->vars['address'],
	'address2' => $this->vars['address2'],
	'lastname' => $this->vars['lastname'],
	'country' => $this->vars['country'],
	'postal_code' => $this->vars['postal_code'],
	'city' => $this->vars['city'],
	'state' => $this->vars['state'],
	'phone_number' => $this->vars['phone_number'],
	'title' => $this->vars['title'],
	'firstname' => $this->vars['firstname'],
	'caddie' => $this->vars['caddie']
);

$xCaddie = base64_encode(serialize($usefullVars));
$parm = "merchant_id=" . $this->vars["merchant_id"] .
	" merchant_country=" . $this->vars["merchant_country"] .
	" amount=" . $this->vars["amount"] .
	" currency_code=" . $this->vars["currency_code"] .
	" pathfile=" . $this->vars["upload_folder"]."pathfile" .
	" normal_return_url=" . $this->vars["return_url"] .
	" cancel_return_url=" . $this->vars["cancel_return_url"] .
	" automatic_response_url=" . $this->vars["automatic_response_url"] .
	" language=" . $this->vars["language"] .
	" payment_means=" . $this->vars["payment_means"] .
	" header_flag=yes" .
	" capture_day=" . $this->vars["delay"] .
	" capture_mode=" . $this->vars["capture_mode"] .
	" block_align=center" .
	" block_order=1,2,3,4,5,6,7,8" .
	" caddie=" . $xCaddie .
	" customer_id=" . $this->vars["user_id"] .
	" customer_email=" . $this->vars["customer_email"];


if(strpos($this->vars["customer_ip"], ':') === false)
	$parm .= " customer_ip_address=" . $this->vars["customer_ip"];

$parm .= " order_id=".$this->vars["caddie"];

if(!empty($this->vars["data"]))
	$parm .= " data=" . $this->vars["data"];

$os = strtolower(substr(PHP_OS, 0, 3));
$path_bin = $this->vars["bin_folder"] . ( ($os == 'win') ? 'request.exe' : 'request' );

$result = exec($path_bin . ' ' . $parm);
$tableau = explode ('!', $result);

$code = $tableau[1];
$error = $tableau[2];
$message = $tableau[3];

if(( $code == "" ) && ( $error == "" ) ) {
	echo "<br/><center>erreur appel request</center><br/>" .
		"executable request non trouve ou non executable ".$path_bin;
} else if($code != 0) {
	echo "<center><b><h2>Erreur appel API de paiement.</h2></center></b>" .
		"<br /><br /><br />" .
		" message erreur : ".$error." <br />";
} else {
	echo "<br />" . $error . "<br />" . $message . "<br />";
}
echo ("</body></html>");
