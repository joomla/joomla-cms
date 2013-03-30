<?php
class ModRandomImageGlue
{
	static $baseCalls;
	static $baseArgs;
	static $baseReturn;
	static $strposCalls;
	static $strposArgs;
	static $strposReturn;
	static $_Calls;
	static $_Args;
	static $_Return;
	static $getLayoutPathCalls;
	static $getLayoutPathArgs;
	static $getLayoutPathReturn;
	
	static function base()
	{
		self::$baseArgs = func_get_args();
		self::$baseCalls += 1;
		return self::$baseReturn;
	}
	static function strpos()
	{
		self::$strposArgs = func_get_args();
		self::$strposCalls += 1;
		return self::$strposReturn;
	}
	static function _()
	{
		self::$_Args = func_get_args();
		self::$_Calls += 1;
		return self::$_Return;
	}
	static function getLayoutPath()
	{
		self::$getLayoutPathArgs = func_get_args();
		self::$getLayoutPathCalls += 1;
		return self::$getLayoutPathReturn;
	}
}
?>