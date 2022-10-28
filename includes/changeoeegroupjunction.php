<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!(isset($_POST['id']) && isset($_POST['options']) && (isset($_POST['delete']) || isset($_POST['add'])))) {
	$_SESSION['sqlMessage'] = 'Please Complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryType = 'select top 1 type.id
from type
join departmentequipment on departmentequipmentfk = departmentequipment.id
where type.id = ' . $_POST['id'];
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$queryType .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 200, 'departmentfk');
};
$dataType = odbc_exec($conn, $queryType);
if (!odbc_fetch_row($dataType)) {
	$_SESSION['sqlMessage'] = 'Type not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
} else {
	$permission = true;
};
$where = '';
$sep = '';
if (isset($_POST['delete'])) {
	foreach($_POST['options'] as $value) {
		$where .= $sep . 'oeegroupfk = ' . $value;
		$sep = ' or ';
	};
	$queryDelete = 'delete from oeegroupjunction
	where oeetypefk = ' . $_POST['id'] . ' and (' . $where . ')';
	odbc_exec($conn, $queryDelete);		
	$_SESSION['sqlMessage'] = 'Group(s) removed!';
	$_SESSION['uiState'] = 'active';
};
if (isset($_POST['add'])) {
	foreach($_POST['options'] as $value) {
		$where .= $sep . '(' . $value . ', ' . $_POST['id'] . ')';
		$sep = ', ';
	};
	$queryAdd = 'insert into oeegroupjunction (oeegroupfk, oeetypefk)
	Values ' . $where;
	odbc_exec($conn, $queryAdd);		
	$_SESSION['sqlMessage'] = 'Group(s) added!';
	$_SESSION['uiState'] = 'active';
};
fRedirect(); ?>