<?PHP
require_once 'functions.php';
$output = array();
$aDE = fPermissionDE();
$departmentURL = '';
$departmentData = '';
if (isset($aDE['department'])) {
	$departmentData .= 'data-department="' . $aDE['department'] . '" ';
	$departmentURL = 'department=' . $aDE['department'] . '&';
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
		
		$queryAlarm = 'select TagName, Comment, Priority, SUM(duration) as duration, COUNT(duration) as count, alarmtype, groupname, departmentequipmentfk 
		from (select [plantavail].[dbo].[alarmgroup].[departmentequipmentfk], alarmmaster.tagname, comment.comment, alarmconsolidated.alarmtype, alarmconsolidated.priority, alarmmaster.groupname,
		case when alarmconsolidated.returntime = \'9999-12-12 23:59:59.997\'
			then 0
			else datediff(ss, alarmtime, returntime) end as duration
		from alarmconsolidated 
		join alarmmaster on alarmconsolidated.alarmid = alarmmaster.alarmid 
		join comment on comment.commentid = alarmconsolidated.commentid
		left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup 
		where'; 
		if (isset($aDE['department'])) {
			$queryAlarm .= ' departmentfk = ' . $aDE['department'];
			if (isset($aDE['equipment'])) {
				$queryAlarm .= ' and (departmentequipmentfk = ' . $aDE['equipment'] . ' or departmentequipmentfk is null)';
			} else {
				$queryAlarm .= ' and departmentequipmentfk is NULL';
			};
		} else {
			$queryAlarm .= ' departmentfk is NULL';
		};
		$queryAlarm .= ' and 
		((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
		or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
		or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
		and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
		and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
		and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))
		) as temp
		group by TagName, Comment, Priority, GroupName, alarmtype, departmentequipmentfk
		order by groupname asc, TagName asc';
		$dataAlarm = odbc_exec($aConn, $queryAlarm);
		$row = 1;
		$output['oreturn'] = '<table id="ajax-alarm" class="records ajax"><thead><tr><th>Tagname</th><th>Group Name</th><th>Type</th><th>Priority</th><th>Duration</th><th>Count</th><th>Average</th><th><a href="#" id="toggle-alarm-comments">Description</a></th></tr></thead><tbody>';
		while (odbc_fetch_row($dataAlarm)) {
			if ($row % 2 == 0) {
				$rowHeader = 'oddRow';
			} else {
				$rowHeader = 'evenRow';
			};
			$output['oreturn'] .= '<tr class="' . $rowHeader . '"><td><a data-text="Open Table" href="#"><span data-url="includes/ajax.alarm.single.php" data-id="ajax-alarm-single" data-name="' . odbc_result($dataAlarm, 1) . '" class="icon-table icon-hover-hint" ' . $departmentData;
			
			$equipmentUrl = '';
			if (odbc_result($dataAlarm, 8)) {
				$output['oreturn'] .= 'data-equipment="' . odbc_result($dataAlarm, 8) . '" ';
				$equipmentUrl = 'equipment=' . odbc_result($dataAlarm, 8) . '&';
			};
			
			$output['oreturn'] .= '></span></a> <a href="alarm.php?startdate=' . $startDate . '&enddate=' . $endDate . '&tagname=' . odbc_result($dataAlarm, 1) . '">' . odbc_result($dataAlarm, 1) .'</a></td><td><a href="alarms.php?startdate=' . $startDate . '&enddate=' . $endDate . $departmentURL . $equipmentUrl . 'group[]=' . odbc_result($dataAlarm, 7) . '">' . odbc_result($dataAlarm, 7) . '</a></td><td>' . odbc_result($dataAlarm, 6) . '</td><td>' . odbc_result($dataAlarm, 3) . '</td>
			<td data-duration="';
			if (odbc_result($dataAlarm, 4) == "") {
				$output['oreturn'] .= 0;
			} else {
				$output['oreturn'] .= odbc_result($dataAlarm, 4);
			};
			$output['oreturn'] .= '">' . fToTime(odbc_result($dataAlarm, 4)) . '</td>
			<td>' . odbc_result($dataAlarm, 5) . '</td>
			<td data-duration="' . (odbc_result($dataAlarm, 4) / odbc_result($dataAlarm, 5)) . '">' . fToTime(odbc_result($dataAlarm, 4) / odbc_result($dataAlarm, 5)) . '</td>
			<td><a href="#" class="toggle-table-sorter">Show</a></td></tr><tr class="' . $rowHeader . ' hiddenrow"><td colspan ="8">' . odbc_result($dataAlarm, 2) . '</td></tr>';
			$row++;
		};
		$output['oreturn'] .= '</tbody>' . fTableFooter(['id' => 'ajax-alarm', 'cols' => 8, 'totals' => [[0 => 'count-child', 4 => 'time-mean', 5 => 'mean'], [4 => 'time-sum', 5 => 'sum']]]) . '</table>';
		odbc_close($aConn);
};
print(json_encode($output));
?>