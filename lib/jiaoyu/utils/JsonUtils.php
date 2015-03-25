<?php

	/**
	 * Utility class to handle encoding/decoding JSON and getting the correct errors
	 * from PHP's weird JSON api.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class JsonUtils {

		/**
		 * Wraps json_decode to be able to get the errors in human readable format.
		 *
		 * @param String $jsonString The raw JSON to be decoded.
		 * @return Object The json object
		 * @throws JsonException - If the json cannot be parsed, we throw an exception.
		 */
		public static function decode($jsonString) {
			$decoded = json_decode($jsonString);
			if (!$decoded) {
				throw new JsonException("Couldn't parse string as JSON: ".self::getJsonErrorMessage(json_last_error()));
			} else {
				return $decoded;
			}
		}

		/**
		 *Wraps json_encode to be able to get the errors in human readable format.
		 *
		 * @param Mixed PHP value to be encoded as JSON.
		 * @return String The JSON string
		 * @throws JsonException - If the json cannot be created, we throw an exception.
		 */
		public static function encode($toEncode) {
			$encoded = json_encode($toEncode);
			if (!$encoded) {
				throw new JsonException("Couldn't convert value to JSON: ".self::getJsonErrorMessage(json_last_error()));
			} else {
				return $encoded;
			}
		}

		/**
		* Translate json_decode/json_encode error codes to string error messages
		*
		* @param int $jsonError Error code to check string for
		* @return string The error message or empty string if there was no errror
		*/
		private static function getJsonErrorMessage($jsonError) {
			switch ($jsonError) {
				case JSON_ERROR_NONE:
					return "";
					break;
				case JSON_ERROR_DEPTH:
					return 'Maximum stack depth exceeded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					return 'Underflow or the modes mismatch';
					break;
				case JSON_ERROR_CTRL_CHAR:
					return 'Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					return 'Syntax error, malformed JSON';
					break;
				case JSON_ERROR_UTF8:
					return 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				default:
					return 'Unknown error';
					break;
			}
			return "";
		}

	}
