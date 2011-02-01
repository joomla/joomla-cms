<?php

abstract class JUnitHelper
{
	public static function normalize($path)
	{
		return strtr($path, '\\', '/');
	}
}
