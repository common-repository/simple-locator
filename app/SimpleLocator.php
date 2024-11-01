<?php 
/**
* Static Wrapper for Bootstrap Class
* Prevents T_STRING error when checking for 5.3.2
*/
class SimpleLocator 
{
	public static function init()
	{
		// dev/live
		global $simple_locator_env;
		$simple_locator_env = 'live';

		global $simple_locator_version;
		$simple_locator_version = '2.0.3';

		$app = new SimpleLocator\Bootstrap;
	}
}