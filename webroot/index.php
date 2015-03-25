<?php

	/**
	 * Main entry point for all incoming requests to the application.
	 *
	 * First we load up the defines in the CoreConfig, so that the autoloader
	 * can know where to look when it tracks down php classes.
	 *
	 * Next it asks HttpCore to build it a request object, which is created from
	 * the PHP superglobal arrays which contain request data.
	 *
	 * Finally, it passes the request object to the Router in order to dispatch it.
	 * Dispatch will look at the request, figure out the appropriate controller which
	 * should be called and then
	 */

	require_once("../CoreConfig.php");
	require_once("../lib/jiaoyu/AutoLoader.php");

	AutoLoader::init();
	JiaoyuCore::init();

	$request = HttpCore::buildRequest();
	Router::dispatch($request);
