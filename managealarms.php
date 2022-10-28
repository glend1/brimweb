<?PHP 
$title = 'Alarm Maintenance';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][12] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$sDate = new DateTime(($localTime));
$sDate->sub(new DateInterval('P15D'));
$sDate = $sDate->format('Y-m-d H:i:s');
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryAlarmPurge = 'select distinct AlarmConsolidated.AlarmId, tagname, groupname, alarmconsolidated.alarmtype, case when alarmconsolidated.AckTime <> \'9999-12-12 23:59:59.997\' then
	\'ACK\'
else 
	\'UNACK\'
end 
+ \' \' +
case when alarmconsolidated.returntime <> \'9999-12-12 23:59:59.997\' then 
	\'RTN\' 
else 
	\'ALM\' 
end as state, alarmconsolidated.Priority, 
(DATEADD(mi, Alarmconsolidated.alarmTimeZoneOffset - (Alarmconsolidated.alarmDaylightAdjustment * 7.5), Alarmconsolidated.alarmtime)) as ralarmtime, comment.comment
from alarmmaster
left join AlarmConsolidated on alarmmaster.AlarmId = AlarmConsolidated.AlarmId
left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup
join Comment on AlarmConsolidated.CommentId = Comment.Commentid
where (returntime = \'9999-12-12 23:59:59.997\' or returntime is null) 
and (DATEADD(mi, Alarmconsolidated.alarmTimeZoneOffset - (Alarmconsolidated.alarmDaylightAdjustment * 7.5), Alarmconsolidated.alarmtime)) < \'' . $sDate . '\'';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryAlarms[] = 'DepartmentFK is null';
	$queryAlarmPurge .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 300, 'departmentfk', $aQueryAlarms);
};
$queryAlarmPurge .= 'order by ralarmtime';
$alarmTable = '<h2>Showing active Alarms from before ' . $sDate . '</h2><table class="records"><thead><tr><th>Tagname</th><th>Groupname</th><th>Type</th><th>State</th><th>Priority</th><th>Alarmtime</th><th>Comment</th><th>Action</th></tr></thead><tbody>';
$dataAlarmPurge = odbc_exec($aConn, $queryAlarmPurge);
$row = 0;
while(odbc_fetch_row($dataAlarmPurge)) {
	$alarmFound = true;
	if ($row % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$row++;
	$alarmTable .= '<tr class="' . $rowHeader . '"><td>' . odbc_result($dataAlarmPurge, 2) . '</td><td>' . odbc_result($dataAlarmPurge, 3) . '</td><td>' . odbc_result($dataAlarmPurge, 4) . '</td><td>' . odbc_result($dataAlarmPurge, 5) . '</td><td>' . odbc_result($dataAlarmPurge, 6) . '</td><td>' . substr(odbc_result($dataAlarmPurge, 7), 0, -4) . '</td><td><a href="#" class="toggle-table-sorter">Show</a></td><td><form action="includes/maintenancealarms.php" method="post"><input type="hidden" name="alarmid" value="' . odbc_result($dataAlarmPurge, 1) . '" /><input type="submit" value="Delete!"/></form></td></tr><tr class="' . $rowHeader . ' hiddenrow"><td colspan ="8">' . odbc_result($dataAlarmPurge, 8) . '</td></tr>';
};
$alarmTable .= '</tbody></table>
<script type="text/javascript">
	$(function() {
		showDisc(".records");
	});	
</script>';
if (isset($alarmFound)) {
	$stdOut .= $alarmTable;
} else {
	$stdOut .= '<h2>No open batches from before ' . $sDate . '</h2>';
};
odbc_close($aConn);
require_once 'includes/footer.php'; ?>