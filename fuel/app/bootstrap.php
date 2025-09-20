<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

// Bootstrap the framework - THIS LINE NEEDS TO BE FIRST!
require COREPATH.'bootstrap.php';

// Add framework overload classes here
\Fuel\Core\Autoloader::add_classes(array(
	// Example: 'View' => APPPATH.'classes/myview.php',
));

// Register the autoloader
\Fuel\Core\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
\Fuel\Core\Fuel::$env = \Fuel\Core\Arr::get($_SERVER, 'FUEL_ENV', \Fuel\Core\Arr::get($_ENV, 'FUEL_ENV', getenv('FUEL_ENV') ?: \Fuel\Core\Fuel::DEVELOPMENT));

// Initialize the framework with the config file.
\Fuel\Core\Fuel::init('config.php');

/* --- prevent View from being auto-escaped in templates --- */
\Fuel\Core\Config::set(
    "security.whitelisted_classes",
    array_unique(array_merge(
        (array) \Fuel\Core\Config::get("security.whitelisted_classes", []),
        ["Fuel\\Core\\View","Fuel\\Core\\ViewModel","Closure"]
    ))
);
