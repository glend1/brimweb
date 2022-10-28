<?PHP 
$title = 'Department Process Alarms';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 7);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('alarmsplash', '[2013, 06, 12]');
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryAlarmGroup = 'select distinct departmentfk, GroupName, count(groupname)
from alarmmaster
left join [plantavail].[dbo].[alarmgroup] on GroupName = alarmgroup
join alarmconsolidated on alarmconsolidated.alarmid = alarmmaster.alarmid
where ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryAlarms[] = 'DepartmentFK is null';
	$queryAlarmGroup .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryAlarms);
};
$queryAlarmGroup .= ' group by groupname, departmentfk';
$dataAlarmGroup = odbc_exec($aConn, $queryAlarmGroup);
while(odbc_fetch_row($dataAlarmGroup)) {
	if (!odbc_result($dataAlarmGroup, 1)) {
		$aUnused[odbc_result($dataAlarmGroup, 2)] = odbc_result($dataAlarmGroup, 2);
	};
	//$aAlarmDepartments[odbc_result($dataAlarmGroup, 1)]['alarmgroups'][odbc_result($dataAlarmGroup, 2)] = odbc_result($dataAlarmGroup, 2);
	if (!isset($aAlarmDepartments[odbc_result($dataAlarmGroup, 1)])) {
		$aAlarmDepartments[odbc_result($dataAlarmGroup, 1)] = odbc_result($dataAlarmGroup, 3);
	} else {
		$aAlarmDepartments[odbc_result($dataAlarmGroup, 1)] += odbc_result($dataAlarmGroup, 3);
	};
};
if (isset($aAlarmDepartments)) {
	asort($aAlarmDepartments);
	$stdOut .= '<h2>Showing Alarms by Department between ' . $startDate . ' and ' . $endDate . '</h2>';
	$queryDepartments = 'select id, name from department';
	$dataDepartments = odbc_exec($conn, $queryDepartments);
	while(odbc_fetch_row($dataDepartments)) {
		$aDepartments[odbc_result($dataDepartments, 1)] = odbc_result($dataDepartments, 2);
	};
	$data = '';
	$sepB = '';
	$aTicks = array();
	$i = 1;
	foreach($aAlarmDepartments as $key => $array) {
		if ($key != NULL) {
			$data .= $sepB . '{ label: "' . $aDepartments[$key] . '", data: [ [ ' . $array . ', ' . $i . ', "' . $aDepartments[$key] . '", "alarms.php' . fQueryString(['include' => ['department' => $key]]) . '" ] ] }';
		} else {
			$data .= $sepB . '{ label: "Undefined", data: [ [ ' . $array . ', 0, "Undefined", "alarms.php' . fQueryString(['include' => ['department' => NULL]]) . '" ] ] }';
			$aTicks[0] = '[0, "Undefined"]';
			$unAssignedAlarms = implode(', ', $aUnused);
		};
		if (isset($aDepartments[$key])) {
			$aTicks[$key] = '[' . $i . ', "' . $aDepartments[$key] . '"]';
			$i++;
		};
		$sepB = ', ';
	};
	if (isset($unAssignedAlarms)) {
		$extraNotifications .= '<div class="ui-notif-error"><span class="icon-remove-sign"></span><b>Undefined Alarm Groups Please ';
		if (fCanSee(@$_SESSION['permissions']['page'][12] >= 200 && isset($_SESSION['edit']['departmentedit']))) {
			$extraNotifications .= '<a href="alarmadmin.php">Fix</a>';
		} else {
			$extraNotifications .= 'Report';
		};
		$extraNotifications .= ':</b> ' . $unAssignedAlarms . '</div>';
	};
	$stdOut .= '
		<script type="text/javascript">

			$(function() {

				var data = [ ' . $data . ' ] //[ ["January", 10], ["February", 8], ["March", 4], ["April", 13], ["May", 17], ["June", 9] ];
				/*if (data.length > 20) {
					data = $.plot.JUMlib.prepareData.pareto(data, "#", true, 19);
				} else {
					data = $.plot.JUMlib.prepareData.pareto(data, false);
				};*/
				var bar = $.plot("#bar", data, {
					colors: trendcolors,
					series: {
						bars: {
							show: true,
							barWidth: 0.9,
							align: "center",
							horizontal:true
						}
					},
					yaxis: {
						tickSize: 1,
						tickLength: 0,
						show: true,
						axisLabel: "Department",
						ticks: [ ' . implode(', ', $aTicks) . ' ]
					},
					xaxis: {
						axisLabel: "Count"
					},
					/*yaxis: {
						tickSize: 100
					},*/
					grid: {
						hoverable: true,
						clickable: true
					},
					legend: {
						noColumns: 5,
						container:$("#legend"),
						labelFormatter: function(label, series) { 
							//series.data 
							return \'<a href="\' + series.data[0][3] + \'">\' + label + \'</a> (\' + series.data[0][0] + \')\';
						}
					}
				});
				var i = 0;
			$.each(data, function(key, val) {
				val.color = i;
				++i;
			});
				// insert checkboxes 
			var choiceContainer = $("#legend");
			var legendChoiceContainer = choiceContainer.find(".legendColorBox");
			$.each(data, function(key, val) {
				iText = "<td><input type=\'checkbox\' name=\'" + key + "\' checked=\'checked\' id=\'id" + key + "\'></input></td>";
				legendChoiceContainer.eq(key).before(iText);
			});
				
				$("#bar").bind("plotclick", function(event,pos,item) {
					//console.log(data[item.seriesIndex].data[item.dataIndex][3]);
				if (item) {
					if (item.series.data[item.dataIndex][3]) {
						window.location.href = item.series.data[item.dataIndex][3];
					};
				};
			});
				$("#bar").bind("plothover", function(event, pos, item) {
					if (item) {
						if ($(this).data("previous-post") != item.seriesIndex) {
							$(this).data("previous-post", item.seriesIndex);
						}
						$("#tooltip").remove();
						showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][2] + " (" + item.datapoint[0] + ")");
					} else {
						$("#tooltip").remove();
						previousPost = $(this).data("previous-post", -1);
					}
				});
				
				choiceContainer.find("input").click(plotAccordingToChoices);
			function plotAccordingToChoices() {
				var newData = [];
				var sum = 0;
				choiceContainer.find("input:checked").each(function () {
					var key = $(this).attr("name");
					if (key && data[key]) {
						newData.push(data[key]);
						for (i = 0; i < data[key].data.length; i++) {
							sum += data[key].data[i][0];
						};
					}
				});
				if (newData.length > 0) {
					bar.getOptions().legend.container = null;
					bar.getOptions().legend.show = false;
					bar.setData(newData);
					bar.draw();
				};
				$("#dynamic-totals").html("Sum:" + sum + " Total:" + newData.length + " Mean:" + Math.round(sum / newData.length));  
			};
		plotAccordingToChoices();	
				
				
			});
			</script>
					<div id="bar"></div>
					<h3>Departments<span id="dynamic-totals"></span></h3>
					<div id="legend"></div>';
} else {
	$stdOut .= '<h2>No data available</h2>';
};
odbc_close($aConn);
$hookReplace['help'] = $helptext['standardcal'] . $helptext['barhover'] . '<a href="#">Bar Graph Clicking</a><div>Clicking a Bar in the Bar Graph will take you to the selected departments alarm page.</div>' . $helptext['graphtoggle'] . $helptext['default7'];
require_once 'includes/footer.php'; ?>