<!DOCTYPE html>
<html>
	<head>
		<title><?php echo JiaoyuCore::config('app_name'); ?> - Not Found</title>
		<style>
			body {
				text-align: center;
			}
		</style>
	</head>
	<body>
		<h1>404 Not Found</h1>
		<h2>
			Sorry, a page at <?php echo $request->path; ?> cannot be found.
		</h2>
		<p>
			If you clicked a link and were brought here, we apologize and will hopefully fix it shortly.
		</p>
		<p>
			Pressing back on your browser should return you somewhere which makes sense.
		</p>
	</body>
</html>
