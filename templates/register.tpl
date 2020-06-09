{include file='header.tpl'}

	<div style='max-width: 25em; margin-left: auto; margin-right: auto;'>
	{if isset($errors)}
		<p id='errors'>{$errors}</p>
	{/if}

	<form class="form-signin" method='POST' action=''>
		<h2 class="form-signin-heading">Register</h2><br />
		<label for="username" class="sr-only">Username</label>
		<input type="text" id="username" name="username" class="form-control" placeholder="Username" required autofocus>
		<br />
		<label for="email" class="sr-only">Email address</label>
		<input type="text" id="email" name="email" class="form-control" placeholder="Email address" required autofocus>
		<br />
		<label for="password" class="sr-only">Password</label>
		<input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
		<br />
		<label for="password-repeat" class="sr-only">Confirm password</label>
		<input type="password" id="password-repeat" name="password-repeat" class="form-control" placeholder="Confirm password" required>
		<br />
		<button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
	</form>
	</div>

{include file='footer.tpl'}
