<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Check if field 'username' (in com_user register form), meets some requirements:
 *   - Minimum number of letters (if no specified, no limit apply).
 *
 */
class PlgUserUsernameCheck extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;
	
	/**
	 * Method to handle the "onUserBeforeSave" event
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean $isNew   True to allow the save process to continue,
	 *			     false to stop it
	 *
	 * @since   3.6
	 */
	public function onUserBeforeSave($user, $isNew, $data)
	{
		// If we aren't saving a "new" user (registration), or if we are not 
		// in the front end of the site, the let the save happen withour interrption.
		if (!$isNew || !JFactory::getApplication()->isSite()) {
			return true;
		}
		
		// Load the language file for the plugin
		$this->loadLanguage();
		$result = true;
		
		// Create the input object
		// http://joomla.stackexchange.com/questions/8883/joomla-3-3-deprecated-function-for-jrequestgetvar
		$input = JFactory::getApplication()->input;
		
		// Get the $username field
		$username = $data['username'];
		
		// Get the number of characters in $username
		$usernameLenght = StringHelper::strlen($username);
		
		// CHECK MINIMUM CHARACTER'S NUMBER
		
		// Get the minimum number of characters
		$minNumChars = $this->params->get('minNumChars');
		
		// Only check if minNumChars field is set
		if ($minNumChars != 0){
			// Check if $usernameLenght achieve minimum lenght
			if ($usernameLenght < $minNumChars) {
				$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_MINNUMCHARS_REQUIRED', $minNumChars, $username), 'warning');
				$result = false;
			}
		}
		
		// CHECK MAXIMUM CHARACTER'S NUMBER
		
		// Get the minimum number of characters
		$maxNumChars = $this->params->get('maxNumChars');
		
		// Only check if maxNumChars field is set
		if ($maxNumChars != 0){
			// Check if $usernameLenght achieve minimum lenght
			if ($usernameLenght > $maxNumChars) {
				$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_MAXNUMCHARS_REQUIRED', $maxNumChars, $username), 'warning');
				$result = false;
			}
		}
		
		// CHECK IF USERNAME SPELLING IN CHARACTER SET
		
		// Get the charset specified in the plugin's parameters
		$charset = $this->params->get('charset');
		
		// Only check if $charset field is set
		if (!empty($charset)) {
			// Convert username to an array of characters
			$usernameSpelling = array_unique(StringHelper::str_split($username));
			
			// Buffer for non-compliance characters
			$notAllowedCharsArray = array();
			
			// Walk the $usernameSpelling array looking for not allowed characters
			foreach ($usernameSpelling as $char) {
				// If the $char is not in charset, store it in buffer.
				if (StringHelper::strpos($charset, $char) === false) {
					$notAllowedCharsArray[] = $char;
				}
			}
			
			// If there are $chars not allowed, report it
			if (count($notAllowedCharsArray)) {
				$notAllowedCharsString = implode(" ", $notAllowedCharsArray);
				$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_CHARSET_REQUIRED', $notAllowedCharsString), 'warning');
				$result = false;
			}
		}
		
		// Return result
		return $result;
		
	}
}