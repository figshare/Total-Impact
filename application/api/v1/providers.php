<?php

require_once '../../library/restler/restler.php';

class Providers {
	function index() {
		throw new RestException(501, "Working on it!");
		return 42;
	}
}

