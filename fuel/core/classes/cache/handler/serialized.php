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

namespace Fuel\Core;

class Cache_Handler_Serialized implements \Cache_Handler_Driver
{
	public function readable($contents)
	{
		return unserialize($contents);
	}

	public function writable($contents)
	{
		return serialize($contents);
	}

}
