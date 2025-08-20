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
 * ViewModel, alias for Presenter, for BC purposes
 *
 * @package	    Fuel
 * @subpackage  Core
 * @category    Core
 */
abstract class Viewmodel extends \Presenter
{
	// namespace prefix
	protected static $ns_prefix = 'View_';
}
