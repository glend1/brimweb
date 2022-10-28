<?PHP 
$title = 'Process Alarms';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
$departmentURL = '';
if (isset($aDE['department'])) {
	$departmentURL = 'department=' . $aDE['department'] . '&';
};
if (isset($_GET['countfirst'])) {
	$countFirst = $_GET['countfirst'];
} else {
	$countFirst = true;
}
fSetDates($startDate, $endDate, 7);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('alarms', '[2013, 06, 12]');
$hookReplace['downloadicon'] = '<a data-text="Download" href="#" class="menucontext"><span class="icon-download-alt icon-hover-hint icon-large"></span></a>';
$hookReplace['downloadmenu'] = '<div id="downloadmenu"></div>';
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryAlarmGroup = 'select distinct GroupName, DepartmentEquipmentFK, [plantavail].[dbo].[DepartmentEquipment].[name]
from alarmmaster
left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup join alarmconsolidated on alarmconsolidated.alarmid = alarmmaster.alarmid
left join [plantavail].[dbo].[DepartmentEquipment] on DepartmentEquipmentFK = [plantavail].[dbo].[DepartmentEquipment].[ID]
where ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
if (!isset($aDE['department'])) {
	$queryAlarmGroup .= ' and [plantavail].[dbo].[alarmgroup].[DepartmentFK] is null';
} else {
	$queryAlarmGroup .= ' and [plantavail].[dbo].[alarmgroup].[DepartmentFK] = ' . $aDE['department'];
};
$queryAlarmGroup .= ' order by groupname asc, name asc';
$dataAlarmGroup = odbc_exec($aConn, $queryAlarmGroup);
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$hookReplace['contextmenu'] = '<div id="subnav"><div>Sort Graph:</div><form action="alarms.php" method="get"><ul><li><input id="ordertrue" type="radio" ';
if ($countFirst) {
	$hookReplace['contextmenu'] .= 'checked';
};
$hookReplace['contextmenu'] .= ' value="1" name="countfirst" /><label for="ordertrue">Count</label></li><li><input id="orderfalse" type="radio" ';
if (!$countFirst) {
	$hookReplace['contextmenu'] .= 'checked';
};
$hookReplace['contextmenu'] .= ' value="0" name="countfirst" /><label for="orderfalse">Duration</label></li></ul><div>Selection:</div><ul>';
$otherAlarm = [];
while(odbc_fetch_row($dataAlarmGroup)) {
	$otherAlarm[odbc_result($dataAlarmGroup, 2)][] = odbc_result($dataAlarmGroup, 1);
	$equipLookup[odbc_result($dataAlarmGroup, 2)] = odbc_result($dataAlarmGroup, 3);
	$lookupEquipReverse[odbc_result($dataAlarmGroup, 1)] = odbc_result($dataAlarmGroup, 2);
};
foreach ($otherAlarm as $key => $array) {
	$hookReplace['contextmenu'] .= '<li class="alarmequiphead">';
	if ($key) {
		$hookReplace['contextmenu'] .= $equipLookup[$key];
	} else {
		$hookReplace['contextmenu'] .= 'None';
	};
	$hookReplace['contextmenu'] .= '<a href="#">Toggle Selection</a><ul>';
	foreach ($array as $value) {
		$hookReplace['contextmenu'] .= '<li><input type="checkbox" ';
		if (isset($_GET['group'])) {
			foreach ($_GET['group'] as $subNavGroupName) {
				if ($value == $subNavGroupName) {
					$hookReplace['contextmenu'] .= 'checked ';
				};
			};
		} else {
			$hookReplace['contextmenu'] .= 'checked ';
		};
		$hookReplace['contextmenu'] .= 'name="group[]" id="' . $value . '" value="' . $value . '"/> <label for="' . $value . '">' . $value . '</label></li>';
	};
	$hookReplace['contextmenu'] .= '</ul></li>';
};
$hookReplace['contextmenu'] .= '</ul><input type="hidden" name="enddate" value="' . $endDate . '" />';
if (isset($aDE['department'])) {
	$hookReplace['contextmenu'] .= '<input type="hidden" name="department" value="' . $aDE['department'] . '" />';
};
$hookReplace['contextmenu'] .= '<input type="hidden" name="startdate" value="' . $startDate . '" /><input type="submit" value="Submit!"/></form></div>';
$queryAlarm = 'select TagName, Comment, Priority, SUM(duration) as duration, COUNT(duration) as count, alarmtype, groupname 
from (select alarmmaster.tagname, comment.comment, alarmconsolidated.alarmtype, alarmconsolidated.priority, alarmmaster.groupname,
case when alarmconsolidated.returntime = \'9999-12-12 23:59:59.997\'
	then 0
	else datediff(ss, alarmtime, returntime) end as duration
from alarmconsolidated 
join alarmmaster on alarmconsolidated.alarmid = alarmmaster.alarmid 
join comment on comment.commentid = alarmconsolidated.commentid
left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup 
where'; 
if (!isset($aDE['department'])) {
	$queryAlarm .= ' departmentfk is NULL';
} else {
	$queryAlarm .= ' departmentfk = ' . $aDE['department'];
};
if (isset($_GET['group'])) {
	$queryAlarm .= ' and (';
	$querySep = '';
	foreach ($_GET['group'] as $getGroup) {
		$queryAlarm .= $querySep . ' GroupName = \'' . $getGroup . '\'';
		$querySep = 'or';
	};
	$queryAlarm .= ')';
};
$queryAlarm .= ' and 
((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))
) as temp
group by TagName, Comment, Priority, GroupName, alarmtype
order by groupname asc, TagName asc';
//where alarmstate = \'ack_rtn\'
$dataAlarm = odbc_exec($aConn, $queryAlarm);
$row = 1;
$bar = array();
$resultSet = '<h3>Records <a data-text="Table Headers" data-id="alarm" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><table data-name="Alarms" class="records" id="alarm-sort"><thead><tr><th>Tagname</th><th>Group Name</th><th>Type</th><th>Priority</th><th>Duration</th><th>Count</th><th>Average</th><th><a href="#" id="toggle-alarm-comments">Description</a></th></tr></thead><tbody>';
while (odbc_fetch_row($dataAlarm)) {
	if ($row % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$equipmentURL = '';
	if ($lookupEquipReverse[odbc_result($dataAlarm, 7)]) {
		$equipmentURL = '&equipment=' . $lookupEquipReverse[odbc_result($dataAlarm, 7)] . '&';
	};
	$alarmLookup[odbc_result($dataAlarm, 1)] = $equipmentURL;
	$resultSet .= '<tr class="' . $rowHeader . '"><td><a href="alarm.php?' . $departmentURL . $equipmentURL . 'startdate=' . $startDate . '&enddate=' . $endDate . '&tagname=' . odbc_result($dataAlarm, 1) . '">' . odbc_result($dataAlarm, 1) . '</a></td><td><a href="alarms.php?startdate=' . $startDate . '&enddate=' . $endDate . '&' . $departmentURL . '&group[]=' . odbc_result($dataAlarm, 7) . '">' . odbc_result($dataAlarm, 7) . '</a></td><td>' . odbc_result($dataAlarm, 6) . '</td><td>' . odbc_result($dataAlarm, 3) . '</td><td data-duration="';
	if (odbc_result($dataAlarm, 4) == "") {
		$resultSet .= 0;
	} else {
		$resultSet .= odbc_result($dataAlarm, 4);
	};
	$resultSet .= '">' . fToTime(odbc_result($dataAlarm, 4)) . '</td><td>' . odbc_result($dataAlarm, 5) . '</td><td data-duration="' . (odbc_result($dataAlarm, 4) / odbc_result($dataAlarm, 5)) . '">' . fToTime(odbc_result($dataAlarm, 4) / odbc_result($dataAlarm, 5)) . '</td><td><a href="#" class="toggle-table-sorter">Show</a></td></tr><tr class="hiddenrow ' . $rowHeader . '"><td colspan ="8">' . odbc_result($dataAlarm, 2) . '</td></tr>';
	$row++;
	if (isset($bar['Count'][odbc_result($dataAlarm, 1)])) {
		$bar['Count'][odbc_result($dataAlarm, 1)] += odbc_result($dataAlarm, 5);
	} else {
		$bar['Count'][odbc_result($dataAlarm, 1)] = odbc_result($dataAlarm, 5);
	};
	if (isset($bar['Duration'][odbc_result($dataAlarm, 1)])) {
		$bar['Duration'][odbc_result($dataAlarm, 1)] += odbc_result($dataAlarm, 4);
	} else {
		$bar['Duration'][odbc_result($dataAlarm, 1)] = odbc_result($dataAlarm, 4);
	};
	//$bar['Duration'][odbc_result($dataAlarm, 1)] = odbc_result($dataAlarm, 4);
	//$bar['Average'][odbc_result($dataAlarm, 1)] = odbc_result($dataAlarm, 4) / odbc_result($dataAlarm, 5);
};
$resultSet .= '</tbody>' . fTableFooter(['id' => 'alarm-sort', 'cols' => 8, 'totals' => [[0 => 'count-child', 4 => 'time-mean', 5 => 'mean'], [4 => 'time-sum', 5 => 'sum']]]) . '</table>';
if ($row > 1) { 
	$sAlarms = $resultSet;
};
if (!isset($sAlarms)) {
	$stdOut .= '<h2>No data available</h2>';
} else {
	if (!isset($aDE['department'])) {
		$stdOut .= '<h2>Showing Everything else between ' . $startDate . ' and ' . $endDate . '</h2>';
	} else {
		$queryGetDepartment = 'select name from department where id = ' . $aDE['department'];
		$dataGetDepartment = odbc_exec($conn, $queryGetDepartment);
		while (odbc_fetch_row($dataGetDepartment)) {
			$stdOut .= '<h2>Showing ' . odbc_result($dataGetDepartment, 1) . ' department between ' . $startDate . ' and ' . $endDate . '</h2>';
		};
	};
};
if (isset($alarmList)) {
	$stdOut .= $alarmList;
};
if (!empty($bar)) {
	if ($countFirst) {
		arsort($bar['Count']);
		$settings = [['name' => 'Duration', 'order' => 2, 'axis' => 2, 'label' => 'time'], ['name' => 'Count', 'order' => 1, 'axis' => 1, 'label' => 'int']];
	} else {
		arsort($bar['Duration']);
		$settings = [['name' => 'Count', 'order' => 2, 'axis' => 1, 'label' => 'int'], ['name' => 'Duration', 'order' => 1, 'axis' => 2, 'label' => 'time']];
	};
	$i = 0;
	$ticks = '';
	$top = 10;
	$barKey = 1;
	$barSep = '';
	foreach ($settings as $key => $val) {
		$data[$key] = '{
		label: \'' . $val['name'] . '\',
		xaxis: ' . $val['axis'] . ',
		bars: {
				order: ' . $val['order'] . '
			},
		data:['; 
	}
	foreach ($bar[$settings[count($settings) - 1]['name']] as $tagname => $value) {
		foreach ($settings as $setting => $settingData) {
			$data[$setting] .= $barSep . '[' . $bar[$settingData['name']][$tagname] . ', ' . $barKey . ', 0, 0, \'';
			switch ($settingData['label']) {
				case 'time':
					$data[$setting] .= fToTime($bar[$settingData['name']][$tagname]);
					break;
				case 'int':
				default:
					$data[$setting] .= $bar[$settingData['name']][$tagname];
					break;
			};
			$data[$setting] .= '\', \'alarm.php?' . $alarmLookup[$tagname] . $departmentURL . '&startdate=' . $startDate . '&enddate=' . $endDate . '&tagname=' . $tagname . '\']
				';
		};
		$ticks .= $barSep . '[' . $barKey . ', \'' . $tagname . '\']';
		$barSep = ', ';
		$barKey++;
		if ($i >= $top) {
			break;
		};
		$i++;
	}
	foreach ($settings as $key => $val) {
		$data[$key] .= ']}';
	}
	$stdOut .= '<script type="text/javascript">

		var data = [' . implode($data, ', ') . ']
			$(function() {
				
				$(".alarmequiphead a").bind("click", function(event) {
					checkboxes = $(this).parent().find("input");
					checkboxes.prop("checked", !checkboxes.prop("checked"));
					return false;
				});
			
				fTableSorter({sorttable: "#alarm-sort", 
					sortorder: [[1,0]],
					rowheaders: "alarm",
					headers: {2 : { columnSelector: false },
						4 : { sorter: "duration" },
						6 : { sorter: "duration", columnSelector: false },
						7 : { sorter: false }}
				});
				
				var bar = $.plot("#bar", data, {
					colors: trendcolors,
					series: {
						bars: {
							show: true, 
							barWidth: ' . ((1 / 2) - ((1 / 2) / 2)) . ', 
							horizontal:true
						}
					},
					xaxes: [
						{ position: "bottom", axisLabel: "Count"},
						{ position: "top", axisLabel: "Duration", tickFormatter: function format(val, axis) { return msToTime(val * 1000); }, alignTicksWithAxis: 1},
					],
					yaxis: {
						show: true,
						ticks: [' . $ticks . '],
						axisLabel: "Tagnames (Top ' . $top . ' Alarms)"
					},
					grid: {
						hoverable: true,
						clickable: true
					}
				});
				
				$("#bar").bind("plotclick", function(event,pos,item) {
					if (item) {
						if (item.series.data[item.dataIndex][5]) {
							window.location.href = item.series.data[item.dataIndex][5];
						};
					};
				});
				$("#bar").bind("plothover", function(event, pos, item) {
					if (item) {
						if ($(this).data("previous-post") != item.seriesIndex) {
							$(this).data("previous-post", item.seriesIndex);
						}
						$("#tooltip").remove();
						showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][4]);
					} else {
						$("#tooltip").remove();
						previousPost = $(this).data("previous-post", -1);
					}
				});				
				
			});
			
			</script>
					<div id="bar"></div><div class="tablesorter-row-hider" id="alarm-table-rows"></div>' . $sAlarms;
					};
odbc_close($aConn);
$hookReplace['help'] = $helptext['standardcal'] . '<a href="#">Context Sensitive "Briefcase" Menu</a><div>Click the context sensitive menu to view Alarm groups inside the department. Selecting alarm groups and pressing the submit button will filter the dataset accordingly.</div>' . $helptext['barhover'] . '<a href="#">Bar Graph Clicking</a><div>Clicking a Bar in the Bar Graph will take you to the selected departments alarm page.</div>' . $helptext['default7'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] ;
require_once 'includes/footer.php'; ?>