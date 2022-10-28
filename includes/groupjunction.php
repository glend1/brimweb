<?PHP
require_once 'functions.php';
if (isset($_POST['add'])) {
	if (!fCanSee($_SESSION['permissions']['group'][$_POST['group']] >= 300)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (isset($_POST['group'])) {
		$queryNewJunction = 'insert into groupjunction (groupid, userid)
		values (' . $_POST['group'] . ',' . $_POST['add'] . ')';
		odbc_exec($conn, $queryNewJunction);
		$_SESSION['sqlMessage'] = 'User added to group!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Group failed to create!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['update'])) {
	if (!fCanSee($_SESSION['permissions']['group'][$_POST['group']] >= 200)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	if (isset($_POST['group'])) {
		$queryUpdateJunction = 'update groupjunction
		set groupid=' . $_POST['group'] . '
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateJunction);
		$_SESSION['sqlMessage'] = 'Group updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Group failed to update!';
		$_SESSION['uiState'] = 'error';
	}
};
if (isset($_POST['delete'])) {
	if (!fCanSee($_SESSION['permissions']['group'][$_POST['group']] >= 300)) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
	$queryDeleteJunction = 'delete from groupjunction
	where id = ' . $_POST['delete'];
	odbc_exec($conn, $queryDeleteJunction);
	$_SESSION['sqlMessage'] = 'User deleted from Group!';
	$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>