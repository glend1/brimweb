<?PHP 
$title = 'Manage Departments';
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
$hookReplace['searchform'] = '<form id="search" action="managedepartment.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managedepartment.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'dname\']");
			var newuseroption = fields.find("[name=\'groupadmin\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "department", 1, 60 );
			bValid = bValid && checkOptionSelected( newuseroption, "You must select a group.");
			if (bValid == false) {
				return false;
			};
		});
		$(".validatetextbutton2").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'dname\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "department", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryDepartment = 'select id, Name from Department';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['department'], 200, 'id', $aQueryDepartment);
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
$sDepartmentForms = '';
if (fCanSee(isset($bAdminDept))) {
	$sDepartmentForms .= '<form class="createform" action="includes/changedepartment.php" method="post"><h3>Create Department</h3>
	<div class="formelement"><label for="name">Department Name</label><br /><input type="text" id="name" name="dname" value=""><span class="required"> * </span></div>
	<div class="formelement"><label for="groupadmin">Group Owner</label><br />' . fGroupSelect() . '<span class="required"> * </span></div>
	<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
};
$sDepartmentForms .= '<table class="recordssmall"><thead><tr><th>Rename Department</th><th>Delete Deparmtent</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataDepartment)) {
	if ($row % 2 == 0) {
		$sDepartmentForms .= '<tr class="oddRow">';
	} else {
		$sDepartmentForms .= '<tr class="evenRow">';
	};
	$row++;
	$sDepartmentForms .= '<td><form action="includes/changedepartment.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataDepartment, 1) . '"><input type="text" name="dname" value="' . odbc_result($dataDepartment, 2) . '"><span class="required"> * </span><input class="validatetextbutton2" type="submit" value="Update"></form></td>
	<td><form action="includes/changedepartment.php" method="post">';
	fSetVar($_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)]);
	if (fCanSee($_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
		$sDepartmentForms .= '<input type="hidden" name="delete" value="' . odbc_result($dataDepartment, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
	};
};
$sDepartmentForms .= '</tbody></table>';
$stdOut .= $sDepartmentForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'] . $helptext['groupadmin'];
require_once 'includes/footer.php'; ?>