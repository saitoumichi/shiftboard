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

/**
 * A Fuel Specific extension of the PHPUnit TestCase.  This will
 * be used for custom functionality in the future.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	// backward compatibility with PHPUnit < v6
    public function expectException($exception)
    {
        self::setExpectedException($exception);
    }
}
