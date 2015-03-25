<?php

	/**
	 * A widget is a special type of view which is meant to be called independantly of the regular
	 * controller-view lifecycle.  It can be added directly to another view (even a layout) and it
	 * will call its own "controller" method when it gets rendered.  This means it can be used to build
	 * dynamic page chunks with database calls and such that can easily be reused.
	 *
	 * Because Widgets do not pass through the standard routing system in order to figure out which
	 * controller to call, we register widgets in a JSON config file which provides this mapping.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Widget {

		/**
		 * The name of the controller class (should extend WidgetController, not regular controller)
		 */
		private $controllerName;

		/**
		 * Name of the template file
		 */
		private $templateName;

		/**
		 * Builds a new widget object from the JSON configuration.  Meant to be called from
		 * JiaoyuCore on init so that all available widgets will be registered and ready to use.
		 *
		 * @param StdClass A raw json object built from parsing the config file.
		 * @return Widget The widget object created from the config.
		 */
		public static function buildFromJson($rawJsonObject) {
			$widget = new self();
			$valid = true;
			if (isset($rawJsonObject->controller)) {
				$widget->controllerName = $rawJsonObject->controller;
			} else {
				$valid = false;
			}

			if (isset($rawJsonObject->template)) {
				$widget->templateName = $rawJsonObject->template;
			} else {
				$valid = false;
			}

			if (!$valid) {
				throw new ConfigurationException("Malformed widget configuration file - each
					widget must have name, template and controller defined.");
			}

			return $widget;
		}

		/**
		 * Executes a widget by name.  Takes a parameter array to send to the widget's controller
		 * when executing it.  This method renders the template and returns it as a string.
		 *
		 * @param String $widgetName The name of the widget to run
		 * @param Array $controllerVars The variables (name, value pair) to pass to the controller action
		 * @return String The rendered template.
		 */
		public static function run($widgetName, $controllerVars = array()) {
			// First we have to find the widget by name from the config file
			$widget = JiaoyuCore::widget($widgetName);
			if (!$widget) {
				throw new ViewException("No widget registered with name $widgetName in widgets.conf");
			}

			// Now we have to instantiate the controller class
			$className = $widget->controllerName;
			$controller = new $className($controllerVars);
			$viewParams = $controller->execute();

			$template = View::make($widget->templateName, $viewParams);
			return $template->render();
		}

	}
