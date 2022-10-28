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
if (!isset($error)) {
	$queryCategory = '
	select distinct OEECategoryFK, OEECategory.name
	from type
	join OEECategory on OEECategory.ID = oeecategoryfk
	where departmentequipmentfk = ' . $_GET['departmentequipment'] . '
	order by name asc';
	$dataCategory = odbc_exec($conn, $queryCategory);
	$out = '';
	while(odbc_fetch_row($dataCategory)) {
		$categoryFound = true;
		$out .= '<option value="' . odbc_result($dataCategory, 1) . '">' . odbc_result($dataCategory, 2) . '</option>';
	}
	if (isset($categoryFound)) {
		$output['status'] = 'complete';
		$output['oreturn'] = $out;
	} else {
		$output['status'] = 'not found in database.';
	};
};
print(json_encode($output));
?>