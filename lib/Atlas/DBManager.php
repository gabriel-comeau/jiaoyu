<?php

	/**
	 * Manages the app-wide connection to the mysql database.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class DBManager {

		/**
		 * The PDO database handle
		 */
		private static $dbh;

		/**
		 * Read the config and load up the database handle based on it.
		 *
		 * @param String $host The database host name
		 * @param String $user The database user name
		 * @param String $pass The database password
		 * @param String $name The database name
		 */
		public static function init($host, $user, $pass, $name) {
			try {
				self::$dbh = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
			} catch (Exception $e) {
				throw new AtlasException("Database connection error: ".$e->getMessage());
			}
		}

		/**
		 * Gets the mysql database handle
		 *
		 * @return PDO The handle
		 */
		public static function getInstance() {
			return self::$dbh;
		}
	}
