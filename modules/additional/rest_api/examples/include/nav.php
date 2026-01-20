<?php
	$page = isset($page) ? $page : 'index.php';
?>
<nav class="navbar navbar-default navbar-static-top">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php">uHotelBooking REST API</a>
			<ul class="nav navbar-nav">
				<li <?= ($page == 'index.php' ? 'class="active"' : ''); ?>><a href="index.php">Home</a></li>
				<li <?= ($page == 'configuration.php' ? 'class="active"' : ''); ?>><a href="configuration.php">Configuration</a></li>
				<li <?= ($page == 'getting-started.php' ? 'class="active"' : ''); ?>><a href="getting-started.php">Getting Started</a></li>
				<li <?= ($page == 'examples-index.php' ? 'class="active"' : ''); ?>><a href="examples-index.php">Examples</a></li>
			</ul>				
		</div>
	</div>
</nav>
