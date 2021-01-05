<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Social Communications class for the Joomla Platform.
 *
 * @since  3.2.0
 */
class JLinkedinCommunications extends JLinkedinObject
{
	/**
	 * Method used to invite people.
	 *
	 * @param   string  $email       A string containing email of the recipient.
	 * @param   string  $firstName   A string containing first name of the recipient.
	 * @param   string  $lastName    A string containing last name of the recipient.
	 * @param   string  $subject     The subject of the message that will be sent to the recipient
	 * @param   string  $body        A text of the message.
	 * @param   string  $connection  Only connecting as a 'friend' is supported presently.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function inviteByEmail($email, $firstName, $lastName, $subject, $body, $connection = 'friend')
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base.
		$base = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/email=' . $email . '">
							<first-name>' . $firstName . '</first-name>
							<last-name>' . $lastName . '</last-name>
						</person>
					</recipient>
				</recipients>
				<subject>' . $subject . '</subject>
				<body>' . $body . '</body>
				<item-content>
				    <invitation-request>
				      <connect-type>' . $connection . '</connect-type>
				    </invitation-request>
				</item-content>
			 </mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to invite people.
	 *
	 * @param   string  $id          Member id.
	 * @param   string  $firstName   A string containing first name of the recipient.
	 * @param   string  $lastName    A string containing last name of the recipient.
	 * @param   string  $subject     The subject of the message that will be sent to the recipient
	 * @param   string  $body        A text of the message.
	 * @param   string  $connection  Only connecting as a 'friend' is supported presently.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function inviteById($id, $firstName, $lastName, $subject, $body, $connection = 'friend')
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base for people search.
		$base = '/v1/people-search:(people:(api-standard-profile-request))';

		$data['format'] = 'json';
		$data['first-name'] = $firstName;
		$data['last-name'] = $lastName;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		if (strpos($response->body, 'apiStandardProfileRequest') === false)
		{
			throw new RuntimeException($response->body);
		}

		// Get header value.
		$value = explode('"value": "', $response->body);
		$value = explode('"', $value[1]);
		$value = $value[0];

		// Split on the colon character.
		$value = explode(':', $value);
		$name = $value[0];
		$value = $value[1];

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base.
		$base = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>
				  	<recipient>
						<person path="/people/id=' . $id . '">
						</person>
					</recipient>
				</recipients>
				<subject>' . $subject . '</subject>
				<body>' . $body . '</body>
				<item-content>
				    <invitation-request>
				      <connect-type>' . $connection . '</connect-type>
				      <authorization>
				      	<name>' . $name . '</name>
				        <value>' . $value . '</value>
				      </authorization>
				    </invitation-request>
				</item-content>
			 </mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to send messages via LinkedIn between two or more individuals connected to the member sending the message..
	 *
	 * @param   mixed   $recipient  A string containing the member id or an array of ids.
	 * @param   string  $subject    The subject of the message that will be sent to the recipient
	 * @param   string  $body       A text of the message.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function sendMessage($recipient, $subject, $body)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base.
		$base = '/v1/people/~/mailbox';

		// Build the xml.
		$xml = '<mailbox-item>
				  <recipients>';

		if (is_array($recipient))
		{
			foreach ($recipient as $r)
			{
				$xml .= '<recipient>
							<person path="/people/' . $r . '"/>
						</recipient>';
			}
		}

		$xml .= '</recipients>
				 <subject>' . $subject . '</subject>
				 <body>' . $body . '</body>
				</mailbox-item>';

		$header['Content-Type'] = 'text/xml';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}
}
