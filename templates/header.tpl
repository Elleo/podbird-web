<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Podbird - {$title}</title>
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="/css/overrides.css">

		<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/manifest.json">
		<meta name="msapplication-TileColor" content="#30dcf5">
		<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
		<meta name="theme-color" content="#30dcf5">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	</head>

	<body>
		<nav class="navbar navbar-inverse">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav" style='width: 100%'>
						<li {if $page=='home'} class="active"{/if}><a href="index.php">Features</a></li>
						<li {if $page=='charts'} class="active"{/if}><a href="charts.php">Charts</a></li>
						<li {if $page=='contribute'} class="active"{/if}><a href="contribute.php">Contribute</a></li>
						<div style='float: right;'>
							<ul class="nav navbar-nav">
							{if $logged_in}
							<li {if $page=='profile'} class="active"{/if}><a href="profile.php">Dashboard</a></li>
							<li {if $page=='profile'} class="active"{/if}><a href="profile.php?logout=true">Log out</a></li>
							{else}
							<li {if $page=='profile'} class="active"{/if}><a href="profile.php">Log in</a></li>
							{/if}
							</ul>
						</div>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</nav>

		<div class="container">
			<div class="parrot-header">
				<center>
					<h1 id='title'>Podbird</h1>
					<a href='/download.php'><button class="btn btn-default btn-round" >Download</button></a>
				</center>
			</div>
			
			<div class="whitebox">
