<?PHP 
$title = 'Create Account';
require_once 'includes/header.php'; 
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '
<script type="text/javascript">
	$(function() {
		$("#validatetextbutton").click(function() {
			var bValid = true;
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( $("#username"), "username", 3, 16 );
			bValid = bValid && checkLength( $("#password"), "password", 5, 16 );
			bValid = bValid && checkRegexp( $("#username"), /^[0-9a-zA-Z ]+$/, "Username must be Alphanumeric." );
			bValid = bValid && checkRegexp( $("#password"), /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
			bValid = bValid && checkMatch($("#password"), $("#cpassword"), "Passwords must match.");
			bValid = bValid && checkRegexp( $("#email"), /^[a-z0-9\.]+@[a-z0-9\.]+[\.](com|co[\.]uk)$/i, "Not a valid Email Address." );
			bValid = bValid && checkMatch($("#email"), $("#cemail"), "Email Addresses must match.");
			if ($("#mobile").val() != "") {
				bValid = bValid && checkRegexp( $("#mobile"), /^07[0-9]{9}$/, "Not a valid Telephone Number." );
			};
			if (bValid == false) {
				return false;
			};
		});
	});
</script>
<form action="includes/newuser.php" method="post">
<table>
<tr><td>Username:</td><td><input id="username" name="username" type="text" />*</td></tr>
<tr><td>Password:</td><td><input id="password" name="password" type="password" />*</td></tr>
<tr><td>Confirm Password:</td><td><input id="cpassword" name="cpassword" type="password" />*</td></tr>
<tr><td>Email:</td><td><input id="email" name="email" type="text" />*</td></tr>
<tr><td>Confirm Email:</td><td><input id="cemail" name="cemail" type="text" />*</td></tr>
<tr><td>Mobile Number:</td><td><input id="mobile" name="mobile" type="text" /></td></tr>
<tr><td colspan="2"><input id="validatetextbutton" type="submit" value="Create Account!" /></td></tr>
</table></form>';
require_once 'includes/footer.php'; ?>
