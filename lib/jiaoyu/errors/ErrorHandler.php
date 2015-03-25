<?php

	/**
	 * Global error handler for the application.  This thing will deal with any uncaught exceptions,
	 * and depending on whether or not the app is in debug mode, will print more or less useful
	 * information.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class ErrorHandler {

		/**
		 * Handle exceptions thrown in the application that weren't caught by something else.
		 *
		 * @param Exception $e The uncaught exception.
		 */
		public static function handleExceptions($e) {

			if (get_class($e) == "JiaoyuException" || get_parent_class($e) == 'JiaoyuException') {
				if ($e->isFatal) {
					error_log($e->getMessage()); //for now
					self::errorPage($e, null);
				} else {
					error_log($e->getMessage());
					// keep on keeping on ?
				}
			} else {
				error_log($e->getMessage());
				self::errorPage($e, null);
			}

		}

		/**
		 * Handle standard PHP errors, not exceptions.  This sadly won't be called
		 * in the event of certain types of errors (PHP fatals) but it will be called
		 * for some so it's worth keeping around.
		 *
		 * @param Integer $errno The numberical code of the error that occured
		 * @param String $errorstr The string of the error message.
		 * @param String $errfile The php script the error occurred in
		 * @param Integer $errline The line where the error occurred
		 */
		public static function handleErrors($errno = "", $errstr = "", $errfile = "", $errline = "") {
			$errorArray['errno'] = $errno;
			$errorArray['errstr'] = $errstr;
			$errorArray['errfile'] = $errfile;
			$errorArray['errline'] = $errline;

			self::errorPage(null, $errorArray);
		}

		/**
		 * Send user to error page and then die, preventing any more execution from happening.
		 *
		 * Depending on whether or not debug is enabled, we'll use different pages.
		 *
		 * @param Exception $e - The uncaught exception to display on the error page.
		 * @param Array $errArray - The
		 */
		private static function errorPage($e, $errArray) {

			$errorPageParams = array(
				"exception" => $e,
				"errors" => $errArray,
			);


			if (JiaoyuCore::config('debug_mode')) {
				// Send user to full debug page.
				$exceptionView = View::make('debugerrorpage', $errorPageParams)->render();
				Response::html($exceptionView, 500);
				die();
			} else {
				// Has the user set up their own error page?
				$customErrorPage = JiaoyuCore::config('custom_error_page');
				if ($customErrorPage) {
					$customErrorView = View::make($customErrorPage, $errorPageParams)->render();
					Response::html($customErrorView, 500);
					die();
				} else {
					$standardErrorView = View::make('standarderrorpage', $errorPageParams)->render();
					Response::html($standardErrorView, 500);
					die();
				}
			}
		}
	}
