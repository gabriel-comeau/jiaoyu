<?php

	/**
	 * Represents an HTTP response - what is sent back from the server to the client's browser.
	 *
	 * This handles the headers and the content - and as a single point of network communication
	 * is the only place in the framework where "echo" gets used - that way there is certainty
	 * that no content gets sent to the client before headers can be set here.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Response {

		/**
		 * HTTP Status code to return with the response.
		 */
		private $statusCode;

		/**
		 * Headers to be sent in the response before sending the
		 */
		private $headers;

		/**
		 * Content to be sent to client.
		 */
		private $payload;

		/**
		 * Echo the response's payload out at the end?
		 *
		 * Should be false if you want to perform a redirect.
		 */
		private $sendPayload = true;

		/**
		 * Shortcut method for quickly sending html content to the client.
		 *
		 * @param String $payload The text to send to the client (often a rendered view object)
	     * @param Integer $code The HTTP status code to return.
		 */
		public static function html($payload, $code = 200) {
			$response = new self();
			$response->statusCode = $code;
			$response->payload = $payload;
			$response->headers = array(
				'Content-Type: text/html',
			);

			self::send($response);
		}

		/**
		 * Shortcut method for quickly sending plain text content
		 *
		 * @param String $payload The text to send to the client
	     * @param Integer $code The HTTP status code to return.
		 */
		public static function text($payload, $code = 200) {
			$response = new self();
			$response->statusCode = $code;
			$response->payload = $payload;
			$response->headers = array(
				'Content-Type: text/plain',
			);
			self::send($response);
		}

		/**
		 * Shortcut method for quickly sending json content to the client.
		 *
		 * @param Mixed $payload Data to be json encoded
		 * @param Integer $code The HTTP status code to return.
		 */
		public static function json($payload, $code = 200) {
			$response = new self();
			$response->statusCode = $code;
			$response->payload = JsonUtils::encode($payload);
			$response->headers = array(
				'Content-Type: application/json',
			);

			self::send($response);
		}

		/**
		 * Performs a redirect to another url (which can be generated with the route() helper)
		 *
		 * @param String $url The url to redirect to
		 * @param Integer $code The HTTP code to give.  Defaults to 302 but a 301 can be used too.
		 */
		public static function redirect($url, $code = 302) {
			$response = new self();
			$response->statusCode = $code;
			$response->payload = null;
			$response->headers = array(
				"Location: $url",
			);
			$response->sendPayload = false;

			self::send($response, true);
		}

		/**
		 * Handles 404 situation when a route can't be found for a request
		 *
		 * @param Request $request The unmatched request
		 */
		public static function fourOhFour(Request $request) {
			$response = new self();
			$response->statusCode = 404;
			$response->headers = array(
				'Content-Type: text/html',
			);

			$viewParams = array('request' => $request);

			if (JiaoyuCore::config('custom_404_page')) {
				$response->payload = View::make(JiaoyuCore::config('custom_404_page'), $viewParams)->render();
			} else {
				$response->payload = View::make('standard404page', $viewParams)->render();
			}

			self::send($response);
		}

		/**
		 * Set all of the headers and then deliver the content to the client.
		 *
		 * @param Response $resp A response object
		 */
		public static function send(Response $resp) {
			Session::writeSession();
			session_write_close();

			// Send any new cookies that need sending
			foreach (Session::getNewCookies() as $key => $cookie) {
				setcookie($cookie->name, $cookie->value, $cookie->expiry, $cookie->path, $cookie->domain);
			}

			foreach ($resp->headers as $header) {
				header($header);
			}

			http_response_code($resp->statusCode);

			// Sometimes you want to redirect so you don't want to be sending any content!
			if (!$resp->sendPayload) {
				die();
			} else {
				echo $resp->payload;
			}
		}
	}
