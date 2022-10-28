<?PHP 
$title = 'Login';
require_once 'includes/header.php'; 
if (isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You are already logged in!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<h3>Login</h3>
<form action="includes/login.php" method="POST">Enter your Username and Password.<br />
	<label for="user">Username:</label><br />
	<input type="text" name="user" id="user" size="16"><br />
	<label for="user">Password:</label><br />
	<input type="password" name="pass" id="pass" size="16"><br />
	<div><input id="login" type="submit" value="Log in"></div>
	</form>
	<div><a href="createaccount.php">Need an account?</a><br /><a href="resetpassword.php">Forgot your password?</a></div>';
require_once 'includes/footer.php'; ?>
