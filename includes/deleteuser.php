<?PHP
require_once 'functions.php';
if ($_SESSION['id'] != 1) {
	$queryUserCanChange = 'select distinct Users.id 
	from Users, GroupJunction';
	$aQueryGroupPermissions[] = 'GroupJunction.UserID = Users.ID';
	fOrThem($_SESSION['permissions']['group'], 300, 'GroupJunction.GroupID', $aQueryGroupPermissions);
	fGenerateWhere($queryUserCanChange, $aQueryGroupPermissions);
	$dataUserCanChange = odbc_exec($conn, $queryUserCanChange);;
	while (odbc_fetch_row($dataUserCanChange)) {
		$aUserCanChange[] = odbc_result($dataUserCanChange, 1);
	};
	if (!fCanSee(in_array($_POST['id'] , $aUserCanChange))) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
if ($_POST['id'] != '') {
	$queryDeleteUser = 'delete from users
	where id = ' . $_POST['id'];
	odbc_exec($conn, $queryDeleteUser);
	$queryDeleteJunction = 'delete from groupjunction
	where userid = ' . $_POST['id'];
	odbc_exec($conn, $queryDeleteJunction);
	$_SESSION['sqlMessage'] = 'User was sucessfully Deleted!';
	$_SESSION['uiState'] = 'active';
	odbc_close($conn);
} else {
	$_SESSION['sqlMessage'] = 'User deletion failed!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>