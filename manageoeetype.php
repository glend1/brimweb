<?PHP $title = 'Manage OEE Types';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['departmentequipment'])) {
	$queryDeptEquip = 'select top 1 departmentfk from departmentequipment where id = ' . $_GET['departmentequipment'];
	$dataDeptEquip = odbc_exec($conn, $queryDeptEquip);
	if (odbc_fetch_row($dataDeptEquip)) {
		$department = odbc_result($dataDeptEquip, 1);
	} else {
		$_SESSION['sqlMessage'] = 'Associated department not found!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
} else {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!fCanSee(@$_SESSION['permissions']['department'][$department] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!(isset($department) && isset($_GET['discipline']) && isset($_GET['oeecategory'])))	{
	$_SESSION['sqlMessage'] = 'Please complete the form!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (@$_SESSION['permissions']['page'][1] >= 300 && @$_SESSION['permissions']['department'][$department] >= 300) {
	$bAdmin = TRUE;
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageoeetype.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageoeetype.php' . fQueryString(['exclude' => ['search']]) . '"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text" />
<input name="departmentequipment" type="hidden" value="' . $_GET['departmentequipment'] . '" />
<input name="discipline" type="hidden" value="' . $_GET['discipline'] . '" />
<input name="oeecategory" type="hidden" value="' . $_GET['oeecategory'] . '" />
<input type="submit" value="Search!" /></form>';
$queryType = 'select type.id, oeename.name
from type
join oeename on oeename.id = oeenamefk
where disciplinefk = ' . $_GET['discipline'] . ' and departmentequipmentfk = ' . $_GET['departmentequipment'] . ' and oeecategoryfk = ' . $_GET['oeecategory'];
if (isset($_GET['search'])) {
	$queryType .= ' and name like \'%' . $_GET['search'] . '%\'';
};
$queryType .= ' order by oeename.name asc';
$dataType = odbc_exec($conn, $queryType);
if (fCanSee(isset($bAdmin))) {
	$stdOut .= '<form class="createform" action="changeoeetypeform.php" method="get"><h3>Create Type</h3>
	<input name="departmentequipment" type="hidden" value="' . $_GET['departmentequipment'] . '" />
	<input name="discipline" type="hidden" value="' . $_GET['discipline'] . '" />
	<input name="category" type="hidden" value="' . $_GET['oeecategory'] . '" />
	<input type="submit" value="Create" name="add" class="validatetextbutton" /></form>';
};
$stdOut .= '<table class="recordssmall"><thead><tr><th>Name</th><th>Change Types</th><th>Delete Types</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataType)) {
	if ($row % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$row++;
	$stdOut .= '<td>' . odbc_result($dataType, 2) . '</td><td><a href="changeoeetypeform.php?id=' . odbc_result($dataType, 1) . '&update=true">Change Type</a></td>';
	if (fCanSee(isset($bAdmin))) {
		$stdOut .= '<td><form method="post" action="includes/changeoeetype.php"><input type="hidden" name="delete" value="' . odbc_result($dataType, 1) . '"><input type="submit" value="Delete"></form></td>';
	};
	$stdOut .= '</tr>';
};
$stdOut .= '</tbody></table>';
$hookReplace['help'] .= $helptext['search'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>