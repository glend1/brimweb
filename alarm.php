<?PHP 
$title = 'Process Alarm';
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
$equipmentUrl = '';
if (isset($aDE['equipment'])) {
	$equipmentUrl = '&equipment=' . $aDE['equipment'] . '&';
};
if (!isset($_GET['tagname'])) {
	$_SESSION['sqlMessage'] = 'Please select an Alarm!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 7);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('alarm', '[2013, 06, 12]');
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
$queryComment = 'select top 1 Comment
from AlarmMaster
join AlarmConsolidated on AlarmConsolidated.AlarmId = AlarmMaster.AlarmId
join Comment on AlarmConsolidated.CommentId = Comment.Commentid
where TagName = \'' . $_GET['tagname'] . '\'
order by OriginationTime desc';
$dataComment = odbc_exec($aConn, $queryComment);
while(odbc_fetch_row($dataComment)) {
	$comment = odbc_result($dataComment, 1);
};
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
where alarmmaster.TagName = \'' . $_GET['tagname'] . '\' and 
((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . $startDate . '\' 
and DATEADD(mi, AlarmTimeZoneOffset - (AlarmDaylightAdjustment * 7.5), Alarmtime) < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and (DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . $startDate . '\'
and DATEADD(mi, ReturnTimeZoneOffset - (ReturnDaylightAdjustment * 7.5), Returntime) > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))
order by alarmtime asc';
$dataAlarm = odbc_exec($aConn, $queryAlarm);
$sAlarm = '<h3>Description</h3>' . $comment .  '<div class="tablesorter-row-hider" id="record-table-rows"></div>' . fRecordSwap(['exclude' => ['alarm'], 'table' => true]) . '<table class="records" id="alarm-sort"><thead><tr><th>State</th><th>Type</th><th>Priority</th><th>Start Date/Time</th><th>Acknowledge Date/Time</th><th>Acknowledge Duration</th><th>Return Date/Time</th><th>Return Duration</th></tr></thead><tbody>';
$row = 1;
while(odbc_fetch_row($dataAlarm)) {
	if ($row % 2 == 0) {
		$sAlarm .= '<tr class="oddRow">';
	} else {
		$sAlarm .= '<tr class="evenRow">';
	};
	$row++;
	$dataDuration['Acknowledge'][] = '[' . strtotime(odbc_result($dataAlarm, 4)) * 1000 . ',' . odbc_result($dataAlarm, 6) . ',\'' . fToTime(odbc_result($dataAlarm, 6)) . '\', \'alarmsingle.php?' . $departmentURL . $equipmentUrl . 'id=' . odbc_result($dataAlarm, 10) . '\']';
	$dataDuration['Return'][] = '[' . strtotime(odbc_result($dataAlarm, 4)) * 1000 . ',' . odbc_result($dataAlarm, 8) . ',\'' . fToTime(odbc_result($dataAlarm, 8)) . '\', \'alarmsingle.php?' . $departmentURL . $equipmentUrl . 'id=' . odbc_result($dataAlarm, 10) . '\']';
	$sAlarm .= '<td><a href="alarmsingle.php?' . $departmentURL . $equipmentUrl . 'id=' . odbc_result($dataAlarm, 10) . '">' . odbc_result($dataAlarm, 1) . '</a></td>';
	$sAlarm .= '<td>' . odbc_result($dataAlarm, 2) . '</td>';
	$sAlarm .= '<td>' . odbc_result($dataAlarm, 3) . '</td>';
	$sAlarm .= '<td>' . substr(odbc_result($dataAlarm, 4), 0, -4) . '</td>';
	if (odbc_result($dataAlarm, 5) == '9999-12-13 00:59:59.997') {
		$sAlarm .= '<td></td>';
	} else {
		$sAlarm .= '<td>' . substr(odbc_result($dataAlarm, 5), 0, -4) . '</td>';
	};
	$sAlarm .= '<td data-duration="';
	if (odbc_result($dataAlarm, 6) == "") {
		$sAlarm .= 0;
	} else {
		$sAlarm .= odbc_result($dataAlarm, 6);
	};
	$sAlarm .= '">' . fToTime(odbc_result($dataAlarm, 6)) . '</td>';
	if (odbc_result($dataAlarm, 7) == '9999-12-13 00:59:59.997') {
		$sAlarm .= '<td></td>';
	} else {
		$sAlarm .= '<td>' . substr(odbc_result($dataAlarm, 7), 0, -4) . '</td>';
	};
	$sAlarm .= '<td data-duration="';
	if (odbc_result($dataAlarm, 8) == "") {
		$sAlarm .= 0;
	} else {
		$sAlarm .= odbc_result($dataAlarm, 8);
	};
	$sAlarm .= '">' . fToTime(odbc_result($dataAlarm, 8)) . '</td>';
	//$sAlarm .= '<td>' . odbc_result($dataAlarm, 9) . '</td>';
	$sAlarm .= '</tr>';
}
$sAlarm .= '</tbody>' . fTableFooter(['id' => 'alarm-sort', 'cols' => 8, 'totals' => [[3 => "count", 5 => 'time-mean', 7 => 'time-mean'], [5 => 'time-sum', 7 => 'time-sum']]]) . '</table>';
$sDataDuration = '';
	$sep = '';
	foreach ($dataDuration as $key => $array) {
		$sDataDuration .= $sep . '{label:"' . $key . '", data:[' . implode(',', $array) . ']}';
		$sep = ', ';
	};
$stdOut .= '<h2>Showing ' . $_GET['tagname'] . ' between ' . $startDate . ' and ' . $endDate . '</h2>
<script type="text/javascript">

	$(function() {
	
		fTableSorter({sorttable: "#alarm-sort", 
			sortorder: [[3,0]],
			rowheaders: "record",
			headers: {1 : { columnSelector: false },
					5 : { sorter: "duration" },
					7 : { sorter: "duration" }}
		});

		var dataDuration = [' . $sDataDuration . ']; 

		var options = {
			colors: trendcolors,
			series: {
				points: {
					show: true
				},
				lines: {
					show: true
				}
			},
			xaxis: {
				mode: "time",
				timeformat: "%d/%m/%y<br />%H:%M:%S",
				ticks: 5,
				tickLength: 0,
				axisLabel: "Date/Time"
			},
			yaxis: {
				axisLabel: "Duration",
				ticks: durTicks
			},
			selection: {
				color: trendselection,
				mode: "x"
			},
			grid: {
				hoverable: true,
				clickable: true,
				markings: fMarkings
			},
			legend: {
				labelFormatter: function(label, series) { 
							return label + " (" + series.data.length + ")";
						}
			}
		};

		recordSwapTarget = $.plot("#line", dataDuration, options);

		var overview = $.plot("#overview2", dataDuration, {
			colors: trendcolors,
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				shadowSize: 0
			},
			xaxis: {
				min: ' . (strtotime($startDate) * 1000) . ',
				max: ' . (strtotime($endDate) * 1000) . ',
				mode: "time",
				timeformat: "%d/%m/%y<br />%H:%M:%S",
				ticks: 5,
				tickLength: 0
			},
			yaxis: {
				show:false
			},
			selection: {
				color: trendselection,
				mode: "x"
			},
			legend: {
				show: false
			},
			grid: {
				markings: fMarkings
			}
		});
		var iDataSmall = recordSwapTarget.getAxes().xaxis.min;
		var iDataBig = recordSwapTarget.getAxes().xaxis.max;
	
		$("#line").bind("plotclick", function(event,pos,item) {
			if (item) {
				if (item.series.data[item.dataIndex][5]) {
					window.location.href = item.series.data[item.dataIndex][5];
				};
			};
		});

		// now connect the two

		$("#line").bind("plotselected", function (event, ranges) {

			// do the zooming

			recordSwapTarget = $.plot("#line", dataDuration, $.extend(true, {}, options, {
				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to
				}
			}));

			// don"t fire event on the overview to prevent eternal loop

			overview.setSelection(ranges, true);
		});

		$("#overview2").bind("plotselected", function (event, ranges) {
			recordSwapTarget.setSelection(ranges);
		});
		
		$("#line").bind("plothover", function(event, pos, item) {
				if (item) {
					if ($(this).data("previous-post") != item.seriesIndex) {
						$(this).data("previous-post", item.seriesIndex);
					}
					$("#tooltip").remove();
					showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][2]);
				} else {
					$("#tooltip").remove();
					previousPost = $(this).data("previous-post", -1);
				}
			});
		$("#line").bind("plotclick", function(event,pos,item) {
			if (item) {
				if (item.series.data[item.dataIndex][3]) {
					window.location.href = item.series.data[item.dataIndex][3];
				};
			};
		});
			
		$("#reset").click(function () {
			recordSwapTarget = $.plot("#line", dataDuration, $.extend(true, {}, options, {
				xaxis: {
					min: iDataSmall,
					max: iDataBig
				}
			}));
			overview.clearSelection();
			return false;
		});
		
	});
	</script>
			<div id="line" ></div>
			<div id="resetcontainer"><a href="#" id="reset">Reset&nbsp;Zoom</a></div>
			<h3>Overview</h3><div id="overview2"></div>' . $sAlarm;
odbc_close($aConn);
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehover'] . $helptext['linedrag'] . $helptext['linemarkings'] . $helptext['recordswap'] . $helptext['dynrecordswap'] . $helptext['autohighlight'] . $helptext['default7'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] ;
require_once 'includes/footer.php'; ?>