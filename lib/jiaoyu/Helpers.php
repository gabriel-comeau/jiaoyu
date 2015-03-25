<?php

	/**
	 * This is the 'helpers' file - it includes utility functions that don't really fit inside
	 * classes.  These are especially meant to be called from templates, where we don't want to
	 * be using heavy Object Orientation anyway, to keep it simple.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */

	// Time defines - handy for cookies and other stuff along those lines
	define("ONE_DAY_AGO", strtotime("-1 day"));
	define("ONE_HOUR_FROM_NOW", strtotime("+1 hour"));
	define("TWELVE_HOURS_FROM_NOW", strtotime("+12 hour"));
	define("ONE_DAY_FROM_NOW", strtotime("+1 day"));
	define("FIVE_DAYS_FROM_NOW", strtotime("+5 day"));
	define("ONE_MONTH_FROM_NOW", strtotime("+1 month"));
	define("THREE_MONTHS_FROM_NOW", strtotime("+3 month"));
	define("SIX_MONTHS_FROM_NOW", strtotime("+6 month"));
	define("ONE_YEAR_FROM_NOW", strtotime("+1 year"));


	/**
	 * Returns the url to a given route, by name.  If this route has
	 * url parameters, they should be passed, in order, as an array.
	 *
	 * @param String $routeName The canonical (unique) name of a route
	 * @param Array $routeParams The url params needed by the route
	 */
	function route($routeName, $routeParams = array()) {
		$route = null;
		foreach (JiaoyuCore::getRoutes() as $checkRoute) {
			if ($checkRoute->name == $routeName) {
				$route = $checkRoute;
				break;
			}
		}

		if ($route) {
			$url = JiaoyuCore::baseUrl();

			// Quick check to make sure that a "/" url can be used without
			// breaking it and causing .htaccess nightmares
			if ($route->path == "/") {
				return $url."/";
			}

			$routeParts = explode('/', $route->path);
			$paramCount = 0;
			for ($i = 0; $i < count($routeParts); $i++) {
				if ($routeParts[$i] != "") {
					$url .= '/';
					if (preg_match('/^\{.+\}$/', $routeParts[$i])) {
						$url .= $routeParams[$paramCount];
						$paramCount++;
					} else {
						$url .= $routeParts[$i];
					}
				}
			}

			return $url;
		} else {
			throw new RoutingException("No route found for $routeName");
		}
	}

	/**
	 * Returns the HTML tag for a stylesheet
	 *
	 * @param String $name The name/path of the desired style sheet
	 * @param Boolean $strict If this is enabled, an exception will be thrown if the file can't
	 *                        be found.  Othewise "" is returned.
	 *
	 * @return String The html tags for the stylesheet
	 */
	function stylesheet($name, $strict = false) {
		// By default we assume the stylesheet is in webroot/css or a subfolder of it
		if (preg_match('/^.+\.css$/', $name)) {
			$name = preg_replace('/\.css$/', '', $name);
		}
		if (file_exists(JIAOYU_PROJECT_HOME."/webroot/css/$name.css")) {
			$path = JiaoyuCore::baseUrl()."/css/$name.css";
			$tag = '<link href="'.$path.'" rel="stylesheet" type="text/css" media="all"></link>';
			return $tag;
		} else {
			if ($strict) {
				throw new ViewException("Can't find CSS file $name");
			} else {
				return "";
			}
		}
	}

	/**
	 * Returns HTML tag for a javascript include
	 *
	 * @param String $name The name/path of the desired script
	 * @param Boolean $strict If this is enabled, an exception will be thrown if the file can't
	 *                        be found.  Othewise "" is returned.
	 *
	 * @return String The html tags for the script
	 */
	function script($name, $strict = false) {
		// By default we assume the script is in webroot/js or a subfolder of it
		if (preg_match('/^.+\.js$/', $name)) {
			$name = preg_replace('/\.js$/', '', $name);
		}
		if (file_exists(JIAOYU_PROJECT_HOME."/webroot/js/$name.js")) {
			$path = JiaoyuCore::baseUrl()."/js/$name.js";
			$tag = '<script type="text/javascript" src="'.$path.'"></script>';
			return $tag;
		} else {
			if ($strict) {
				throw new ViewException("Can't find JS file $name");
			} else {
				return "";
			}
		}
	}
