<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo JiaoyuCore::config('app_name'); ?></title>
		<?php echo stylesheet("jiaoyu/framework.css"); ?>
		<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
		<?php echo script("jiaoyu/jquery-2.1.3.min.js"); ?>
	</head>
	<body>
		<div class="top-titlebar">
			<span class="frameworktitle">教育 Jiaoyu</span>
		</div>

		<div class="content">
			<?php echo $content; ?>
		</div>
	</body>
</html>
