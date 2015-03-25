<?php

	/**
	 * Simple object to represent an incoming HTTP request to the application.
	 *
	 * These are built from the PHP superglobal $_ arrays by the HttpCore factory class.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Request {

		/**
		 * The GET parameters
		 */
		public $queryStringParams;

		/**
		 * The POST parameters
		 */
		public $postParams;

		/**
		 * Files
		 */
		public $files;

 		/**
 		 * The HTTP verb (GET, POST, PUT, DELETE) used to make this request
 		 */
		public $method;

		/**
		 * The URI path part of the request.
		 */
		public $path;

		/**
		 * Whether or not this request was made via XHR
		 */
		public $ajax;

		/**
		 * Whether or not this request was made with HTTPS
		 */
		public $https;

		/**
		 * Gets $_GET and $_POST input from the request by key or null if it isn't there.
		 *
		 * @param String $key The key to search for
		 * @param String $type Search the 'get', 'post' and 'file' params or all of them.  Defaults to all.
		 * @return Mixed The value if found or null if not.
		 */
		public function get($key, $type = 'all') {
			if (strtolower($type) != 'all' && strtolower($type) != 'get' && strtolower($type) != 'post' && strtolower($type) != 'file') {
				throw new JiaoyuException("Invalid input type passed: $type.  Must be 'get', 'post' or 'all'");
			}

			if (strtolower($type) == 'get') {
				if (!empty($this->queryStringParams[$key])) {
					return $this->queryStringParams[$key];
				} else {
					return null;
				}
			}

			if (strtolower($type) == 'post') {
				if (!empty($this->postParams[$key])) {
					return $this->postParams[$key];
				} else {
					return null;
				}
			}

			if (strtolower($type) == 'file') {
				if (!empty($this->files[$key])) {
					return $this->files[$key];
				} else {
					return null;
				}
			}

			// NOTE - this favors $_GET over $_POST and then $_POST over $_FILE!  If the key exists
			// all of them, you'll always get the querystring param back.
			if (strtolower($type) == 'all') {
				if (!empty($this->queryStringParams[$key])) {
					return $this->queryStringParams[$key];
				} else if (!empty($this->postParams[$key])) {
					return $this->postParams[$key];
				} else if (!empty($this->files[$key])) {
					return $this->files[$key];
				} else {
					return null;
				}
			}
		}
	}
