<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 3 or later
 */

defined('_JEXEC') || die;

/**
 * User information display field
 *
 * Use this by extending it (I'm using -at- instead of the actual at-sign)
 * -at-include('any:lib_fof30/Common/user_show', $params)
 *
 * $params is an array defining the following keys (they are expanded into local scope vars automatically):
 *
 * @var \FOF30\Model\DataModel $item          The record which holds the user ID in the $field property
 * @var string                 $field         The name of the field in the current row containing the user ID
 * @var string                 $id            The ID of the generated DIV
 * @var string                 $showUsername  Should I display the username?
 * @var string                 $showEmail     Should I display the email address?
 * @var string                 $showName      Should I display the full name?
 * @var string                 $showID        Should I display the numeric user ID?
 * @var string                 $showAvatar    Should I display the avatar of the user?
 * @var string                 $showLink      Should I display a link?
 * @var string                 $linkURL       What link should I display? Default is com_users edit page (backend only).
 * @var string                 $avatarMethod  Method to display an avatar: gravatar | plugin
 * @var string                 $avatarSize    Size [pixels] of the avatar. Avatars are square. Size 64 means 64x64 px.
 * @var string                 $class         Extra class to append
 *
 * Variables made automatically available to us by FOF:
 *
 * @var \FOF30\View\DataView\Raw $this
 */

use FOF30\Html\FEFHelper\BrowseView;global $akeebaSubsShowUserCache;

if (!isset($akeebaSubsShowUserCache))
{
    $akeebaSubsShowUserCache = [];
}

// Get field parameters
$defaultParams = [
	'id'           => '',
	'showUsername' => true,
	'showEmail'    => true,
	'showName'     => true,
	'showID'       => true,
	'showAvatar'   => true,
	'showLink'     => true,
	'linkURL'      => null,
	'avatarMethod' => 'gravatar',
	'avatarSize'   => 64,
	'class'        => '',
];

foreach ($defaultParams as $paramName => $paramValue)
{
	if (!isset(${$paramName}))
	{
		${$paramName} = $paramValue;
	}
}

unset($defaultParams, $paramName, $paramValue);

// Initialization
$value = $item->getFieldValue($field);
$key   = is_numeric($value) ? $value : 'empty';

// Get the user
if (!array_key_exists($key, $akeebaSubsShowUserCache))
{
	$akeebaSubsShowUserCache[$key] = $this->getContainer()->platform->getUser($value);
}

$user = $akeebaSubsShowUserCache[$key];

// Get the field parameters
if ($avatarMethod)
{
	$avatarMethod = strtolower($avatarMethod);
}

if (!$linkURL && $this->getContainer()->platform->isBackend())
{
	$linkURL = 'index.php?option=com_users&task=user.edit&id=[USER:ID]';
}
elseif (!$linkURL)
{
	// If no link is defined in the front-end, we can't create a default link. Therefore, show no link.
	$showLink = false;
}

// Post-process the link URL
if ($showLink)
{
	$replacements = array(
		'[USER:ID]'			 => $user->id,
		'[USER:USERNAME]'	 => $user->username,
		'[USER:EMAIL]'		 => $user->email,
		'[USER:NAME]'		 => $user->name,
	);

	foreach ($replacements as $key => $value)
	{
		$linkURL = str_replace($key, $value, $linkURL);
	}

	$linkURL = BrowseView::parseFieldTags($linkURL, $item);
}

// Get the avatar image, if necessary
$avatarURL = '';

if ($showAvatar)
{
	$avatarURL = '';

	if ($avatarMethod == 'plugin')
	{
		// Use the user plugins to get an avatar
		$this->getContainer()->platform->importPlugin('user');
		$jResponse = $this->getContainer()->platform->runPlugins('onUserAvatar', array($user, $avatarSize));

		if (!empty($jResponse))
		{
			foreach ($jResponse as $response)
			{
				if ($response)
				{
					$avatarURL = $response;
				}
			}
		}
	}

	// Fall back to the Gravatar method
	if (empty($avatarURL))
	{
		$md5 = md5($user->email);

		$avatarURL = 'https://secure.gravatar.com/avatar/' . $md5 . '.jpg?s='
			. $avatarSize . '&d=mm';
	}
}

?>
<div id="{{ $id }}" class="{{ $class }}">
    @if($showAvatar)
        <img src="{{ $avatarURL }}" alt="{{ $showName ? $user->name : ($showUsername ? $user->username : '') }}" align="left" class="fof-usersfield-avatar" />
    @endif
    @if($showLink)
        <a href="{{ $linkURL }}">
    @endif
    @if($showUsername)
        <span class="fof-usersfield-username">
            {{{ $user->username }}}
        </span>
    @endif
    @if($showID)
        <span class="fof-usersfield-id">
            {{{ $user->id }}}
        </span>
    @endif
    @if($showName)
        <span class="fof-usersfield-name">
            {{{ $user->name }}}
        </span>
    @endif
    @if($showEmail)
        <span class="fof-usersfield-email">
            {{{ $user->email }}}
        </span>
    @endif
    @if($showLink)
        </a>
    @endif
</div>
