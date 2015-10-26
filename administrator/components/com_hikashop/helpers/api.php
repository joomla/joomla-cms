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

if(!class_exists('HikApiHelper')) {
	if(!HIKASHOP_J16)
		$path = JPATH_PLUGINS.DS.'system'.DS.'hikapihelper.php';
	else
		$path = JPATH_PLUGINS.DS.'system'.DS.'hikapi'.DS.'hikapihelper.php';
	if(!file_exists($path))
		return;
	require_once($path);
}

class HikashopApiHelper extends HikApiHelper {
	protected $routes = array(
		'/user' => array(
			'ctrl' => 'helper.api-user'
		),
		'/user/auth' => array(
			'ctrl' => 'helper.api-user'
		),
		'/user/create' => array(
			'ctrl' => 'helper.api-user'
		),
		'/user/update' => array(
			'ctrl' => 'helper.api-user'
		),
		'/user/require' => array(
			'ctrl' => 'helper.api-user'
		),

		'/product/:id' => array(
			'ctrl' => 'helper.api-product'
		),
		'/product/waitlist/:id' => array(
			'ctrl' => 'helper.api-product'
		),
		'/products/:id' => array(
			'ctrl' => 'helper.api-product',
			'options' => array(
				'pagination' => true
			)
		),
		'/products' => '/products/0', // alias
		'/products/filters/:id' => array(
			'ctrl' => 'helper.api-product'
		),

		'/category/:id' => array(
			'ctrl' => 'helper.api-category'
		),
		'/categories/:id' => array(
			'ctrl' => 'helper.api-category',
			'params' => array(
				'depth' => 'int'
			),
			'options' => array(
				'pagination' => true
			)
		),
		'/categories' => '/categories/0', // alias

		'/cart/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/cart' => '/cart/0',
		'/cart/add/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/cart/add' => '/cart/add/0',
		'/cart/update/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/cart/update' => '/cart/update/0',
		'/cart/delete/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/cart/delete' => '/cart/delete/0',
		'/carts' => array(
			'ctrl' => 'helper.api-cart',
		),

		'/wishlist/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/wishlist' => '/cart/0',
		'/wishlist/add/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/wishlist/add' => '/wishlist/add/0',
		'/wishlist/update/:id' => array( // For products only
			'ctrl' => 'helper.api-cart',
		),
		'/wishlist/update' => '/wishlist/update/0',
		'/wishlist/delete/:id' => array(
			'ctrl' => 'helper.api-cart',
		),
		'/wishlist/delete' => '/wishlist/delete/0',
		'/wishlists' => array(
			'ctrl' => 'helper.api-cart',
		),

		'/order/:id' => array(
			'ctrl' => 'helper.api-order',
		),
		'/orders/' => array(
			'ctrl' => 'helper.api-order',
			'options' => array(
				'pagination' => true
			)
		),
		'/order/create' => array(
			'ctrl' => 'helper.api-order',
		),
		'/order/pay/:id' => array(
			'ctrl' => 'helper.api-order',
		),
		'/order/require/:id' => array(
			'ctrl' => 'helper.api-order',
		),

		'/shippings/' => array(
			'ctrl' => 'helper.api-shipping',
		),
		'/shippings/:cartid' => array(
			'ctrl' => 'helper.api-shipping',
		),

		'/payments/' => array(
			'ctrl' => 'helper.api-payment',
		),
		'/payments/:cartid' => array(
			'ctrl' => 'helper.api-payment',
		),

		'/addresses' => array(
			'ctrl' => 'helper.api-address'
		),
		'/address/:id' => array(
			'ctrl' => 'helper.api-address'
		),
		'/address/create' => array(
			'ctrl' => 'helper.api-address'
		),
		'/address/update/:id' => array(
			'ctrl' => 'helper.api-address'
		),
		'/address/delete/:id' => array(
			'ctrl' => 'helper.api-address'
		),
		'/address/require' => array(
			'ctrl' => 'helper.api-address'
		),

		'/zones' => array(
			'ctrl' => 'helper.api-zone'
		),

		'/currencies' => array(
			'ctrl' => 'helper.api-currency'
		),

		'/vote/:type/:id' => array(
			'ctrl' => 'helper.api-vote',
		),
		'/votes/:type/:id' => array(
			'ctrl' => 'helper.api-vote',
			'options' => array(
				'pagination' => true
			)
		),
	);

	public function getController($ctrl) {
		if(empty($ctrl))
			return false;
		if(is_object($ctrl))
			return $ctrl;

		if(is_string($ctrl) && substr($ctrl, 0, 11) == 'helper.api-') {
			$ret = hikashop_get($ctrl);
			if(!empty($ret))
				return $ret;
		}

		return parent::getController($ctrl);
	}

	public function auth($data) {
		if(empty($data['username']) || empty($data['password'])) {
			$this->setHeaders(array(
				'error' => 500,
			));
			return false;
		}

		$options = array(
			'remember' => false,
			'return' => false,
		);
		$credentials = array(
			'username' => $data['username'],
			'password' => $data['password'],
		);

		$app = JFactory::getApplication();
		$error = $app->login($credentials, $options);
		$user = JFactory::getUser();
		if(JError::isError($error)) {
			$this->setHeaders(array(
				'error' => $error,
			));
			return false;
		}
		if($user->guest) {
			$this->setHeaders(array(
				'error' => 401,
			));
			return false;
		}

		$hkUser = hikashop_loadUser(true);
		$api_salt = $this->getSalt();
		$timestamp = time();
		$timestamp -= ($timestamp % 60);

		$token_frame = (int)$this->plugin_params->get('token_frame', 15);
		if($token_frame < 2)
			$token_frame = 2;
		$timestamp -= ($timestamp % ($token_frame * 60));

		return array(
			'user' => $hkUser->user_email,
			'token' => sha1((int)$hkUser->user_id . '#' . (int)$hkUser->user_cms_id . '#' . (int)$hkUser->user_created . '#' . date('dmY:Hi', $timestamp) . '#' . $api_salt),
		);
	}

	public function checkToken($data) {
		if(empty($data['user']) || empty($data['token']))
			return false;

		$db = JFactory::getDBO();
		$query = 'SELECT * FROM ' . hikashop_table('user') . ' WHERE user_email = ' . $db->Quote($data['user']);
		$db->setQuery($query);
		$hkUser = $db->loadObject();

		if(empty($hkUser))
			return false;

		$api_salt = $this->getSalt();
		$token_frame = $this->getTokenFrame();

		$timestamp = time();
		$timestamp -= ($timestamp % 60);
		$timestamp -= ($timestamp % (60 * $token_frame));

		$token = sha1((int)$hkUser->user_id . '#' . (int)$hkUser->user_cms_id . '#' . (int)$hkUser->user_created . '#' . date('dmY:Hi', $timestamp) . '#' . $api_salt);
		$previous_token = sha1((int)$hkUser->user_id . '#' . (int)$hkUser->user_cms_id . '#' . (int)$hkUser->user_created . '#' . date('dmY:Hi', $timestamp - ($token_frame * 60)) . '#' . $api_salt);

		if(($data['token'] == $token) || ($data['token'] == $previous_token)) {
			$this->setHeader('token', $token);

			$app = JFactory::getApplication();
			$app->setUserState(HIKASHOP_COMPONENT.'.user_id', $hkUser->user_id);

			if((int)$hkUser->user_cms_id > 0) {
				$user = JFactory::getUser((int)$hkUser->user_cms_id);
				JFactory::getSession()->set('user', $user);
			} else {
				JFactory::getSession()->set('user', null);
			}

			return true;
		}
		return false;
	}

	public function getSalt() {
		$config = hikashop_config();
		$ret = $config->get('api_store_salt', null);
		if(!empty($ret))
			return $ret;

		$ret = $this->generateSalt();

		$update_config = new stdClass();
		$update_config->api_store_salt = $ret;
		$config->save($update_config, true);
		$config->set('api_store_salt', $ret);

		return $ret;
	}


	public function getTokenFrame() {
		$token_frame = $this->plugin_params->get('token_frame', 15);
		if($token_frame < 2)
			$token_frame = 2;
		return $token_frame;
	}
}
