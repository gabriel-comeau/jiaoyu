<?php

	/**
	 * Handles class loading for the framework.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class AutoLoader {

		/**
		 * Maintain the classname=>filepath list in memory so
		 * the filesystem doesn't need to get involved more than once
		 * per request!
		 */
		private static $classNameToFilePathList;

		/**
		 * Registers the spl_autoloader so that trying to load up a missing class
		 * will call Autoloader::doLoad() which will recursively scan the project's
		 * filesystem to find any .php files with the same name as the class.
		 *
		 * Loading classes this way is case sensitive!
		 *
		 */
		public static function init() {
			// Since the function helper library is not a class that can be autoloaded,
			// let's hard-include it here.
			require_once(JIAOYU_PROJECT_HOME."/lib/jiaoyu/Helpers.php");

			// TODO: handle user-written helper libs in app/libraries

			spl_autoload_register("AutoLoader::doLoad");
		}

		/**
		 * Performs the class load for the framework.
		 *
		 * @param String $className The name of the class to load
		 * @return void
		 */
		public static function doLoad($className) {

			//TODO: disable this caching altogether according to a config directive
			if (self::$classNameToFilePathList == null) {

				$rawPhpFiles = self::findFiles(JIAOYU_PROJECT_HOME, ".php");
				self::$classNameToFilePathList = array();

				// Have to go from full path to file to file without extension
				foreach($rawPhpFiles as $fullPath) {
					$splitPath = explode('/', $fullPath);
					$fileName = $splitPath[sizeof($splitPath) -1];
					$noExtension = explode('.', $fileName);
					self::$classNameToFilePathList[$noExtension[0]] = $fullPath;
				}

			}

			if (array_key_exists($className, self::$classNameToFilePathList)) {
				include(self::$classNameToFilePathList["$className"]);
			} else {
				throw new ClassLoadException("Can't find $className!");
			}

		}

		/**
		 * Recursively traverse the filesystem, starting at the initially given path
		 * and find any files ending in the given extension.
		 *
		 * @param String $path The directory to begin searching in
		 * @param String $extension The desired extension to look for files with
		 * @return Array A filename => full absolute path to file array of the resulting found files.
		 */
		private static function findFiles($path, $extension) {
			$scanList = scandir($path);
			$filesList = array();

			foreach ($scanList as $fileName) {
				if ($fileName == '.' || $fileName == '..' || $fileName == '.git') {
					continue;
				}

				if (is_dir($path.'/'.$fileName)) {
					$filesList = array_merge($filesList, self::findFiles($path.'/'.$fileName, $extension));
				} elseif (substr($fileName, -(strlen($extension))) == $extension) {
					$filesList[] = $path.'/'.$fileName;
				}
			}
			return $filesList;
		}
	}
