<?PHP
if (isset($_GET['name'])) {
	require_once 'includes/functions.php';
	if (isset($_SESSION['id'])) {
		$_SESSION['sqlMessage'] = 'You are already logged in!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$queryCheck = 'SELECT top 1 id, active, email
	from users
	where name = \'' . $_GET['name'] . '\'';
	$dataCheck = odbc_exec($conn, $queryCheck);
	if (odbc_fetch_row($dataCheck)) {
		if (odbc_result($dataCheck, 2)) {
			$_SESSION['sqlMessage'] = 'User Already activated!';
			$_SESSION['uiState'] = 'error';
		} else {
			fMakeRandom(1, $_GET['name']);
			fConfirmEmail(odbc_result($dataCheck, 3), $_GET['username'], odbc_result($dataCheck, 1));
			$_SESSION['sqlMessage'] = 'New email sent!';
			$_SESSION['uiState'] = 'active';
		};
	} else {
		$_SESSION['sqlMessage'] = 'User Not Found!';
		$_SESSION['uiState'] = 'error';
	};
	fRedirect();
} else {
	$title = "Request Activation";
	require_once 'includes/header.php';
	if (isset($_SESSION['id'])) {
		$_SESSION['sqlMessage'] = 'You are already logged in!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$stdOut .= '<form method="get" action="requestactivation.php"><label for="name">Username:</label><input type="text" name="name" id="name"/><br /><input type="submit" value="Submit!" id="validatetextbutton"/>
	</form>
	<script type="text/javascript">	
		$(function() {
			$("#validatetextbutton").click(function() {
				var bValid = true;
				$("input").removeClass( "ui-state-error" );
				bValid = bValid && checkLength( $("#name"), "username", 3, 16 );
				if (bValid == false) {
					return false;
				};
			});
		});
	</script>';
	require_once 'includes/footer.php'; 
};
?>