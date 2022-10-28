<?PHP 
$title = 'Manage Department Equipment';
require_once 'includes/header.php';
if (isset($_SESSION['admin']['departmentadmin']) && isset($_SESSION['admin']['groupadmin'])) {
	$bAdminDept = true;
};
if (!fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managedepartmentequipment.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managedepartmentequipment.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'dename\']");
			var departmentoption = fields.find("[name=\'departmentid\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "Department Equipment", 1, 60 );
			bValid = bValid && checkOptionSelected( departmentoption, "You must select a Department.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$sDepartmentEquipmentForms = '';
if (fCanSee(isset($bAdminDept))) {
	$sDepartmentSelectAdmin = '<select id="departmentid" name="departmentid"><option value="none">None</option>';
		$queryDepartmentSelectAdmin = 'select id, name from department';
		if ($_SESSION['id'] != 1) {
			fOrThem($_SESSION['permissions']['department'], 200, 'id', $aQuerySelectDepartmentAdmin);
			fGenerateWhere($queryDepartmentSelectAdmin, $aQuerySelectDepartmentAdmin);
		};
		$dataDepartmentSelectAdmin = odbc_exec($conn, $queryDepartmentSelectAdmin);
		while (odbc_fetch_row($dataDepartmentSelectAdmin)) {
			$aDepartmentSelectAdmin[odbc_result($dataDepartmentSelectAdmin, 1)] = odbc_result($dataDepartmentSelectAdmin, 2);
			$sDepartmentSelectAdmin .= '<option value="' . odbc_result($dataDepartmentSelectAdmin, 1) . '" >' . odbc_result($dataDepartmentSelectAdmin, 2) . '</option>';
		};
		$sDepartmentSelectAdmin .= '</select>';
	$sDepartmentEquipmentForms .= '<form class="createform" action="includes/changedepartmentequipment.php" method="post"><h3>Create Department Equipment</h3>
	<div class="formelement"><label for="name">Equipment</label><br /><input type="text" id="name" name="dename" value=""><span class="required"> * </span></div>
	<div class="formelement"><label for="departmentid">Department</label><br />' . $sDepartmentSelectAdmin . '<span class="required"> * </span></div>
	<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
};
function fDepartmentSelect ($id) {
	GLOBAL $aDepartmentSelectAdmin;
	$out = '<select id="departmentid" name="departmentid"><option value="none">None</option>';
	foreach ($aDepartmentSelectAdmin as $key => $value) {
		$out .= '<option ';
		if ($key == $id) {
			$out .= 'selected';
		};
		$out .= ' value="' . $key . '" >' . $value . '</option>';
	};
	$out .= '</select>';
	return $out;
};
$queryDepartment = 'select id, Name, departmentfk from Departmentequipment';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['department'], 200, 'departmentfk', $aQueryDepartment);
	fGenerateWhere($queryDepartment, $aQueryDepartment);
};
if (isset($_GET['search'])) {
	if ($_SESSION['id'] != 1) {
		$queryDepartment .= ' and name like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryDepartment .= ' where name like \'%' . $_GET['search'] . '%\'';
	};
};
$dataDepartment = odbc_exec($conn, $queryDepartment);
$sDepartmentEquipmentForms .= '<table class="recordssmall"><thead><tr><th>Rename/Reassign Department Equipment</th><th>Action</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataDepartment)) {
	if ($row % 2 == 0) {
		$sDepartmentEquipmentForms .= '<tr class="oddRow">';
	} else {
		$sDepartmentEquipmentForms .= '<tr class="evenRow">';
	};
	$row++;
	$sDepartmentEquipmentForms .= '<td><form action="includes/changedepartmentequipment.php" method="post">Equipment:<input type="hidden" name="update" value="' . odbc_result($dataDepartment, 1) . '"><input type="text" name="dename" value="' . odbc_result($dataDepartment, 2) . '"><span class="required"> * </span>
	 Department:' . fDepartmentSelect(odbc_result($dataDepartment, 3)) . '<span class="required"> * </span>
	<input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changedepartmentequipment.php" method="post"><input type="hidden" name="delete" value="' . odbc_result($dataDepartment, 1) . '">
	<input type="hidden" name="departmentid" value="' . odbc_result($dataDepartment, 3) . '">
	<input type="submit" value="Delete"></form></td></tr>';
};
$sDepartmentEquipmentForms .= '</tbody></table>';
$stdOut .= $sDepartmentEquipmentForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$departmentNotice = '';
if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$departmentNotice = '<div class="notice">To Create a Department <a href="managedepartment.php">Click Here</a>.</div>';
};
$hookReplace['help'] = $departmentNotice . $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>