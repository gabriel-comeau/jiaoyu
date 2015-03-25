<?php

	/**
	 * Base class for all ORM exceptions.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class AtlasException extends Exception {

		public $isFatal = true;
	}
