<?PHP
require_once 'functions.php';
if (!fCanSee()) {
	fRedirect();
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
};
if (isset($_POST['name']) && isset($_POST['path']) && isset($_POST['parent']) && isset($_POST['order'])) {
	if (fTextDatabase($_POST['name'], 61) && fTextDatabase($_POST['path'], 61) && is_numeric($_POST['order']) && is_numeric($_POST['permissions'])) {
		$name = '\'' . fTextDatabase($_POST['name'], 61) . '\'';
		$path = '\'' . fTextDatabase($_POST['path'], 61) . '\'';
		if (isset($_POST['add'])) {
			$queryInsert = 'insert into pages (name, path, parentid, "order", defaultpermissions) values (' . $name . ', ' . $path . ', ' . $_POST['parent'] . ', ' . $_POST['order'] . ', ' . $_POST['permissions'] . ')';
			odbc_exec($conn, $queryInsert);
			$_SESSION['sqlMessage'] = 'Page creation succeeded!';
			$_SESSION['uiState'] = 'active';
		} elseif (!isset($_POST['update'])) {
			$_SESSION['sqlMessage'] = 'Page creation failed!';
			$_SESSION['uiState'] = 'error';
		};
		if (isset($_POST['update'])) {
			$queryUpdate = 'update pages set name = ' . $name . ', path = ' . $path . ', parentid = ' . $_POST['parent'] . ', "order" = ' . $_POST['order'] . ', defaultpermissions = ' . $_POST['permissions'] . 'where id =' . $_POST['update'];
			odbc_exec($conn, $queryUpdate);
			$_SESSION['sqlMessage'] = 'Page update succeeded!';
			$_SESSION['uiState'] = 'active';
		} elseif (!isset($_POST['add'])) {
			$_SESSION['sqlMessage'] = 'Page update failed!';
			$_SESSION['uiState'] = 'error';
		};
	} elseif (!isset($_POST['delete'])) {
		$_SESSION['sqlMessage'] = 'Form is incomplete or invalid!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	$queryDelete = 'delete from pages where id = ' . $_POST['delete'];
	odbc_exec($conn, $queryDelete);
	$queryPermissions = 'delete from permissions where pagefk = ' . $_POST['delete'];
	odbc_exec($conn, $queryPermissions);
	$queryDeleteUpdate = 'update pages set parentid = 0 where parentid = ' . $_POST['delete'];
	odbc_exec($conn, $queryDeleteUpdate);
	$_SESSION['sqlMessage'] = 'Page delete succeeded!';
	$_SESSION['uiState'] = 'active';
} elseif (!isset($_POST['update']) && !isset($_POST['add'])) {
	$_SESSION['sqlMessage'] = 'Page delete failed!';
	$_SESSION['uiState'] = 'error';
};
fRedirect(); ?>