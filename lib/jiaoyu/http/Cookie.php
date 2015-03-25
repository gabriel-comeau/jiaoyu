<?php

	/**
	 * Object to represent a cookie.  Not meant to be instantiated directly, should be created using
	 * the Session::addCookie() call instead.
	 *
	 * @author Gabriel Comeau
	 * @package Jiaoyu
	 */
	class Cookie {

		/**
		 * Cookie's name and unique key.  Both for the session cookie holder and the cookie name
		 * itself when sent to the browser.
		 */
		public $name;

		/**
		 * The value stored in the cookie.  Should be a simple type to prevent weird object
		 * serialization problems. (Don't store objects in cookies!)
		 */
		public $value;

		/**
		 * The domain the cookie is accessible from.
		 */
		public $domain;

		/**
		 * The timestamp this cookie will expire at.
		 */
		public $expiry;

		/**
		 * The path this cookie is accessible from.  For sites built on the framework this should
		 * be / (the default value when making a new cookie) because with mod_rewrite rules all
		 * requests will be treated as coming from / anyway.
		 */
		public $path;
	}
