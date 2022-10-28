<?PHP 
$title = 'Equipment Admin';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][20] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="equipadmin.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="equipadmin.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryEquip = 'select distinct *
from (select distinct unitorconnection
from [BatchHistory].[dbo].[batchdetail]
union
select distinct unitorconnection
from [oldBatchHistory].[dbo].[batchdetail]) as temp
where unitorconnection <> \'\'
order by UnitOrConnection';
$dataEquip = odbc_exec($bConn, $queryEquip);
while(odbc_fetch_row($dataEquip)) {
	$aEquips[] = odbc_result($dataEquip, 1);
};
foreach ($aEquips as $needle) {	
	foreach ($aEquips as $haystack) {
		if (strpos($haystack, $needle) !== FALSE && $needle != $haystack) {
			$needles[$needle] = TRUE;
			$haystacks[$haystack] = TRUE;
		};
	};
};
foreach ($aEquips as $hay) {
	if (isset($needles[$hay]) || !isset($haystacks[$hay])) {
		$aSorted[] = $hay;
		$aUnused[$hay] = TRUE;
	};
};
$queryIncludeJoin = 'select id, departmentfk, equip from equip order by equip';
$dataIncludeJoin = odbc_exec($conn, $queryIncludeJoin);
while(odbc_fetch_row($dataIncludeJoin)) {
	$aDepartments[odbc_result($dataIncludeJoin, 2)]['include'][] = ['id' => odbc_result($dataIncludeJoin, 1), 'equip' => odbc_result($dataIncludeJoin, 3)];
	$aExclude[odbc_result($dataIncludeJoin, 2)][] = odbc_result($dataIncludeJoin, 3);
	$unset = true;
	while ($unset = in_array_regex(odbc_result($dataIncludeJoin, 3), $aUnused, 'key')) {
		unset($aUnused[$unset]);
	};
};
$queryExcludeJoin = 'select id, departmentfk, eequip from eequip order by eequip';
$dataExcludeJoin = odbc_exec($conn, $queryExcludeJoin);
while(odbc_fetch_row($dataExcludeJoin)) {
	$aDepartments[odbc_result($dataExcludeJoin, 2)]['exclude'][] = ['id' => odbc_result($dataExcludeJoin, 1), 'equip' => odbc_result($dataExcludeJoin, 3)];
	$aExclude[odbc_result($dataExcludeJoin, 2)][] = odbc_result($dataExcludeJoin, 3);
	$unset = true;
	while ($unset = in_array_regex(odbc_result($dataExcludeJoin, 3), $aUnused, 'key')) {
		unset($aUnused[$unset]);
	};
};
function fEquips($exclude = array(), $selected = NULL) {
	global $aSorted;
	$sEquipSelect = '<select name="equip"><option>none</option>';
	foreach ($aSorted as $key => $value) {
		if (!in_array_regex_reverse($value, $exclude) || $selected == $value) {
			$sEquipSelect .= '<option value="' . $value . '" ';
			if ($selected == $value) {
				$sEquipSelect .= 'selected';
			};
			$sEquipSelect .= '>' . $value . '</option>';
		};
	};
	$sEquipSelect .= '</select>';
	return $sEquipSelect;
};
if (!empty($aUnused)) {
	$extraNotifications .= '<div class="ui-state-error"><span class="icon-remove-sign"></span><b>Unassigned Equipment:</b> ';
	$sep = '';
	foreach ($aUnused as $name => $bool) {
		$extraNotifications .= $sep . $name;
		$sep = ', ';
	};
	$extraNotifications .= '</div>';
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatebutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var areaoption = fields.find("[name=\'equip\']");
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkOptionSelected( areaoption, "You must select an Equipment.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$stdOut .= '<table class="records"><thead><tr><th>Department</th><th>Include</th><th>Exclude</th></tr></thead><tbody>';
$queryDepartment = 'select id, name from department';
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
$queryDepartment .= ' order by name';
$dataDepartment = odbc_exec($conn, $queryDepartment);
$row = 0;
while(odbc_fetch_row($dataDepartment)) {
	if ($row % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$row++;
	$stdOut .= '<td>' . odbc_result($dataDepartment, 2) . '</td><td>';
	$excludeOut = '';
	if (isset($aDepartments[odbc_result($dataDepartment, 1)]['include'])) {
		foreach ($aDepartments[odbc_result($dataDepartment, 1)]['include'] as $key => $array) {
			$stdOut .= '<form method="post" action="includes/changeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" />' . fEquips($aExclude[(odbc_result($dataDepartment, 1))], $array['equip']) . '<span class="required"> * </span><input class="validatebutton" type="submit" name="update" value="Update" /></form>';
			if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
				$stdOut .= '<form method="post" action="includes/changeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" /><input type="submit" name="delete" value="Delete" /></form>';
			};
			$stdOut .= '<br />';
		};
	};
	if (isset($aDepartments[odbc_result($dataDepartment, 1)]['exclude'])) {
		foreach ($aDepartments[odbc_result($dataDepartment, 1)]['exclude'] as $key => $array) {
			$excludeOut .= '<form method="post" action="includes/changeeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" />' . fEquips($aExclude[(odbc_result($dataDepartment, 1))], $array['equip']) . '<span class="required"> * </span><input class="validatebutton" type="submit" name="update" value="Update" /></form>';
			if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
				$excludeOut .= '<form method="post" action="includes/changeeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" /><input type="submit" name="delete" value="Delete" /></form>';
			};
			$excludeOut .= '<br />';
		};
	};
	if (isset($aExclude[(odbc_result($dataDepartment, 1))])) {
		$otherEquips = fEquips($aExclude[(odbc_result($dataDepartment, 1))]);
	} else {
		$otherEquips = fEquips();
	};
	if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
		$stdOut .= '<form method="post" action="includes/changeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" />' . $otherEquips . '<span class="required"> * </span><input class="validatebutton" type="submit" name="add" value="Include" /></form>';
	};
	$stdOut .= '</td><td>' . $excludeOut;
	if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
		$stdOut .= '<form method="post" action="includes/changeeequip.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" />' . $otherEquips . '<span class="required"> * </span><input class="validatebutton" type="submit" name="add" value="Exclude" /></form>';
	};
	$stdOut .= '</td></tr>';
};
$stdOut .= '</tbody></table><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$hookReplace['help'] .= '<div class="notice">To add Departments <a href="managedepartment.php">Click Here</a></div>';
};
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>