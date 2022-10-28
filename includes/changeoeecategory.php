<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_POST['add']) || isset($_POST['update'])) {
	if (!isset($_POST['order'])) {
		$_SESSION['sqlMessage'] = 'You must provide an order!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	} elseif (!is_numeric($_POST['order'])) {
		$_SESSION['sqlMessage'] = 'You order must be numeric!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
if (isset($_POST['add'])) {
		if ($valid = fTextDatabase($_POST['cname'], 61)) {
			$queryNewCategory = 'insert into oeecategory (name, typeorder)
			values (\'' . $valid . '\', ' . $_POST['order'] . ')';
			odbc_exec($conn, $queryNewCategory);		
			$_SESSION['sqlMessage'] = 'Category added!';
			$_SESSION['uiState'] = 'active';
		} else {
			$_SESSION['sqlMessage'] = 'Category creation failed!';
			$_SESSION['uiState'] = 'error';
		};
};
if (isset($_POST['update'])) {
	if ($valid = fTextDatabase($_POST['cname'], 61)) {
		$queryUpdateCategory = 'update oeecategory
		set name=\'' . $_POST['cname'] . '\', typeorder = ' . $_POST['order'] . '
		where id =' . $_POST['update'];
		odbc_exec($conn, $queryUpdateCategory);
		$_SESSION['sqlMessage'] = 'Category updated!';
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Category failed to update!';
		$_SESSION['uiState'] = 'error';
	};
};
if (isset($_POST['delete'])) {
	$queryDeleteCategory = 'delete from oeecategory
	where id = ' . $_POST['delete'];
	odbc_exec($conn, $queryDeleteCategory);
	$_SESSION['sqlMessage'] = 'Category deleted!';
	$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>