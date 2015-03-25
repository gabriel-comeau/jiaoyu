<?php

	/**
	 * Handle user sessions, cookies and "flash" messages.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Session {

		/**
		 * All cookies - those that came in from the web request and those which were
		 * added during the request's lifetime.
		 */
		private static $cookies = array();

		/**
		 * Everything in session, minus the flash messags which are parsed out
		 */
		private static $session = array();

		/**
		 * The flash messages that came in from the request.
		 */
		private static $oldFlash = array();

		/**
		 * The flash messages which we will add to the session in the response.
		 */
		private static $newFlash = array();

		/**
		 * Cookies which were added during the request lifetime.  This includes cookies
		 * to be deleted since they need to be sent back to the client.
		 */
		private static $newCookies = array();

		// -------------------------------- Cookie methods -------------------------------- //

		/**
		 * Gets cookies which were added this session (that need to be sent to the client)
		 *
		 * @return Array An array of Cookie objects
		 */
		public static function getNewCookies() {
			return self::$newCookies;
		}

		/**
		 * Gets the cookie's value by name.  Returns null if there is no such cookie.
		 *
		 * @param String $key - The name of the cookie
		 * @return Mixed $value - The value of the cookie
		 */
		public static function getCookie($key) {
			if (isset(self::$cookies[$key]->value)) {
				return self::$cookies[$key]->value;
			} else {
				return null;
			}
		}

		/**
		 * Create a new cookie and set it to be saved with the response.
		 *
		 * The cookie will still be accessible during the request this was called on as well.
		 *
		 * @param String $name The unique key and name of the cookie
		 * @param Mixed $value The value stored by the cookie
		 * @param Integer $expiry Timestamp when this cookie expires.  Defaults to 24 hours in the future
		 * @param String $path The folder path for the cookie.  Defaults to "/"
		 * @param String $domain The domain to set the cookie on.  Defaults to server name.
		 */
		public static function addCookie($name, $value, $expiry = ONE_DAY_FROM_NOW, $path = "/", $domain = null) {
			if (!$domain) {
				$domain = HttpCore::getSeverDomain();
			}

			$cookie = self::createCookie($name, $value, $expiry, $path, $domain);
			self::setCookie($cookie);
		}

		/**
		 * Deletes an existing cookie.
		 *
		 * Cookies get deleted in a funny way - you have to send one with a negative expiry date
		 * to the browser in order to for it to delete it on the next page load.
		 *
		 * @param String $name The cookie's key.
		 */
		public static function deleteCookie($name) {
			if (!empty(self::$cookies[$name])) {
				$cookie = self::$cookies[$name];
				$cookie->expiry = ONE_DAY_AGO;
				$cookie->value = "";

				self::setCookie($cookie);
			}
		}

		/**
		 * When a new request comes in, this creates any cookies that were sent along to it.  The
		 * difference between this and addCookie() is that this one doesn't set the cookies to be
		 * added to the response since the client already has them.
		 *
		 * @param String $name The unique key and name of the cookie
		 * @param Mixed $value The value stored by the cookie
		 * @param Integer $expiry Timestamp when this cookie expires.  Defaults to 24 hours in the future
		 * @param String $path The folder path for the cookie.  Defaults to "/"
		 * @param String $domain The domain to set the cookie on.  Defaults to server name.
		 */
		public static function addRequestCookie($name, $value, $expiry = ONE_DAY_FROM_NOW, $path = "/", $domain = null) {
			if (!$domain) {
				$domain = HttpCore::getServerDomain();
			}

			$cookie = self::createCookie($name, $value, $expiry, $path, $domain);
			self::setCookieFromRequest($cookie);
		}

		/**
		 * Create a new cookie object.
		 *
		 * @param String $name The unique key and name of the cookie
		 * @param Mixed $value The value stored by the cookie
		 * @param Integer $expiry Timestamp when this cookie expires.  Defaults to 24 hours in the future
		 * @param String $path The folder path for the cookie.  Defaults to "/"
		 * @param String $domain The domain to set the cookie on.  Defaults to server name.
		 * @return Cookie The newly created cookie.
		 */
		private static function createCookie($name, $value, $expiry, $path, $domain) {
			$cookie = new Cookie();
			$cookie->name = $name;
			$cookie->value = $value;
			$cookie->expiry = $expiry;
			$cookie->path = $path;
			$cookie->domain = $domain;

			return $cookie;
		}

		/**
		 * Sets a new or changed cookie into the array.
		 *
		 * @param Cookie $cookie - The cookie to set.
		 */
		private static function setCookie($cookie) {
			self::$cookies[$cookie->name] = $cookie;
			self::$newCookies[$cookie->name] = $cookie;
		}

		/**
		 * Sets an existing cookie (from the request) into the array.  This one won't
		 * be written out in the response unless it gets changed.
		 *
		 * @param Cookie $cookie - The cookie to set.
		 */
		private static function setCookieFromRequest($cookie) {
			self::$cookies[$cookie->name] = $cookie;
		}


		// -------------------------------- Flash var methods -------------------------------- //


		/**
		 * Returns a flash message which was sent in the previous response.  It will not fetch
		 * any flash messages which were added during the same request.
		 *
		 * @param String $key The key of the message.
		 * @param Mixed The value or null if it doesn't exist
		 */
		public static function getFlash($key) {
			if (isset(self::$oldFlash[$key])) {
				return self::$oldFlash[$key];
			} else {
				return null;
			}
		}

		/**
		 * Get the entire array of flash messages from the previous response.  It will not fetch
		 * any messages which were added during the current request cycle, only the previous one.
		 *
		 * @return Array The flash messages
		 */
		public static function getAllFlash() {
			return self::$oldFlash;
		}

		/**
		 * Adds a flash message to be recieved during the next req/resp cycle.
		 *
		 * @param String $key The key of the message
		 * @param Mixed $val The value of the message
		 */
		public static function setFlash($key, $val) {
			self::$newFlash[$key] = $val;
		}


		// -------------------------------- Normal session methods -------------------------------- //


		/**
		 * Sets a value into the session
		 *
		 * @param String $key The key to save
		 * @param Mixed $val The value to save
		 */
		public static function set($key, $val) {
			self::$session[$key] = $val;
		}

		/**
		 * Gets a value stored in the session by key
		 *
		 * @param String $key Key to search for
		 * @return Mixed The value if it exists or null
		 */
		public static function get($key) {
			if (isset(self::$session[$key])) {
				return self::$session[$key];
			} else {
				return null;
			}
		}

		/**
		 * Removes a value from the session by it's key.
		 *
		 * @param String $key The session key to unset
		 */
		public static function delete($key) {
			unset(self::$session[$key]);
		}

		/**
		 * Go through the local session array and sync it with the real session array before
		 * it gets written to.
		 *
		 */
		public static function writeSession() {
			// Sort out any differences between self::$session and $_SESSION
			if (!empty(self::$session) || !empty($_SESSION) || !empty(self::$newFlash)) {

				// Wipe the existing $_SESSION superglobal
				foreach ($_SESSION as $k => $v) {
					unset($_SESSION[$k]);
				}

				// Set the values based on what's in $_SESSION
				foreach  (self::$session as $k => $v) {
					$_SESSION[$k] = $v;
				}

				// Write the flash values
				foreach (self::$newFlash as $k => $v) {
					$flashKey = JIAOYU_FLASH_KEY.$k;
					$_SESSION[$flashKey] = $v;
				}
			}
		}

		/**
		 * Sets up the session / flash vars at the start of a request so anything from
		 * that request is available through its lifecycle.
		 */
		public static function initSession() {
			if (!empty($_SESSION)) {
				foreach ($_SESSION as $key => $val) {
					// Check if this is flash message
					if (preg_match('/^'.JIAOYU_FLASH_KEY.'/', $key)) {
						$flashKey = preg_replace('/^'.JIAOYU_FLASH_KEY.'/', '', $key);
						self::$oldFlash[$flashKey] = $val;
					} else {
						// Just a regular session variable
						self::$session[$key] = $val;
					}
				}
			}
		}

	}
