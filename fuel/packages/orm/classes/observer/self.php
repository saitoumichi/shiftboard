<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010-2025 Fuel Development Team
 * @link       https://fuelphp.com
 */

namespace Orm;

/**
 * Dummy observer class, which allows you to define observer methods in the
 * model itself.
 */
class Observer_Self
{
	/**
	 * Get notified of an event
	 *
	 * @param  Model   $instance
	 * @param  string  $event
	 */
	public static function orm_notify(Model $instance, $event)
	{
		if (method_exists($instance, $method = '_event_'.$event))
		{
			call_user_func(array($instance, $method));
		}
	}
}
