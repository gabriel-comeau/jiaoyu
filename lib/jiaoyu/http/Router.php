<?php

	/**
	 * The Router is the class which handles "dispatch".  It looks at the available routes in the
	 * application and figures out which one (if any) are appropriate for the incoming request.
	 *
	 * If it finds something, it instantiates the controller defined by the route and calls the
	 * appropriate action method.  It also handles getting any variables off of the urlparams of
	 * the route/req pair and sending them to the action method as arguments.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Router {

		/**
		 * Performs dispatch.  This takes an incoming request object
		 * which has been created and searches all of the loaded routes
		 * for one which matches.  If it finds a route it instantiates the
		 * controller for that route and calls the appropriate method.
		 *
		 * If it doesn't find what it is looking for it will trigger a
		 * 404 response.
		 *
		 * @param Request $request The incoming request to be handled.
		 */
		public static function dispatch(Request $request) {
			$route = self::findRouteForRequest($request);
			if ($route) {

				$controllerName = $route->controller;
				$controller = new $controllerName($request);

				if ($route->hasUrlParams()) {
					$actionParams = self::getUrlParams($request, $route);
					call_user_func_array(array($controller, $route->action), $actionParams);
				} else {
					call_user_func(array($controller, $route->action));
				}
			} else {
				// Defer to the response class to send a 404 error.
				Response::fourOhFour($request);
			}

		}

		/**
		 * Loop through the available routes, trying to match each one to
		 * the current request.  If one of them matches it gets returned, otherwise
		 * null gets returned.
		 *
		 * @param Request $request The request to test for
		 * @param Route The matching route (or null if nothing matched)
		 */
		private static function findRouteForRequest(Request $request) {
			foreach (JiaoyuCore::getRoutes() as $route) {

				// First we look for method - bail out if this route
				// doesn't match it
				if (!in_array($request->method, $route->methods)) {
					continue;
				}

				// Next look for a literal string match (no url params)
				if ($request->path == $route->path) {
					return $route;
				}

				// Now we have to look for the patterns
				$requestParts = explode('/', $request->path);
				$routeParts = explode('/', $route->path);
				if (count($requestParts) != count($routeParts)) {
					// Matching pairs will have the same number of parts
					continue;
				}

				$matchCount = 0;
				for ($i = 0; $i < count($requestParts); $i++) {
					// Exact match check for this pair of parts
					if ($routeParts[$i] == $requestParts[$i]) {
						$matchCount++;
					} else if (preg_match("/^\{.+\}$/", $routeParts[$i])) {
						// This is an ugly check for the {varname} pattern in
						// routes with url params
						$matchCount++;
					}
				}

				if ($matchCount == count($requestParts)) {
					// All parts matched
					return $route;
				}
			}

			// Found nothing
			return null;
		}

		/**
		 * This method finds all of the url parameters needed by the route
		 * in the response.  It returns an array of these values, which can
		 * be used as an argument for call_user_method_array.
		 *
		 * @param Request $request The current request
		 * @param Route $route The matching route
		 * @return Array The values of the url params from the request
		 */
		private static function getUrlParams(Request $request, Route $route) {
			$reqParts = explode('/', $request->path);
			$routeParts = explode('/', $route->path);

			$params = array();
			for ($i = 0; $i < count($reqParts); $i++) {
				if (preg_match('/^\{.+\}$/', $routeParts[$i])) {
					$params[] = $reqParts[$i];
				}
			}

			return $params;
		}


	}
