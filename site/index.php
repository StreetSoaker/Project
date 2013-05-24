<?php 

require('includes/config.php');

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>StreetSoaker</title>
	<script type="text/javascript" src="http://code.jquery.com/jquery-2.0.0.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#login').submit(function(event) {
				event.preventDefault();
				var username = $('#login input[name="username"]').val();
				var password = $('#login input[name="password"]').val();

				$.post("pages/login.php", { 'username': username, 'password': password },
					function(data) {
						$('body').append(data);
						// $('#loginField').hide();
					}
				);
			});


			$('#register').submit(function(event) {
				event.preventDefault();
				var username = $('#register input[name="username"]').val();
				var password = $('#register input[name="password"]').val();
				var passwordagain = $('#register input[name="passwordagain"]').val();
				var email = $('#register input[name="email"]').val();

				$.post("pages/register.php", { 'username': username, 'password': password, 'passwordagain': passwordagain, 'email':email },
					function(data) {
						$('body').append(data);
						// $('#registerField').hide();
					}
				);
			});
		});
	</script>
</head>
<body>

	<fieldset id="loginField">
		<legend>Login In</legend>
		<form action="" id="login" method="post">
			<input type="text" name="username" placeholder="Username" required /><br />
			<input type="password" name="password" placeholder="Password" required /><br />
			<input type="submit" name="submit" value="Inloggen" /><a href="pages/passwordrecovery.php">Recover password</a>
		</form>
	</fieldset>
	

	<fieldset id="registerField">
		<legend>Register</legend>
		<form action="" id="register" method="post">
			<input type="text" name="username" placeholder="Username" required /><br />
			<input type="password" name="password" placeholder="Password" required /><br />
			<input type="password" name="passwordagain" placeholder="Password Again" required /><br />
			<input type="email" name="email" placeholder="Email" required /><br />
			<input type="submit" name="submit" value="Register" />
		</form>
	</fieldset>
	
</body>
</html>