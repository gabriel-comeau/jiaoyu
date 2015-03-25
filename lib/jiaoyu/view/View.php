<?php

	/**
	 * View represents a template.  An instance of this class should map directly to an includable
	 * template file in the app/ folder.  Any variables that should be available to the template
	 * should be passed to the view as an array.
	 *
	 * Note that the render method effectively executes any PHP code in the templates, but doesn't
	 * actually send anything to the browser - Response does that instead (so headers can be controlled)
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class View {

		/**
		 * Path to the template file to be included.
		 */
		private $templateFile;

		/**
		 * Array of values that will be available as variables in the template.  The key is the name
		 * of the variable in the template and the value is the value.
		 */
		private $templateVars;

		/**
		 * Static factory method for creating views.
		 */
		public static function make($templateName, $templateVars = array()) {
			$view = new self();

			$view->templateFile = JIAOYU_PROJECT_HOME."/app/views/".$templateName.".php";
			if (!file_exists($view->templateFile)) {
				// This might be a framework resource - let's check if it is
				$view->templateFile = JIAOYU_PROJECT_HOME."/lib/jiaoyu/resources/views/".$templateName.".php";
				if (!file_exists($view->templateFile)) {
					throw new ViewException("Can't find template file for $templateName");
				}
			}

			$view->templateVars = $templateVars;

			return $view;
		}

		/**
		 * Renders the template, passing in the correct variables from the array.
		 *
		 * Doesn't actually send the template to the browser - only returns the string
		 * so that the response can be manipulated if desired, and to make nesting views
		 * inside one another easy.
		 *
		 * @return String The rendered template's content.
		 */
		public function render() {

			// First we need to get all of the variables out of the array and as "normal" variables.
			if ($this->templateVars) {
				foreach ($this->templateVars as $varKey => $varVal) {
					${$varKey} = $varVal;
				}
			}

			// Using the output buffer we can include templates which will execute regular
			// php code but not actually display them to output.
			ob_start();
			include($this->templateFile);
			$content = ob_get_clean();
			return $content;
		}

		/**
		 * Nest one template inside another one.
		 *
		 * @param String $key The name of the variable where the inner template will be displayed
		 * @param View $view The inner template
		 * @param Boolean $render Whether or not to immediately render this template.
		 * @return Mixed - String if render is set to true and View if not.
		 */
		public function embed($key, View $innerView, $render = false) {
			$this->templateVars[$key] = $innerView->render();
			if ($render) {
				return $this->render();
			} else {
				return $this;
			}
		}
	}
