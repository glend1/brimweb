<?PHP 
$title = 'Change Details';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$id = $_SESSION['id'];
if ($_SESSION['id'] == 1) {
	if (isset($_GET['id'])) {
		if ($_GET['id'] == 1) {
			$isAdmin = true;
		} else {
			$id = $_GET['id'];
		};
	} else {
		$isAdmin = true;
	};
};
$mobile = '';
$queryUser = 'select ';
if ($_SESSION['id'] == 1 && !isset($isAdmin)) {
	$queryUser .= 'name, email, ';
};
$queryUser .= 'mobile from users where id = ' . $id;
$dataUser = odbc_exec($conn, $queryUser);
while ($rows = odbc_fetch_array($dataUser)) {
	$mobile = $rows['mobile'];
	$row = $rows;
};
$stdOut .= '

<script type="text/javascript">
	$(function() {
		$("#validatetextbutton").click(function() {
			var bValid = true;
			$("input").removeClass( "ui-state-error" );';
if ($_SESSION['id'] == 1 && !isset($isAdmin)) {
			$stdOut .= 'bValid = bValid && checkLength( $("#name"), "username", 3, 16 );
			bValid = bValid && checkRegexp( $("#name"), /^[0-9a-zA-Z ]+$/, "Username must be Alphanumeric." );
			bValid = bValid && checkLength( $("#password"), "password", 5, 16 );
			bValid = bValid && checkRegexp( $("#password"), /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
			bValid = bValid && checkRegexp( $("#email"), /^[a-z0-9\.]+@[a-z0-9\.]+[\.](com|co[\.]uk)$/i, "Not a valid Email Address." );';
};
			$stdOut .= 'if ($("#mobile").val() != "") {
				bValid = bValid && checkRegexp( $("#mobile"), /^07[0-9]{9}$/, "Not a valid Telephone Number." );
			};
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
if ($_SESSION['id'] == 1) {
	if (isset($_GET['id'])) {
		if ($_GET != 1) {
			$stdOut .= '<form id="changepassword" action="includes/changepassword.php" method="POST">
			<script type="text/javascript">
				$(function() {
					$("#forcepasswordbutton").click(function() {
						var bValid = true;
						$("input").removeClass( "ui-state-error" );
						bValid = bValid && checkLength( $("#forcepassword"), "password", 5, 16 );
						bValid = bValid && checkRegexp( $("#forcepassword"), /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
						if (bValid == false) {
							return false;
						};
					});
				});
			</script>
			<h3>Change Password</h3>
			<input name="id" type="hidden" value="' . $_GET['id'] . '"></input>
			New Password:<br />
			<input type="password" name="newpass" id="forcepassword" size="16"><span class="required"> * </span><br />
			<input id="forcepasswordbutton" type="submit" class="password" value="Change password">
			</form>';
		};
	} ;
} else {
	$stdOut .= '<form id="changepassword" title="Change Password" action="includes/changepassword.php" method="POST">
	<h3>Change Password</h3>
	<input type="hidden" name="id" value="' . $_SESSION['id'] . '"> 
	<div class="formelement">
	Current password:<br>
	<input type="password" name="currentpass" id="currentpass" size="16"><span class="required"> * </span></div>
	<div class="formelement">
	New password:<br>
	<input type="password" name="newpass" id="newpass" size="16"></div>
	<div class="formelement">Confirm New password:<br>
	<input type="password" name="confirmnewpass" id="confirmnewpass" size="16"><span class="required"> * </span></div>
	<input type="submit" id="password" value="Change password">
	</form>';
};
$stdOut .= '<p><form action="includes/updateuser.php" method="post">
<h3>Change Details</h3>
<input name="id" type="hidden" value="' . $id . '"/>';
if ($_SESSION['id'] == 1 && !isset($isAdmin)) {
	$stdOut .= '<div class="formelement">Username:<br /><input id="name" name="name" type="text" value="' . $row['name'] . '"/><span class="required"> * </span></div>
	<div class="formelement">Email:<br /><input id="email" name="email" type="text" value="' . $row['email'] . '"/><span class="required"> * </span></div>';
};
$stdOut .= '<div class="formelement">Mobile Number:<br /><input id="mobile" name="mobile" type="text" value="' . $mobile . '"/><br /></div>
<input id="validatetextbutton" type="submit" value="Update Account!" /></form></p><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
require_once 'includes/footer.php'; ?>