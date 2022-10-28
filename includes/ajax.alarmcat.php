<?PHP 
require_once 'functions.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 7);
if (isset($_GET)) {
	if (isset($_GET['filter'])) {
		if (count($_GET['filter']) == 7) {
			foreach ($_GET['filter'] as $key => $value) {
				if ($value != '') {
					switch ($key) {
						case 1:
							$where[$key] = 'priority like ';
							break;
						case 2:
							$where[$key] = 'alarmtype like ';
							break;
						case 3:
							$where[$key] = 'groupname like ';
							break;
						case 4:
							$where[$key] = 'tagname like ';
							break;
						case 5:
							$where[$key] = 'department.name like ';
							break;
						case 6:
							$where[$key] = 'departmentequipment.name like ';
							break;
						case 7:
							$where[$key] = 'alarmstate like ';
							break;
						default:
							//error
							break;
					};
					$where[$key] .= '\'%' . $value . '%\'';
				};
			};
		} else {
			//error
		};
	} else {
		//error
	};
	if (!isset($_GET['column'])) {
		//error
	} else {
		switch ($_GET['column']) {
			case 1:
				$column = 'priority';
				break;
			case 2:
				$column = 'alarmtype';
				break;
			case 3:
				$column = 'groupname';
				break;
			case 4:
				$column = 'tagname';
				break;
			case 5:
				$column = 'department.name';
				break;
			case 6:
				$column = 'departmentequipment.name';
				break;
			case 7:
				$column = 'alarmstate';
				break;
			default:
				//error
				break;
		};
	};
} else {
	//error
};
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$query = 'select distinct ' . $column . '
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
	$aQuery[] = 'DepartmentFK is null';
	$query .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQuery);
	$sep = ' and ';
} else {
	$sep = 'where ';
};
if (isset($where)) {
	foreach ($where as $value) {
		$query .= $sep . $value; 
		$sep = ' and ';
	};
};
$query .= $sep . '((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
$query .= 'order by ' . $column . ' asc';
$data = odbc_exec($aConn, $query);
$out = '';
$sep = '';
while(odbc_fetch_row($data)) {
	$out .= $sep . '"' . trim(odbc_result($data, 1)) . '"';
	$sep = ', ';
};
print('[' . $out . ']');
?>