<?php

	/**
	 * A controller extending the widget controller class will be instantiated
	 * and executed when calling Widget::run().  The name of this controller class is
	 * defined in the widget config file but in order for it to work properly it must
	 * extend this abstract class.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	abstract class WidgetController {

		/**
		 * Constructor method - takes in an array of parameters and makes
		 * all of the values in it available as properties of the object,
		 * with the names of the props being the keys.
		 *
		 * @param Array $params Associative array of the objects properties.
		 */
		public function __construct(Array $params) {
			foreach ($params as $k => $v) {
				$this->{$k} = $v;
			}
		}

		/**
		 * This is what actually happens when the controller is "called" during widget
		 * generation.  This should return an associative array which will be
		 * passed on as parameters to the receiving template.
		 *
		 * @return Array
		 */
		abstract public function execute();
	}
