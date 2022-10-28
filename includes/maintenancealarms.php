<?PHP
require_once 'functions.php';
if (!fCanSee(@$_SESSION['permissions']['page'][12] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_POST['alarmid'])) {
	$_SESSION['sqlMessage'] = 'No Alarm Identified!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryPermission = 'select top 1 GroupName
from AlarmMaster
left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup
where alarmid = ' . $_POST['alarmid'];
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryAlarms[] = 'DepartmentFK is null';
	$queryPermission .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 300, 'departmentfk', $aQueryAlarms);
};
$dataPermission = odbc_exec($aConn, $queryPermission);
if (odbc_fetch_row($dataPermission)) {
	$deleteConsolidated = 'delete from alarmconsolidated where alarmid = ' . $_POST['alarmid'];
	odbc_exec($aConn, $deleteConsolidated);
	$deleteMaster = 'delete from alarmmaster where alarmid = ' . $_POST['alarmid'];
	odbc_exec($aConn, $deleteMaster);
	$_SESSION['sqlMessage'] = 'Alarm Deleted!';
	$_SESSION['uiState'] = 'active';
	fRedirect();
} else {
	$_SESSION['sqlMessage'] = 'Alarm not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
odbc_close($aConn); ?>