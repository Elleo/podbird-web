{include file='header.tpl'}

	<center>
		<h2>Please sign in</h2>
	</center>
	<br />

	<div style='max-width: 25em; margin-left: auto; margin-right: auto;'>
	<form class="form-signin" method='POST' action=''>
		<label for="inputUsername" class="sr-only">Username</label>
		<input type="text" id="username" name="username" class="form-control" placeholder="Username" required autofocus>
		<br />
		<label for="inputPassword" class="sr-only">Password</label>
		<input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
		<br />
		<button class="btn btn-lg btn-round-primary btn-primary btn-block" type="submit">Sign in</button><br />
		<a href='/register.php'><span class="btn btn-lg btn-round-primary btn-primary btn-block">Register</button></a>
	</form>
	</div>

	<br />

{include file='footer.tpl'}
