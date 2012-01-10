<?php
/**
 * 06/06/2010 10.10
 * 
 * Akismet PHP4 class
 * 
 * <b>Usage</b>
 * <code>
 *    $comment = array(
 *           'author'    => 'viagra-test-123',
 *           'email'     => 'test@example.com',
 *           'website'   => 'http://www.example.com/',
 *           'body'      => 'This is a test comment',
 *           'permalink' => 'http://yourdomain.com/yourblogpost.url',
 *        );
 *
 *    $akismet = new Akismet('http://www.yourdomain.com/', 'YOUR_WORDPRESS_API_KEY', $comment);
 *
 *    if($akismet->errorsExist()) {
 *        echo"Couldn't connected to Akismet server!";
 *    } else {
 *        if($akismet->isSpam()) {
 *            echo"Spam detected";
 *        } else {
 *            echo"yay, no spam!";
 *        }
 *    }
 * </code>
 * 
 * @author Bret Kuhns {@link www.miphp.net}
 * @link http://www.miphp.net/blog/view/new_akismet_class/
 * @version 0.3.4
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */



// Error constants
define("AKISMET_SERVER_NOT_FOUND",	0);
define("AKISMET_RESPONSE_FAILED",	1);
define("AKISMET_INVALID_KEY",		2);



// Base class to assist in error handling between Akismet classes
class AkismetObject {
	var $errors = array();
	
	
	/**
	 * Add a new error to the errors array in the object
	 *
	 * @param	String	$name	A name (array key) for the error
	 * @param	String	$string	The error message
	 * @return void
	 */ 
	// Set an error in the object
	function setError($name, $message) {
		$this->errors[$name] = $message;
	}
	

	/**
	 * Return a specific error message from the errors array
	 *
	 * @param	String	$name	The name of the error you want
	 * @return mixed	Returns a String if the error exists, a false boolean if it does not exist
	 */
	function getError($name) {
		if($this->isError($name)) {
			return $this->errors[$name];
		} else {
			return false;
		}
	}
	
	
	/**
	 * Return all errors in the object
	 *
	 * @return String[]
	 */ 
	function getErrors() {
		return (array)$this->errors;
	}
	
	
	/**
	 * Check if a certain error exists
	 *
	 * @param	String	$name	The name of the error you want
	 * @return boolean
	 */ 
	function isError($name) {
		return isset($this->errors[$name]);
	}
	
	
	/**
	 * Check if any errors exist
	 *
	 * @return boolean
	 */
	function errorsExist() {
		return (count($this->errors) > 0);
	}
	
	
}





// Used by the Akismet class to communicate with the Akismet service
class AkismetHttpClient extends AkismetObject {
	var $akismetVersion = '1.1';
	var $con;
	var $host;
	var $port;
	var $apiKey;
	var $blogUrl;
	var $errors = array();
	
	
	// Constructor
	function AkismetHttpClient($host, $blogUrl, $apiKey, $port = 80) {
		$this->host = $host;
		$this->port = $port;
		$this->blogUrl = $blogUrl;
		$this->apiKey = $apiKey;
	}
	
	
	// Use the connection active in $con to get a response from the server and return that response
	function getResponse($request, $path, $type = "post", $responseLength = 1160) {
		$this->_connect();
		
		if($this->con && !$this->isError(AKISMET_SERVER_NOT_FOUND)) {
			$request  = 
					strToUpper($type)." /{$this->akismetVersion}/$path HTTP/1.1\r\n" .
					"Host: ".((!empty($this->apiKey)) ? $this->apiKey."." : null)."{$this->host}\r\n" .
					"Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
					"Content-Length: ".strlen($request)."\r\n" .
					"User-Agent: Akismet PHP4 Class\r\n" .
					"\r\n" .
					$request
				;
			$response = "";

			@fwrite($this->con, $request);

			while(!feof($this->con)) {
				$response .= @fgets($this->con, $responseLength);
			}

			$response = explode("\r\n\r\n", $response, 2);
			return $response[1];
		} else {
			$this->setError(AKISMET_RESPONSE_FAILED, "The response could not be retrieved.");
		}
		
		$this->_disconnect();
	}
	
	
	// Connect to the Akismet server and store that connection in the instance variable $con
	function _connect() {
		if(!($this->con = @fsockopen($this->host, $this->port))) {
			$this->setError(AKISMET_SERVER_NOT_FOUND, "Could not connect to akismet server.");
		}
	}
	
	
	// Close the connection to the Akismet server
	function _disconnect() {
		@fclose($this->con);
	}
	
	
}





// The controlling class. This is the ONLY class the user should instantiate in
// order to use the Akismet service!
class Akismet extends AkismetObject {
	var $apiPort = 80;
	var $akismetServer = 'rest.akismet.com';
	var $akismetVersion = '1.1';
	var $http;
	
	var $ignore = array(
			'HTTP_COOKIE',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED_HOST',
			'HTTP_MAX_FORWARDS',
			'HTTP_X_FORWARDED_SERVER',
			'REDIRECT_STATUS',
			'SERVER_PORT',
			'PATH',
			'DOCUMENT_ROOT',
			'SERVER_ADMIN',
			'QUERY_STRING',
			'PHP_SELF',
			'argv'
		);
	
	var $blogUrl = "";
	var $apiKey  = "";
	var $comment = array();
	
	
	/**
	 * Constructor
	 * 
	 * Set instance variables, connect to Akismet, and check API key
	 * 
	 * @param	String	$blogUrl	The URL to your own blog
	 * @param 	String	$apiKey		Your wordpress API key
	 * @param 	String[]	$comment	A formatted comment array to be examined by the Akismet service
	 * @return	Akismet
	 */
	function Akismet($blogUrl, $apiKey, $comment = array()) {
		$this->blogUrl = $blogUrl;
		$this->apiKey  = $apiKey;
		$this->setComment($comment);
		
		// Connect to the Akismet server and populate errors if they exist
		$this->http = new AkismetHttpClient($this->akismetServer, $blogUrl, $apiKey);
		if($this->http->errorsExist()) {
			$this->errors = array_merge($this->errors, $this->http->getErrors());
		}
		
		// Check if the API key is valid
		if(!$this->_isValidApiKey($apiKey)) {
			$this->setError(AKISMET_INVALID_KEY, "Your Akismet API key is not valid.");
		}
	}
	
	
	/**
	 * Query the Akismet and determine if the comment is spam or not
	 * 
	 * @return	boolean
	 */
	function isSpam() {
		$response = $this->http->getResponse($this->_getQueryString(), 'comment-check');
		
		return ($response == "true");
	}
	
	
	/**
	 * Submit this comment as an unchecked spam to the Akismet server
	 * 
	 * @return	void
	 */
	function submitSpam() {
		$this->http->getResponse($this->_getQueryString(), 'submit-spam');
	}
	
	
	/**
	 * Submit a false-positive comment as "ham" to the Akismet server
	 *
	 * @return	void
	 */
	function submitHam() {
		$this->http->getResponse($this->_getQueryString(), 'submit-ham');
	}
	
	
	/**
	 * Manually set the comment value of the instantiated object.
	 *
	 * @param	Array	$comment
	 * @return	void
	 */
	function setComment($comment) {
		$this->comment = $comment;
		if(!empty($comment)) {
			$this->_formatCommentArray();
			$this->_fillCommentValues();
		}
	}
	
	
	/**
	 * Returns the current value of the object's comment array.
	 *
	 * @return	Array
	 */
	function getComment() {
		return $this->comment;
	}
	
	
	/**
	 * Check with the Akismet server to determine if the API key is valid
	 *
	 * @access	Protected
	 * @param	String	$key	The Wordpress API key passed from the constructor argument
	 * @return	boolean
	 */
	function _isValidApiKey($key) {
		$keyCheck = $this->http->getResponse("key=".$this->apiKey."&blog=".$this->blogUrl, 'verify-key');
			
		return ($keyCheck == "valid");
	}
	
	
	/**
	 * Format the comment array in accordance to the Akismet API
	 *
	 * @access	Protected
	 * @return	void
	 */
	function _formatCommentArray() {
		$format = array(
				'type' => 'comment_type',
				'author' => 'comment_author',
				'email' => 'comment_author_email',
				'website' => 'comment_author_url',
				'body' => 'comment_content'
			);
		
		foreach($format as $short => $long) {
			if(isset($this->comment[$short])) {
				$this->comment[$long] = $this->comment[$short];
				unset($this->comment[$short]);
			}
		}
	}
	
	
	/**
	 * Fill any values not provided by the developer with available values.
	 *
	 * @return	void
	 */
	function _fillCommentValues() {
		if(!isset($this->comment['user_ip'])) {
			$this->comment['user_ip'] = ($_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR')) ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
		}
		if(!isset($this->comment['user_agent'])) {
			$this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
		if(!isset($this->comment['referrer'])) {
			$this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
		}
		if(!isset($this->comment['blog'])) {
			$this->comment['blog'] = $this->blogUrl;
		}
	}
	
	
	/**
	 * Build a query string for use with HTTP requests
	 *
	 * @access	Protected
	 * @return	String
	 */
	function _getQueryString() {
		foreach($_SERVER as $key => $value) {
			if(!in_array($key, $this->ignore)) {
				if($key == 'REMOTE_ADDR') {
					$this->comment[$key] = $this->comment['user_ip'];
				} else {
					$this->comment[$key] = $value;
				}
			}
		}

		$query_string = '';

		foreach($this->comment as $key => $data) {
			$query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';
		}

		return $query_string;
	}
	
	
}