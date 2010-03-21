<?php

class JHtmlMockTest
{
	public static $arguments = array();

	public static $returnValue;

	public static function method1()
	{
		if(!isset(self::$arguments)) {
			self::$arguments = array(func_get_args());
		} else {
			self::$arguments[] = func_get_args();
		}

		if(isset(self::$returnValue)) {
			return self::$returnValue;
		} else {
			return 'JHtml Mock Called';
		}
	}
}
