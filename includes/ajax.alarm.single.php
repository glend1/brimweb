<?PHP
require_once 'functions.php';
$output = array();
$aDE = fPermissionDE();
if (!isset($_GET['alarm'])) {
	$output['status'] = 'alarm not found.';
	$error = true;
};
if (!isset($_GET['startdate'])) {
	$output['status'] = 'start date not found.';
	$error = true;
};
if (!isset($_GET['enddate'])) {
	$output['status'] = 'end date not found.';
	$error = true;
};
if (!isset($error)) {
	$startDate = date('Y-m-d H:i:s', (($_GET['startdate'] / 1000)));
	$endDate = date('Y-m-d H:i:s', (($_GET['enddate'] / 1000) + 1));
	$output['status'] = 'complete';		
	$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
	$queryAlarm = 'select 
	case when alarmconsolidated.AckTime <> \'9999-12-12 23:59:59.997\' then
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
	as alarmstate, alarmconsolidated.alarmtype, alarmconsolidated.priority, DATEADD(mi, Alarmconsolidated.alarmTimeZoneOffset - (Alarmconsolidated.alarmDaylightAdjustment * 7.5), Alarmconsolidated.alarmtime) AS alarmtime, 
	DATEADD(mi, Alarmconsolidated.ackTimeZoneOffset - (Alarmconsolidated.ackDaylightAdjustment * 7.5), Alarmconsolidated.acktime) AS acktime, 
	case when Alarmconsolidated.acktime = \'9999-12-12 23:59:59.997\'
		then null
		else datediff(ss, Alarmconsolidated.alarmtime, Alarmconsolidated.acktime) end as unackdur,
	DATEADD(mi, Alarmconsolidated.returnTimeZoneOffset - (Alarmconsolidated.returnDaylightAdjustment * 7.5), Alarmconsolidated.returntime) AS returntime, 
	case when alarmconsolidated.returntime = \'9999-12-12 23:59:59.997\'
		then null
		else datediff(ss, alarmconsolidated.alarmtime, alarmconsolidated.returntime) end as almdur, comment.comment, alarmconsolidated.alarmid
	from alarmconsolidated 
	join alarmmaster on alarmconsolidated.alarmid = alarmmaster.alarmid 
	join comment on comment.commentid = alarmconsolidated.commentid
	where alarmmaster.TagName = \'' . $_GET['alarm'] . '\' and 
	((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
	or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
	or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
	and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
	and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
	and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))
	order by alarmtime';
	$dataAlarm = odbc_exec($aConn, $queryAlarm);
	$output['oreturn'] = '<table id="ajax-alarm-single" class="records ajax"><thead><tr><th>State</th><th>Type</th><th>Priority</th><th>Start Date/Time</th><th>Acknowledge Date/Time</th><th>Acknowledge Duration</th><th>Return Date/Time</th><th>Return Duration</th></tr></thead><tbody>';
	$row = 1;
	while(odbc_fetch_row($dataAlarm)) {
		if ($row % 2 == 0) {
			$output['oreturn'] .= '<tr class="oddRow">';
		} else {
			$output['oreturn'] .= '<tr class="evenRow">';
		};
		$row++;
		$output['oreturn'] .= '<td><a data-text="Show On Chart" href="#top"><span class="icon-bar-chart icon-hover-hint" data-start="' . (strtotime(odbc_result($dataAlarm, 4)) * 1000) . '" data-end="' . (strtotime(odbc_result($dataAlarm, 7)) * 1000) . '"></span></a> <a href="alarmsingle.php?id=' . odbc_result($dataAlarm, 10) . '">' . odbc_result($dataAlarm, 1) . '</a></td>';
		$output['oreturn'] .= '<td>' . odbc_result($dataAlarm, 2) . '</td>';
		$output['oreturn'] .= '<td>' . odbc_result($dataAlarm, 3) . '</td>';
		$output['oreturn'] .= '<td>' . substr(odbc_result($dataAlarm, 4), 0, -4) . '</td>';
		if (odbc_result($dataAlarm, 5) == '9999-12-13 00:59:59.997') {
			$output['oreturn'] .= '<td></td>';
		} else {
			$output['oreturn'] .= '<td>' . substr(odbc_result($dataAlarm, 5), 0, -4) . '</td>';
		};
		$output['oreturn'] .= '<td data-duration="';
		if (odbc_result($dataAlarm, 6) == "") {
			$output['oreturn'] .= 0;
		} else {
			$output['oreturn'] .= odbc_result($dataAlarm, 6);
		};
		$output['oreturn'] .= '">' . fToTime(odbc_result($dataAlarm, 6)) . '</td>';
		if (odbc_result($dataAlarm, 7) == '9999-12-13 00:59:59.997') {
			$output['oreturn'] .= '<td></td>';
		} else {
			$output['oreturn'] .= '<td>' . substr(odbc_result($dataAlarm, 7), 0, -4) . '</td>';
		};
		$output['oreturn'] .= '<td data-duration="';
		if (odbc_result($dataAlarm, 8) == "") {
			$output['oreturn'] .= 0;
		} else {
			$output['oreturn'] .= odbc_result($dataAlarm, 8);
		};
		$output['oreturn'] .= '">' . fToTime(odbc_result($dataAlarm, 8)) . '</td>';
		$output['oreturn'] .= '</tr>';
	}
	$output['oreturn'] .= '</tbody>' . fTableFooter(['id' => 'ajax-alarm-single', 'cols' => 8, 'totals' => [[3 => "count", 5 => 'time-mean', 7 => 'time-mean'], [5 => 'time-sum', 7 => 'time-sum']]]) . '</table>';
	odbc_close($aConn);
};
print(json_encode($output));
?>