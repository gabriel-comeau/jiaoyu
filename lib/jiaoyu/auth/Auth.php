<?php

	/**
	 * Handles app-wide authentication for the framework.  It is enabled/disabled via config.json
	 * and is based off of a model class.  It uses a user-defined model class (should extend Atlas)
	 * and needs to be told in advance which columns to use as the identifier (user name) and password.
	 *
	 * All passwords are stored hashed via the php 5.5 hashing API.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Auth {

		/**
		 * Whether or not the Auth system is being used.  (Defined in config.json)
		 */
		private static $active;

		/**
		 * If there's a logged in user, it's model object will live here!
		 */
		private static $user;

		/**
		 * Performs a login attempt - will log out any currently logged in user for this session
		 * if called while in that state.
		 *
		 * @param String $username Identifying string
		 * @param String $password Cleartext password
		 * @return Boolean Whether or not the attempt was successful
		 */
		public static function login($username, $password) {
			if (!self::$active) {
				throw new AuthException("Auth module being called but not enabled in config!");
			}

			if (self::$user != null) {
				// Let's log out first, to be sure!
				self::logout();
			}

			$user = self::checkAuth($username, $password);
			if ($user) {
				self::$user = $user;
				self::setStatusToSession();
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Logs user out.
		 */
		public static function logout() {
			if (!self::$active) {
				throw new AuthException("Auth module being called but not enabled in config!");
			}

			self::$user = null;
			self::clearStatusFromSession();
		}

		/**
		 * If there's a currently logged in user, get it.  If not, returns null.
		 *
		 * @return Mixed Atlas-extending user object if logged in, null otherwise.
		 */
		public static function getUser() {
			if (!self::$active) {
				throw new AuthException("Auth module being called but not enabled in config!");
			}

			return self::$user;
		}

		/**
		 * To be called by JiaoyuCore on app startup.  Checks the session for an already logged in
		 * user and loads up the model object into memory if applicable.
		 */
		public static function init() {
			if (JiaoyuCore::config('use_auth')) {
				self::$active = true;
				self::getStatusFromSession();
			} else {
				self::$active = false;
			}
		}

		/**
		 * Checks the credentials against the database.  Returns an object of the user-defined User
		 * model if the credentials checkout and null otherwise.
		 *
		 * @param String $username Identifying column (could be email or whatever)
		 * @param String $password Cleartext password
		 * @return Atlas The model object for the user or null if no match.
		 */
		private static function checkAuth($username, $password) {

			// First we need the username - this should be on a unique field but we'll test
			// for ambiguous results and exception out if it happens.
			$userClass = JiaoyuCore::config('auth_model_class');
			$users = $userClass::where(JiaoyuCore::config('auth_model_user_id_column'), '=', $username)->execute();

			if (count($users)) {
				if (count($users) > 1) {
					// More than one user, no good!
					throw new AuthException("More than one user found for credentials - too ambiguous!");
				} else {
					// OK we have a single user who matches the username
					$potentialUser = $users[0];

					// Find the hashed password stored in the DB by the column defined in the config
					$passwordCol = JiaoyuCore::config('auth_model_password_column');
					$hashed = $potentialUser->{$passwordCol};

					// Check if there's a match - return the user if there is and a big fat null if not
					if (CryptoUtils::verify($password, $hashed)) {
						return $potentialUser;
					} else {
						return null;
					}
				}
			} else {
				// No users exist for this username!
				return null;
			}
		}

		/**
		 * Called by init - when a request is fired, this checks if the session contains
		 * a user id and if it does attempts to build a user object for it.  If it can,
		 * the auth class holds a reference to the logged in user's object and considers
		 * the user as "logged in".
		 */
		private static function getStatusFromSession() {
			if (Session::get('jiaoyu_authed_user_id')) {
				$userClass = JiaoyuCore::config('auth_model_class');
				self::$user = $userClass::one(Session::get('jiaoyu_authed_user_id'));
				if (!self::$user) {
					throw new AuthException("Attempted to load non-existing user from session's user id");
				}
			}
		}

		/**
		 * Once credentials have been checked, the user id is saved to the session so until logged
		 * out they will be considered logged in for every subsequent request.
		 */
		private static function setStatusToSession() {
			Session::set('jiaoyu_authed_user_id', self::$user->id);
		}

		/**
		 * Removes status from session so on the next request the user won't be
		 * considered as logged in.
		 */
		private static function clearStatusFromSession() {
			Session::delete('jiaoyu_authed_user_id');
		}
	}
