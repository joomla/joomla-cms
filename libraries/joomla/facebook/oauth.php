<?php 

/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

defined('JPATH_PLATFORM') or die();


/**
 * Joomla Platform class for generating Facebook API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */
class JFacebookOAuth
{
	/**
	* @var    string  app_id for the Facebook application.
	*/
	protected $app_id;

	/**
	* @var    string  app_secret for the Facebook application.
	*/
	protected $app_secret;

	/**
	* @var    string  redirect uri for the Facebook application.
	*/
	protected $my_url;

	/**
	 * @var string	comma separated list of permissions.
	 */
	protected $scope;

	/**
	 * Constructor.
	 *
	 * @param   string  $app_id      Facebook application's id.
	 * @param   string  $app_secret  Facebook application's secret.
	 * @param   string  $my_url      Facebook redirect uri.
	 */
	public function __construct($app_id, $app_secret, $my_url)
	{
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
		$this->my_url = $my_url;
	}

	/**
	 * Method used to set permissions.
	 * 
	 * @param   string  $scope  Comma separated list of permissions.
	 */
	public function setScope($scope)
	{
		$this->scope = $scope;
	}

	/**
	 * Method to get the current scope
	 * 
	 * @return string Comma separated list of permissions.
	 */
	public function getScope()
	{
		return $this->scope;
	}

	/**
	 * Method to get an access token for Facebook.
	 *
	 * @return  string  The access token.
	 */
	public function getToken()
	{
		// Browser session
		$session = JFactory::getSession();

		$request = JFactory::getApplication()->input;
		$code = $request->get('code');

		// Once the user is successfully authenticated he is asked to authorize the app.
		if (empty($code))
		{
			// CSRF protection
			$session->set('my_state', md5(uniqid(rand(), true)));
			$dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
				. $this->app_id . "&redirect_uri=" . urlencode($this->my_url) . "&state="
				. $session->get('my_state') . "&scope=" . $this->scope;

			echo("<script> top.location.href='" . $dialog_url . "'</script>");
		}

		// If the user hits Allow, your app is authorized abd obtains the access token
		if ($request->get('state') == $session->get('my_state'))
		{
			$token_url = "https://graph.facebook.com/oauth/access_token?"
			. "client_id=" . $this->app_id . "&redirect_uri=" . urlencode($this->my_url)
			. "&client_secret=" . $this->app_secret . "&code=" . $code;

			$response = @file_get_contents($token_url);

			$params = null;
			parse_str($response, $params);

			return $params['access_token'];
		}
		else
		{
			throw new DomainException("The state does not match. You may be a victim of CSRF.");
		}
	}
}
