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

return array(
    'name'   => 'Role name',
    'filter' => 'Special permissions',

    'permissions' => array(
        ''  => 'None',
        'A' => 'Allow all access',
        'D' => 'Deny all access',
        'R' => 'Revoke assigned permissions',
	),
);
