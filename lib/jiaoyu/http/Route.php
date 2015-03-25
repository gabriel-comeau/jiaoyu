<?php

	/**
	 * Built from a json config file, a Route object represents a mapping between a URI and
	 * a controller class/method combo.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Route {

		/**
		 * Route's name - used an app-wide identifier for the route so you can find it to do
		 * things like redirects or building urls dynamically.
		 */
		public $name;

		/**
		 * The URI path this route matches.  It can be static like /foo/bar or have dynamic
		 * parameters like /blog/{post_permalink}.
		 */
		public $path;

		/**
		 * Name of the controller class which this route will instantiate and pass control of
		 * application to when matched.
		 */
		public $controller;

		/**
		 * Method name to execute in the controller class.
		 */
		public $action;

		/**
		 * An array of allowed HTTP methods (PUT, POST, DELETE, GET)
		 */
		public $methods;

		/**
		 * Private constructor, to be used by static factory
		 * method.
		 *
		 * @param String $name Route name
		 * @param String $path Uri (or pattern) to match the route to
		 * @param String $controller Name of controller class to call
		 * @param String $action Name of controller method to call
		 * @param Array $methods List of allowed HTTP methods
		 */
		private function __construct($name, $path, $controller,
			$action, $methods) {

			$this->name = $name;
			$this->path = $path;
			$this->controller = $controller;
			$this->action = $action;
			$this->methods = $methods;
		}


		/**
		 * Builds and returns a Route object from the JSON object
		 * read in from the routes config file.
		 *
		 * @param Object $jsonObject - Bare object read in from the json config file
		 * @return Route The constructed route
		 */
		public static function buildFromJson($jsonObject) {

			//TODO: might want to do some validation here so we can return more meaningful
			//      errors to a user who doesn't make a valid route file.
			//
			//      This will also allow us to build a "route checker" tool which can be
			//      used to ensure new route files are good before someone dumps a bad
			//      one into production and gets embarassed.

			return new self($jsonObject->name, $jsonObject->path,
				$jsonObject->controller, $jsonObject->action, $jsonObject->methods);
		}


		/**
		 * Check if a route has any url parameters which need to
		 * be sent on to the controller class.
		 *
		 * @return Boolean
		 */
		public function hasUrlParams() {
			$pathParts = explode('/', $this->path);
			foreach ($pathParts as $part) {
				if (preg_match("/^\{.+\}$/", $part)) {
					return true;
				}
			}

			return false;
		}

	}
