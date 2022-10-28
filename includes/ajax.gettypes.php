<?PHP
require_once 'functions.php';
$output = array();
if (!isset($_GET['departmentequipment'])) {
	$output['status'] = 'department equipment not found.';
	$error = true;
} else {
	$queryDeptEquip = 'select top 1 departmentfk from departmentequipment where id = ' . $_GET['departmentequipment'];
	$dataDeptEquip = odbc_exec($conn, $queryDeptEquip);
	if (odbc_fetch_row($dataDeptEquip)) {
		$department = odbc_result($dataDeptEquip, 1);
	} else {
	$output['status'] = 'department not found.';
	$error = true;
	};
};
if (!isset($_GET['discipline'])) {
	$output['status'] = 'discipline not found.';
	$error = true;
};
if (!isset($_GET['category'])) {
	$output['status'] = 'category not found.';
	$error = true;
};
if (!fCanSee(@$_SESSION['permissions']['department'][$department] >= 100)) {
	$output['status'] = 'you do not have permission.';
	$error = true;
};
if (!isset($error)) {
	$queryTypes = 'select type.id, name 
	from type 
	join oeename on oeename.id = oeenamefk
	where departmentequipmentfk = ' . $_GET['departmentequipment'] . ' and disciplinefk = ' . $_GET['discipline'] . ' and oeecategoryfk = ' . $_GET['category'] . '
	order by name asc';
	$dataTypes = odbc_exec($conn, $queryTypes);
	$out = '';
	while(odbc_fetch_row($dataTypes)) {
		$typesFound = true;
		$out .= '<option value="' . odbc_result($dataTypes, 1) . '">' . odbc_result($dataTypes, 2) . '</option>';
	}
	if (isset($typesFound)) {
		$output['status'] = 'complete';
		$output['oreturn'] = $out;
	} else {
		$output['status'] = 'not found in database.';
	};
};
print(json_encode($output));
?>