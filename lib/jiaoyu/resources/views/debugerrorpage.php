<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo JiaoyuCore::config('app_name'); ?> - debug</title>
		<?php echo stylesheet("jiaoyu/framework.css"); ?>
		<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
		<?php echo script("jiaoyu/jquery-2.1.3.min.js"); ?>
	</head>
	<body>
		<!-- debug bar goes here -->
		<div class="top-titlebar">
			<span class="frameworktitle">教育 Jiaoyu</span>
		</div>

		<div class="content">

			<h1>ERROR</h1>
			<h2 class="important-message">
				<?php if ($exception): ?>
					<?php echo $exception->getMessage(); ?>
				<?php elseif ($errors): ?>
					<?php echo $errors['errstr']; ?>
				<?php else: ?>
					Unknown error
				<?php endif; ?>
			</h2>

			<?php if ($exception): ?>
				<div class="stacktrace-holder">
					<h3>Stacktrace:</h3>
					<?php $hiddenDivs = array(); ?>
					<table class="stacktrace">
						<tr class="header-row">
							<th>#</th>
							<th>Call</th>
							<th>File</th>
						</tr>

						<?php foreach ($exception->getTrace() as $lineCount => $traceLine): ?>
							<?php if ($lineCount % 2 == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>


							<?php
								$file = (empty($traceLine['file'])) ? 'UNKNOWN' : $traceLine['file'];
								$line = (empty($traceLine['line'])) ? 'UNKNOWN' : $traceLine['line'];
								$function = (empty($traceLine['function'])) ? 'UNKNOWN' : $traceLine['function'];
								$type = (empty($traceLine['type'])) ? null : $traceLine['type'];
								$class = (empty($traceLine['class'])) ? null : $traceLine['class'];
								$args = (empty($traceLine['args'])) ? array() : $traceLine['args'];

								$lineOne = "";
								$lineTwo = "";

								if ($class) {
									$lineOne .= '<span class="class-name">'.$class.'</span>';
								}
								if ($type) {
									$lineOne .= $type;
								}

								$lineOne .= '<span class="function-name">'.$function.'</span>';

								// Do the function args
								if (count($args)) {
									$i = 1;
									foreach ($args as $argKey => $arg) {
										if ($i == 1) {
											$lineOne .= "(";
										}

										if (gettype($arg) == 'object') {
											$spanId = 'id="mouseover-'.$lineCount.'-'.$argKey.'"';
											$hiddenId = 'id="popup-'.$lineCount.'-'.$argKey.'"';
											$hiddenDivs[] = '<div style="display: none;" class="popup" '.$hiddenId.'>'.htmlentities(print_r($arg, true)).'</div>';
											$lineOne .= '<span class="arg mousable" '.$spanId.'>'.'OBJECT'.'</span>';
										} else if (gettype($arg) == 'array') {
											$spanId = 'id="mouseover-'.$lineCount.'-'.$argKey.'"';
											$hiddenId = 'id="popup-'.$lineCount.'-'.$argKey.'"';
											$hiddenDivs[] = '<div style="display: none;" class="popup" '.$hiddenId.'>'.htmlentities(print_r($arg, true)).'</div>';
											$lineOne .= '<span class="arg mousable" '.$spanId.'>'.'ARRAY'.'</span>';
										} else if (gettype($arg) == 'string') {
											$lineOne .= '<span class="arg">'.'"'.$arg.'"'.'</span>';
										} else {
											$lineOne .= '<span class="arg">'.$arg.'</span>';
										}

										if ($i < count($args)) {
											$lineOne .= ', ';
										} else {
											$lineOne .= ")";
										}

										$i++;
									}
								} else {
									$lineOne .= '()';
								}

								$lineTwo .= "File: $file - Line: ".'<span class="line">'.$line.'</span>';
							?>

							<td><?php echo count($exception->getTrace()) - $lineCount - 1; ?></td>
							<td><?php echo $lineOne; ?></td>
							<td><?php echo $lineTwo; ?></td>
						</tr>
					<?php endforeach; ?>
					</table>
				</div>
				<div class="stacktrace-hidden">
					<?php foreach ($hiddenDivs as $hiddenDiv): ?>
						<?php echo $hiddenDiv; ?>
					<?php endforeach; ?>
				</div>
			<?php elseif ($errors): ?>
				<div class="stacktrace-holder">
					<h3>Errno: <span class="line"><?php echo $errors['errno']; ?></h3>
					<h3>File: <span class="class-name"><?php echo $errors['errfile']; ?></h3>
					<h3>Line:  <span class="line"><?php echo $errors['errline']; ?></h3>
				</div>
			<?php endif; ?>


			<div class="superglobals-holder">
				<h3>Superglobal Variables:</h3>
				<table class="superglobal-table">
					<tr class="header-row">
						<th colspan="2">$_SERVER</th>
					</tr>
					<?php if (!empty($_SERVER)): ?>
						<?php $i=0; ?>
						<?php foreach ($_SERVER as $k => $v): ?>
							<?php if (($i % 2) == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>
									<td><?php echo $k; ?></td>
									<?php if (gettype($v) == 'array' || gettype($v) == 'object'): ?>
										<td><?php echo print_r($v, true); ?></td>
									<?php else: ?>
										<td><?php echo $v; ?></td>
									<?php endif; ?>
								</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="even-row">
							<td colspan="2">Empty!</td>
						</tr>
					<?php endif; ?>
				</table>

				<table class="superglobal-table">
					<tr class="header-row">
						<th colspan="2">$_GET</th>
					</tr>
					<?php if (!empty($_GET)): ?>
						<?php $i=0; ?>
						<?php foreach ($_GET as $k => $v): ?>
							<?php if (($i % 2) == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>
									<td><?php echo $k; ?></td>
									<?php if (gettype($v) == 'array' || gettype($v) == 'object'): ?>
										<td><?php echo print_r($v, true); ?></td>
									<?php else: ?>
										<td><?php echo $v; ?></td>
									<?php endif; ?>
								</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="even-row">
							<td colspan="2">Empty!</td>
						</tr>
					<?php endif; ?>
				</table>

				<table class="superglobal-table">
					<tr class="header-row">
						<th colspan="2">$_POST</th>
					</tr>
					<?php if (!empty($_POST)): ?>
						<?php $i=0; ?>
						<?php foreach ($_POST as $k => $v): ?>
							<?php if (($i % 2) == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>
									<td><?php echo $k; ?></td>
									<?php if (gettype($v) == 'array' || gettype($v) == 'object'): ?>
										<td><?php echo print_r($v, true); ?></td>
									<?php else: ?>
										<td><?php echo $v; ?></td>
									<?php endif; ?>
								</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="even-row">
							<td colspan="2">Empty!</td>
						</tr>
					<?php endif; ?>
				</table>

				<table class="superglobal-table">
					<tr class="header-row">
						<th colspan="2">$_SESSION</th>
					</tr>
					<?php if (!empty($_SESSION)): ?>
						<?php $i=0; ?>
						<?php foreach ($_SESSION as $k => $v): ?>
							<?php if (($i % 2) == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>
									<td><?php echo $k; ?></td>
									<?php if (gettype($v) == 'array' || gettype($v) == 'object'): ?>
										<td><?php echo print_r($v, true); ?></td>
									<?php else: ?>
										<td><?php echo $v; ?></td>
									<?php endif; ?>
								</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="even-row">
							<td colspan="2">Empty!</td>
						</tr>
					<?php endif; ?>
				</table>

				<table class="superglobal-table">
					<tr class="header-row">
						<th colspan="2">$_COOKIE</th>
					</tr>
					<?php if (!empty($_COOKIE)): ?>
						<?php $i=0; ?>
						<?php foreach ($_COOKIE as $k => $v): ?>
							<?php if (($i % 2) == 0): ?>
								<tr class="even-row">
							<?php else: ?>
								<tr class="odd-row">
							<?php endif; ?>
									<td><?php echo $k; ?></td>
									<?php if (gettype($v) == 'array' || gettype($v) == 'object'): ?>
										<td><?php echo print_r($v, true); ?></td>
									<?php else: ?>
										<td><?php echo $v; ?></td>
									<?php endif; ?>
								</tr>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="even-row">
							<td colspan="2">Empty!</td>
						</tr>
					<?php endif; ?>
				</table>

			</div>
		</div>

		<script type="text/javascript">
			$(function() {
				$('.mousable').hover(
					function(e) {
						// HOVER IN
						var hoverId = $(this).attr("id");
						idToPopUp = hoverId.replace("mouseover", "popup");
						$('#'+idToPopUp).fadeIn();
					},
					function(e) {
						// HOVER OUT
						var hoverId = $(this).attr("id");
						idToPopUp = hoverId.replace("mouseover", "popup");
						$('#'+idToPopUp).fadeOut();
					}
				);
			});
		</script>
	</body>
</html>
