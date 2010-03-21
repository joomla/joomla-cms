<?php

class JErrorInspector extends JError
{
	public static function inspectLevels()
	{
		return self::$levels;
	}

	public static function inspectHandlers()
	{
		return self::$handlers;
	}

	public static function inspectStack()
	{
		return self::$stack;
	}

	public static function manipulateLevels($levels)
	{
		self::$levels = $levels;
	}

	public static function manipulateHandlers($handlers)
	{
		self::$handlers = $handlers;
	}

	public static function manipulateStack($stack)
	{
		self::$stack = $stack;
	}
}
