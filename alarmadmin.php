<?PHP 
$title = 'Alarm Admin';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][12] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="alarmadmin.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="alarmadmin.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryAlarm = 'select distinct groupname from AlarmMaster order by groupname';
$dataAlarm = odbc_exec($aConn, $queryAlarm);
while(odbc_fetch_row($dataAlarm)) {
	$aAlarms[] = odbc_result($dataAlarm, 1);
	$aUnused[strtoupper(odbc_result($dataAlarm, 1))] = true;
};
$queryJoin = 'select id, departmentfk, alarmgroup, departmentequipmentfk from alarmgroup order by alarmgroup';
$dataJoin = odbc_exec($conn, $queryJoin);
while(odbc_fetch_row($dataJoin)) {
	$aDepartments[odbc_result($dataJoin, 2)][] = ['id' => odbc_result($dataJoin, 1), 'alarm' => odbc_result($dataJoin, 3), 'equipment' => odbc_result($dataJoin, 4)];
	$aExclude[odbc_result($dataJoin, 2)][] = odbc_result($dataJoin, 3);
	unset($aUnused[strtoupper(odbc_result($dataJoin, 3))]);
};
$queryEquipment = 'select departmentfk, id, name from departmentequipment order by name';
$dataEquipment = odbc_exec($conn, $queryEquipment);
while(odbc_fetch_row($dataEquipment)) {
	$aEquipment[odbc_result($dataEquipment, 1)][odbc_result($dataEquipment, 2)] = odbc_result($dataEquipment, 3);
};
function fEquipment($department = NULL, $selected = NULL) {
	global $aEquipment;
	$sEquipmentSelect = '<select name="equipment"><option>none</option>';
	if (isset($aEquipment[$department])) {
		foreach ($aEquipment[$department] as $key => $value) {
			$sEquipmentSelect .= '<option value="' . $key . '" ';
			if ($selected == $key) {
				$sEquipmentSelect .= 'selected';
			};
			$sEquipmentSelect .= '>' . $value . '</option>';
		};
	};
	$sEquipmentSelect .= '</select>';
	return $sEquipmentSelect;
};
function fAlarms($exclude = array(), $selected = NULL) {
	global $aAlarms;
	$sAlarmSelect = '<select name="alarm"><option>none</option>';
	foreach ($aAlarms as $key => $value) {
		if (!in_array($value, $exclude) || $selected == $value) {
			$sAlarmSelect .= '<option value="' . $value . '" ';
			if ($selected == $value) {
				$sAlarmSelect .= 'selected';
			};
			$sAlarmSelect .= '>' . $value . '</option>';
		};
	};
	$sAlarmSelect .= '</select>';
	return $sAlarmSelect;
};
if (!empty($aUnused)) {
	$extraNotifications .= '<div class="ui-notif-error"><span class="icon-remove-sign"></span><b>Unassigned Alarm Groups:</b> ';
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
			var areaoption = fields.find("[name=\'alarm\']");
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkOptionSelected( areaoption, "You must select an Alarm Group.");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$stdOut .= '<table class="records"><thead><tr><th>Department</th><th>Create/Update/Delete</th></tr></thead><tbody>';
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
	if (isset($aExclude[(odbc_result($dataDepartment, 1))])) {
		foreach ($aDepartments[odbc_result($dataDepartment, 1)] as $key => $array) {
			$stdOut .= '<form method="post" action="includes/changealarm.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" /><label>Alarm Group: </label>' . fAlarms($aExclude[(odbc_result($dataDepartment, 1))], $array['alarm']) . '<span class="required"> * </span><label>Equipment: </label>' . fEquipment(odbc_result($dataDepartment, 1), $array['equipment']) . '<input class="validatebutton" type="submit" name="update" value="Update" /></form>';
			if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
				$stdOut .= '<form method="post" action="includes/changealarm.php"><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" /><input type="hidden" name="id" value="' . $array['id'] . '" /><input type="submit" name="delete" value="Delete" /></form>';
			};
			$stdOut .= '<br />';
		};
		$resAlarms = fAlarms($aExclude[(odbc_result($dataDepartment, 1))]);
	} else {
		$resAlarms = fAlarms();
	};
	if (fCanSee(@$_SESSION['permissions']['department'][odbc_result($dataDepartment, 1)] >= 300)) {
		$stdOut .= '<form method="post" action="includes/changealarm.php"><label>Alarm Group: </label><input type="hidden" name="department" value="' . odbc_result($dataDepartment, 1) . '" />' . $resAlarms . '<span class="required"> * </span><label>Equipment: </label>' . fEquipment(odbc_result($dataDepartment, 1)) . '<input class="validatebutton" type="submit" name="add" value="Add" /></form></td></tr>';
	};
};
$stdOut .= '</tbody></table><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$hookReplace['help'] .= '<div class="notice">To Create a Department Equipment <a href="managedepartmentequipment.php">Click Here</a>.</div>';
};
odbc_close($aConn);
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>