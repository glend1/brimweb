<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 7);
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$page = 1;
if (isset($_GET['page'])) {
	$page += $_GET['page'];
};
$items = 100;
$queryAlarm = 'select * from (select top ' . $items . ' * 
from (
SELECT TOP ' . ($page * $items) . ' DATEADD(mi, OriginationTimeZoneOffset - (OriginationDaylightAdjustment * 7.5), OriginationTime) AS OriginationTime, alarmmaster.priority, alarmmaster.alarmtype, groupname, tagname, departmentfk, alarmmaster.alarmid, departmentequipmentfk, department.name as departmentname, departmentequipment.name as equipmentname, case when alarmconsolidated.returntime = \'9999-12-12 23:59:59.997\' then null else datediff(ss, alarmconsolidated.alarmtime, alarmconsolidated.returntime) end as almdur, alarmstate
FROM alarmmaster
left join [plantavail].[dbo].[alarmgroup] on alarmgroup = groupname
left join [plantavail].[dbo].[department] as department on departmentfk = department.id
left join (select id, name from [plantavail].[dbo].[departmentequipment]) as departmentequipment on departmentequipmentfk = departmentequipment.id
join (select *,  case when alarmconsolidated.AckTime <> \'9999-12-12 23:59:59.997\' then
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
as alarmstate
		from alarmconsolidated) as alarmconsolidated on alarmconsolidated.alarmid = alarmmaster.alarmid ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryAlarm[] = 'DepartmentFK is null';
	$queryAlarm .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryAlarm);
	$sep = ' and ';
} else {
	$sep = ' where ';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryAlarm .= ' ' . $sep . ' Priority like \'%' . $value . '%\'';
					break;
				case 2:
					$queryAlarm .= ' ' . $sep . ' alarmtype like \'%' . $value . '%\'';
					break;
				case 3:
					$queryAlarm .= ' ' . $sep . ' groupname like \'%' . $value . '%\'';
					break;
				case 4:
					$queryAlarm .= ' ' . $sep . ' tagname like \'%' . $value . '%\'';
					break;
				case 5:
					$queryAlarm .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
				case 6:
					$queryAlarm .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 7:
					$queryAlarm .= ' ' . $sep . ' alarmstate like \'%' . $value . '%\'';
					break;
			};
			$sep = ' and ';
		};
	};
};
$queryAlarm .= $sep . '((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\'
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
$queryAlarm .= ' order by originationtime desc) as temp
order by originationtime asc) as temp2
order by originationtime desc';
$dataAlarm = odbc_exec($aConn, $queryAlarm);
//$out['headers'] = ['Start Date/Time', 'End Date/Time', 'Campaign', 'Lot', 'Batch', 'Product', 'Recipe', 'Train'];
while(odbc_fetch_row($dataAlarm)) {
	$departmentURL = '';
	if (odbc_result($dataAlarm, 6)) {
		$departmentURL = 'department=' . odbc_result($dataAlarm, 6) . '&';
	};
	$equipmentUrl = '';
	if (odbc_result($dataAlarm, 8)) {
		$equipmentUrl = 'equipment=' . odbc_result($dataAlarm, 8) . '&';
	};
	if (odbc_result($dataAlarm, 10)) {
		$equipment = odbc_result($dataAlarm, 10);
	} else {
		$equipment = '';
	};
	$out['rows'][] = [substr(odbc_result($dataAlarm, 1), 0, -4),
	//substr(odbc_result($dataAlarm, 2), 0, -4),
	odbc_result($dataAlarm, 2),
	trim(odbc_result($dataAlarm, 3)),
	'<a href="alarms.php?' . $equipmentUrl . $departmentURL . 'group[]=' . odbc_result($dataAlarm, 4) . '">' . odbc_result($dataAlarm, 4) . '</a>',
	'<a href="alarmsingle.php?' . $equipmentUrl . $departmentURL . 'id=' . odbc_result($dataAlarm, 7). '">' . odbc_result($dataAlarm, 5) . '</a>',
			odbc_result($dataAlarm, 9),
			$equipment,
			odbc_result($dataAlarm, 12)
	];
	$out['data'][] = [strtotime(odbc_result($dataAlarm, 1)) * 1000, odbc_result($dataAlarm, 11), odbc_result($dataAlarm, 5)  . ' (' . fToTime(odbc_result($dataAlarm, 11)) . ')', 'alarmsingle.php?' . $equipmentUrl . $departmentURL . 'id=' . odbc_result($dataAlarm, 7)];
};
$queryAlarmTot = 'select count(*) 
from alarmmaster
left join [plantavail].[dbo].[alarmgroup] on alarmgroup = groupname
left join [plantavail].[dbo].[department] as department on departmentfk = department.id
left join (select id, name from [plantavail].[dbo].[departmentequipment]) as departmentequipment on departmentequipmentfk = departmentequipment.id
join (select *,  case when alarmconsolidated.AckTime <> \'9999-12-12 23:59:59.997\' then
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
as alarmstate
		from alarmconsolidated) as alarmconsolidated on alarmconsolidated.alarmid = alarmmaster.alarmid ';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryAlarmTot[] = 'DepartmentFK is null';
	$queryAlarmTot .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryAlarmTot);
	$sep = ' and ';
} else {
	$sep = ' where ';
};
if (isset($_GET['filter'])) {
	if (!empty($_GET['filter'])) {
		foreach ($_GET['filter'] as $key => $value) {
			switch ($key) {
				case 1:
					$queryAlarmTot .= ' ' . $sep . ' priority like \'%' . $value . '%\'';
					break;
				case 2:
					$queryAlarmTot .= ' ' . $sep . ' alarmtype like \'%' . $value . '%\'';
					break;
				case 3:
					$queryAlarmTot .= ' ' . $sep . ' groupname like \'%' . $value . '%\'';
					break;
				case 4:
					$queryAlarmTot .= ' ' . $sep . ' tagname like \'%' . $value . '%\'';
					break;
				case 5:
					$queryAlarmTot .= ' ' . $sep . ' department.name like \'%' . $value . '%\'';
					break;
				case 6:
					$queryAlarmTot .= ' ' . $sep . ' departmentequipment.name like \'%' . $value . '%\'';
					break;
				case 7:
					$queryAlarmTot .= ' ' . $sep . ' alarmstate like \'%' . $value . '%\'';
					break;
			};
			$sep = ' and ';
		};
	};
};
$queryAlarmTot .= $sep . '((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\'
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
$dataAlarmTot = odbc_exec($aConn, $queryAlarmTot);
if (odbc_fetch_row($dataAlarmTot)) {
	$out['total_rows'] = odbc_result($dataAlarmTot, 1);
};
print(json_encode($out));
?>