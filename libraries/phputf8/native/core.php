<?php
if (class_exists('JLog'))
{
	JLog::add(
		sprintf(
			'Using the phputf8 library through files located in %1$s is deprecated, load the files from %2$s instead.',
			dirname(__DIR__),
			JPATH_LIBRARIES . '/vendor/joomla/string/src/phputf8'
		),
		JLog::WARNING,
		'deprecated'
	);
}

require_once JPATH_LIBRARIES . '/vendor/joomla/string/src/phputf8/native/core.php';
