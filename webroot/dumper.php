<?php
	// Feel free to add debug code here.  The autoloader is available but by default
	// no other framework execution happens.
	require_once("../CoreConfig.php");
	require_once("../lib/jiaoyu/AutoLoader.php");

	AutoLoader::init();
	JiaoyuCore::init();

	// TODO:  Your debug code here
?>
<!DOCTYPE html>
<html>
	<head></head>
	<body>
		<h1>Jiaoyu Debug Dumper</h1>
		<!-- Your debug stuff here -->

		<!-- All of the superglobal values -->
		<h2>$_GET</h2>
		<div><pre><?php var_dump($_GET); ?></pre></div>
		<h2>$_POST</h2>
		<div><pre><?php var_dump($_POST); ?></pre></div>
		<h2>$_REQUEST</h2>
		<div><pre><?php var_dump($_REQUEST); ?></pre></div>
		<h2>$_SERVER</h2>
		<div><pre><?php var_dump($_SERVER); ?></pre></div>
		<?php if (isset($_SESSION)): ?>
			<h2>$_SESSION</h2>
			<div><pre><?php var_dump($_SESSION); ?></pre></div>
		<?php endif; ?>
		<h2>$_COOKIE</h2>
		<div><pre><?php var_dump($_COOKIE); ?></pre></div>
	</body>
</html>
