<?php
class ModRandomImageTemplateGlue
{
	static $_Calls;
	static $_Args;
	static $_Return;

	static function _()
	{
		self::$_Args = func_get_args();
		self::$_Calls += 1;
		return self::$_Return;
	}
}
