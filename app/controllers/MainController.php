<?php

	class MainController extends Controller {

		// Name of the layout template
		protected $layoutName = "layout";

		/**
		 * Show the homepage
		 */
		public function homeAction() {
			$homeView = View::make('homeview');
			$rendered = $this->layout->embed('content', $homeView, true);
			return Response::html($rendered);
		}

		public function fileAction() {
			Response::text("OK");
		}
	}
