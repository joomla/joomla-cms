<?php

/*
 * Mock classes
 */
class JRegistry
{
	static $liveSite;

	function getValue($regpath, $default = null)
	{
		if ($regpath == 'config.live_site') {
			return self::$liveSite;
		}
		return $default;
	}

}

class JFactory
{
	function &getConfig($file = null, $type = 'PHP')
	{
		$config = new JRegistry();
		return $config;
	}
}