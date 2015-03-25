<?php

	/**
	 * This is the parent class for user controllers.  The dispatcher expects the user's controller
	 * to be subclassed from here and will call the constructor with the signature displayed below.
	 *
	 * It handles setting up a default layout for convenience and holding onto the request object.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Controller {

		/**
		 * The request object at the time the controller is called.
		 */
		protected $request;

		/**
		 * Controllers can have default layouts for convenience.
		 * This is the actual View object - it should be null until init
		 * is called
		 */
		protected $layout;

		/**
		 * This is the name of the layout which the extending controllers can set
		 */
		protected $layoutName;

		/**
		 * Initialize the layout if one is set.
		 */
		protected function initLayout() {
			if ($this->layoutName) {
				$this->layout = View::make($this->layoutName, array());
			}
		}

		/**
		 * Constructor method to be inherited by child constructors.
		 *
		 * The dispatcher will call it with this signature when dispatching so if you
		 * override you'll need to match this and probably want to call parent::construct($req)
		 *
		 * @param Request $request The currentrequest
		 */
		public function __construct(Request $request) {
			$this->request = $request;
			$this->initLayout();
		}
	}
