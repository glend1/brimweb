<?PHP 
$title = 'Process Alarm';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'Please select an Alarm!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryComment = 'select top 1 tagname, Comment, groupname, alarmmaster.alarmtype, alarmmaster.priority, ackoperatorname, acknodename, causeid, case when alarmconsolidated.AckTime <> \'9999-12-12 23:59:59.997\' then
	\'ACK\' 
else 
	\'UNACK\'
end 
+ \' \' +
case when alarmconsolidated.returntime <> \'9999-12-12 23:59:59.997\' then 
	\'RTN\' 
else 
	\'ALM\' 
end 
as alarmstate, DATEADD(mi, Alarmconsolidated.alarmTimeZoneOffset - (Alarmconsolidated.alarmDaylightAdjustment * 7.5), Alarmconsolidated.alarmtime) AS alarmtime, 
DATEADD(mi, Alarmconsolidated.ackTimeZoneOffset - (Alarmconsolidated.ackDaylightAdjustment * 7.5), Alarmconsolidated.acktime) AS acktime, 
case when Alarmconsolidated.acktime = \'9999-12-12 23:59:59.997\'
	then null
	else datediff(ss, Alarmconsolidated.alarmtime, Alarmconsolidated.acktime) end as unackdur,
DATEADD(mi, Alarmconsolidated.returnTimeZoneOffset - (Alarmconsolidated.returnDaylightAdjustment * 7.5), Alarmconsolidated.returntime) AS returntime, 
case when alarmconsolidated.returntime = \'9999-12-12 23:59:59.997\'
	then null
	else datediff(ss, alarmconsolidated.alarmtime, alarmconsolidated.returntime) end as almdur
from AlarmMaster
join AlarmConsolidated on AlarmConsolidated.AlarmId = AlarmMaster.AlarmId
join Comment on AlarmConsolidated.CommentId = Comment.Commentid
where alarmmaster.alarmid = \'' . $_GET['id'] . '\'';
$dataComment = odbc_exec($aConn, $queryComment);
if(odbc_fetch_row($dataComment)) {
	$alarmHeader = '<h2>' . odbc_result($dataComment, 1) . ': ' . odbc_result($dataComment, 2) . '</h2>';
	$alarmBody = '<h3>Alarm Details</h3><table class="overviewtable"><thead><tr><th>Groupname</th><th>Type</th><th>Priority</th><th>Operator Name</th><th>Node Name</th><th>Cause</th><th>State</th></tr></thead><tbody><tr class="oddRow">
	<td>' . odbc_result($dataComment, 3) . '</td><td>' . odbc_result($dataComment, 4) . '</td><td>' . odbc_result($dataComment, 5) . '</td><td>' . odbc_result($dataComment, 6) . '</td><td>' . odbc_result($dataComment, 7) . '</td><td>' . odbc_result($dataComment, 8) . '</td><td>' . odbc_result($dataComment, 9) . '</td></tr></tbody></table><h3>Alarm Timings</h3>
	<table class="overviewtable"><thead><tr><th>Alarm Time</th><th>Acknowledge Time</th><th>Acknowledge Duration</th><th>Return Time</th><th>Return Duration</th></tr></thead><tbody><tr class="evenRow"><td>' . substr(odbc_result($dataComment, 10), 0, -4) . '</td><td>' . substr(odbc_result($dataComment, 11), 0, -4) . '</td><td>' . fToTime(odbc_result($dataComment, 12)) . '</td><td>' . substr(odbc_result($dataComment, 13), 0, -4) . '</td><td>' . fToTime(odbc_result($dataComment, 14)) . '</td></tr></tbody></table>';
} else {
	$_SESSION['sqlMessage'] = 'No Alarm found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= $alarmHeader . '<div id="quicktrendholder"><div id="sidebar"><h3>Tagnames</h3><div id="processlegend"></div><div id="tagdescription"></div></div><div id="quicktrend"></div><div id="datetimestampcontainer"><div id="datetimestamp"></div></div></div><h3 id="trendhint">Select a trend from the <a href="#trends">bottom</a> of this page to view Historical Data</h3>' . $alarmBody . '<a name="trends"></a>' . fGetTrends(strtotime(substr(odbc_result($dataComment, 10), 0, -4)) * 1000, strtotime(substr(odbc_result($dataComment, 13), 0, -4)) * 1000, 'local', ['trend', 'alarm']) . '
<script type="text/javascript">
	var iDateStart = ' . (strtotime(substr(odbc_result($dataComment, 10), 0, -4)) * 1000) . ';
	var iDateEnd = ' . (strtotime(substr(odbc_result($dataComment, 13), 0, -4)) * 1000) . ';
</script>
<script language="javascript" type="text/javascript" src="js/quicktrend.js"></script>';
odbc_close($aConn);
$hookReplace['help'] = $helptext['linehoverany'] . $helptext['linemarkings'] . $helptext['recordswap']  . $helptext['dynrecordswap'] . $helptext['autohighlight'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] ;
require_once 'includes/footer.php'; ?>