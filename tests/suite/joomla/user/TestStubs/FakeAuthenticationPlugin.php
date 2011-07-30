<?php

class plgAuthenticationFake
{
	public $name = 'fake';	
	
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		if($credentials['username'] == 'test' && $credentials['password'] == 'test')
		{
			$response->status = JAuthentication::STATUS_SUCCESS;
		}
	}

	public function onUserAuthorisation($response, $options=array())
	{
		$return_value = new JAuthenticationResponse();
		switch($response->username)
		{
			case 'test':
				$return_value->status = JAuthentication::STATUS_SUCCESS;
				break;
			case 'expired':
				$return_value->status = JAuthentication::STATUS_EXPIRED;
				break;
			case 'denied':
				$return_value->status = JAuthentication::STATUS_DENIED;
				break;
			default:
				$return_value->status = JAuthentication::STATUS_UNKNOWN;
				break;
		}
		return $return_value;
	}
}
