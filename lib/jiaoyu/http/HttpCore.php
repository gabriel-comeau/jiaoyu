<?php

	/**
	 * HttpCore deals with raw HTTP stuff - especially the PHP $_ superglobal arrays so that
	 * no other code should have to touch it.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class HttpCore {

		/**
		 * This function reads the php global arrays and creates a Request object from
		 * them.
		 *
		 * @return Request
		 */
		public static function buildRequest() {

			$request = new Request();

			// Set user input.

			$request->queryStringParams = array();
			if (!empty($_GET)) {
				foreach($_GET as $k => $v) {
					$request->queryStringParams[$k] = $v;
				}
			}

			$request->postParams = array();
			if (!empty($_POST)) {
				foreach($_POST as $k => $v) {
					$request->postParams[$k] = $v;
				}
			}

			$request->files = array();
			if (!empty($_FILES)) {
				foreach ($_FILES as $k => $v) {
					$request->files[$k] = $v;
				}
			}

			// Implicit trust is bad but PHP should definitely
			// always be returning certain values so we don't
			// bother with if (isset()).  If this fails that would
			// be the least of our concerns.
			$request->method = $_SERVER['REQUEST_METHOD'];

			// All we've really got for this is request_uri - but it includes
			// the query string so make sure to chop that crap.
			$uri = $_SERVER['REQUEST_URI'];
			$request->path = preg_replace("/\?.+$/", '', $uri);

			// Check if this request has an ajax header
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
				$request->ajax = true;
			} else {
				$request->ajax = false;
			}

			// Check if this request was made over https
			$request->https = false;
			if (self::getRequestScheme() == 'https') {
				$request->https = true;
			}

			return $request;
		}

		/**
		 * Get the base app url
		 *
		 * @return String The url
		 */
		public static function getBaseUrl() {
			$baseUrl = self::getRequestScheme()."://";
			$baseUrl .= self::getServerDomain();

			return $baseUrl;
		}

		/**
		 * Gets the domain for the app (used for cookies)
		 *
		 * @return String The domain
		 */
		public static function getServerDomain() {
			if (isset($_SERVER['HTTP_HOST'])) {
				return $_SERVER['HTTP_HOST'];
			} else {
				return $_SERVER['SERVER_NAME'];
			}
		}

		/**
		 * Setup the session static class based on incoming
		 * session and cookie super globals.
		 */
		public static function initSession() {
			session_start();
			Session::initSession();

			// Write any cookies to the Session holder as well
			if (!empty($_COOKIE)) {
				foreach ($_COOKIE as $key => $val) {
					Session::addRequestCookie($key, $val, null, null, null);
				}
			}
		}

		/**
		 * There doesn't appear to be a web-server independant way of getting the request
		 * scheme so here's a wrapper do check various possibilities out.
 		 *
 		 * @return String (either 'http' or 'https')
		 */
		private static function getRequestScheme() {
			$scheme = "http";
			if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
				$scheme = 'https';
			} else if (!empty($_SERVER['HTTPS'])) {
				if (strtolower($_SERVER['HTTPS']) != "off") {
					$scheme = 'https';
				}
			}

			return $scheme;
		}
	}
