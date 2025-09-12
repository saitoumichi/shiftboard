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

abstract class HttpException extends \FuelException
{
	/**
	 * Must return a response object for the handle method
	 *
	 * @return  Response
	 */
	abstract protected function response();

	/**
	 * When this type of exception isn't caught this method is called by
	 * Errorhandler::exception_handler() to deal with the problem.
	 */
	public function handle()
	{
		// get the exception response
		$response = $this->response();

		// send the response out
		$response->send(true);
	}
}
