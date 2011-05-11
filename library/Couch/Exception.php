<?php
/**
 * PHP-on-Couch
 *
 * @copyright  Copyright (C) 2009  Mickael Bailly
 * @license    GNU General Public License V2 or later
 * @category   Couch
 * @package    Couch
 */

/**
 * customized Exception class for CouchDB errors
 *
 * this class uses : the Exception message to store the HTTP message sent by the server
 * the Exception code to store the HTTP status code sent by the server
 * and adds a method getBody() to fetch the body sent by the server (if any)
 * @category   Couch
 * @package    Couch
 */
class Couch_Exception extends Exception
{
	// couchDB response once parsed
	protected $couch_response = array();

	/**
	 *class constructor
	 *
	 * @param string $raw_response HTTP response from the CouchDB server
	 */
	function __construct($raw_response)
	{
		$this->couch_response = Couch_Db::parseRawResponse($raw_response);
		if (is_object($this->couch_response['body']) and isset($this->couch_response['body']->reason)) {
			$message = $this->couch_response['status_message'] . ' - ' . $this->couch_response['body']->reason;
		}
		else
		{
			$message = $this->couch_response['status_message'];
		}
		parent::__construct($message, $this->couch_response['status_code']);
	}

	/**
	 * returns CouchDB server response body (if any)
	 *
	 * if the response's "Content-Type" is set to "application/json", the
	 * body is json_decode()d
	 *
	 * @return string|object|null CouchDB server response
	 */
	function getBody()
	{
		return $this->couch_response['body'];
	}
}
