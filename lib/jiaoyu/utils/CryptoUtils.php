<?php

	/**
	 * Utility methods for crypto operations.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class CryptoUtils {

		/**
		 * Turns a cleartext string into a hashed password.
		 *
		 * NOTE:  This behaviour is totally reliant on the PHP 5.5 simplified hashing API.
		 *        The only reason I bothered wrapping it was because I would like the framework
		 *        to also function on 5.4 and that means I'll want to re-implement some of this
		 *        on my own (or borrow IRC Maxell's lib)
		 *
		 * @param String $clearText The text to hash
		 * @return String The hashed clear text
		 */
		public static function hash($clearText) {
			return password_hash($clearText, PASSWORD_BCRYPT);
		}

		/**
		 * Checks a cleartext password against a hashed one to see if they match.
		 *
		 * See the notes in pwHash()
		 *
		 * @param String $clearText
		 * @param String $hash
		 * @return Boolean Whether or not they match
		 */
		public static function verify($clearText, $hash) {
			return password_verify($clearText, $hash);
		}
	}
