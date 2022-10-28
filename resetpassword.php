<?PHP 
$title = 'Password Reset';
require_once 'includes/header.php'; 
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '';
if (isset($_GET['code']) && isset($_GET['user'])) {
	if(fCheckRandom(2, $_GET['user'], $_GET['code'], 60 * 60)) {
		$stdOut .= '<script type="text/javascript">
		$(function() {
			$("#validatetextbutton").click(function() {
				var bValid = true;
				$("input").removeClass( "ui-state-error" );
				bValid = bValid && checkLength( $("#password"), "password", 5, 16 );
				bValid = bValid && checkRegexp( $("#password"), /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
				bValid = bValid && checkMatch($("#password"), $("#cpassword"), "Passwords must match.");
				if (bValid == false) {
					return false;
				};
			});
		});
		</script>
		<form action="includes/emailpasswordform.php" method="post">
		<input name="id" type="hidden" value="' . $_GET['user'] . '"/>
		<input name="code" type="hidden" value="' . $_GET['code'] . '"/>
		Password:<br /><input id="password" name="password" type="password" /><br />
		Confirm Password:<br /><input id="cpassword" name="cpassword" type="password" /><br />
		<input id="validatetextbutton" type="submit" value="Change Password!" />
		</form>';
	} else {
		$_SESSION['sqlMessage'] = 'Code expired or invalid!';
		$_SESSION['uiState'] = 'error';
		header('Location:' . $urlPath . 'index.php');
	};
} else {
	$stdOut .= '<script type="text/javascript">
		$(function() {
			$("#validatetextbutton").click(function() {
				var bValid = true;
				$("input").removeClass( "ui-state-error" );
				bValid = bValid && checkRegexp( $("#email"), /^[a-z0-9\.]+@[a-z0-9\.]+[\.](com|co[\.]uk)$/i, "Not a valid Email Address." );
				if (bValid == false) {
					return false;
				};
			});
		});
	</script>
	<form action="includes/emailpassword.php" method="post">
	Email:<input id="email" name="email" type="text" /><br />
	<input id="validatetextbutton" type="submit" value="Reset Password!" />
	</form>';
};
require_once 'includes/footer.php'; ?>
