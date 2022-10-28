<?PHP 
$title = 'Phase Duration Comparison';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
$departmentURL = '';
if (isset($aDE['department'])) {
	$departmentURL = 'department=' . $aDE['department'] . '&';
};
if (!isset($_GET['recipe'])) {
	$_SESSION['sqlMessage'] = 'Please select a Recipe!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['phase'])) {
	$_SESSION['sqlMessage'] = 'Please select a Phase!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batchphasecompare', '[2004, 05, 18]');
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryBatches = 'select Campaign_ID, Lot_ID, Batch_ID, batch_log_id, dbname, datediff(ss, sstart, send) as dur, product_id, recipe_id, sstart, send, int_recipe, Description, train_id, phase_label, Phase_Instance_ID
from (select sstart, send, train_id, phasedur.batch_log_id, Campaign_ID, Lot_ID, Batch_ID, \'oldbatchhistory\' as dbname, product_id, recipe_id, cast(recipe_version as int) int_recipe, CodeTable.Description, phase_label, phasedur.Phase_Instance_ID
from (SELECT Phase_Instance_ID, min(datetime) as sstart, max(datetime) as send, batch_log_id, phase_id, phase_label
FROM [oldBatchHistory].[dbo].[batchdetail]
group by Phase_Instance_ID, batch_log_id, phase_id, phase_label) as phasedur
left join [oldBatchHistory].[dbo].[batchidlog] on [oldBatchHistory].[dbo].[batchidlog].[batch_log_id] = phasedur.batch_log_id
	left join (select Phase_Instance_ID, action_cd, Description
		from (select ROW_NUMBER() over(partition by phase_instance_id order by datetime desc, action_cd desc) as phase_step, Phase_Instance_ID, action_cd
		from BatchDetail
		where Phase_Instance_ID <> \'\' and (Action_CD between 221 and 234 or action_cd between 241 and 246 or Action_CD between 275 and 279)) as temp2
		join CodeTable on Action_CD = Code
		where phase_step = 1
	) as statustable on phasedur.phase_instance_id = statustable.phase_instance_id
	left join CodeTable on Action_CD = Code
where phase_id = \'' . $_GET['phase'] . '\' and recipe_id = \'' . $_GET['recipe'] . '\'
union
select sstart, send, train_id, phasedur.batch_log_id, Campaign_ID, Lot_ID, Batch_ID, \'batchhistory\' as dbname, product_id, recipe_id, cast(recipe_version as int) int_recipe, CodeTable.Description, phase_label, phasedur.Phase_Instance_ID
from (SELECT Phase_Instance_ID, min(datetime) as sstart, max(datetime) as send, batch_log_id, phase_id, phase_label
FROM [BatchHistory].[dbo].[batchdetail]
group by Phase_Instance_ID, batch_log_id, phase_id, phase_label) as phasedur
left join [BatchHistory].[dbo].[batchidlog] on [BatchHistory].[dbo].[batchidlog].[batch_log_id] = phasedur.batch_log_id
	left join (select Phase_Instance_ID, action_cd, Description
		from (select ROW_NUMBER() over(partition by phase_instance_id order by datetime desc, action_cd desc) as phase_step, Phase_Instance_ID, action_cd
		from BatchDetail
		where Phase_Instance_ID <> \'\' and (Action_CD between 221 and 234 or action_cd between 241 and 246 or Action_CD between 275 and 279)) as temp2
		join CodeTable on Action_CD = Code
		where phase_step = 1
	) as statustable on phasedur.phase_instance_id = statustable.phase_instance_id
	left join CodeTable on Action_CD = Code
where phase_id = \'' . $_GET['phase'] . '\' and recipe_id = \'' . $_GET['recipe'] . '\') as temp1
left join [plantavail].[dbo].[train] on train_id = train 
where ';
if (isset($aDE['department'])) {
	$queryBatches .= 'departmentfk = ' . $aDE['department'];
} else {
	$queryBatches .= 'departmentfk is NULL';
};
$queryBatches .= ' and 
((sstart between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or send between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((sstart < \'' . $startDate . '\' and sstart < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((send > \'' . $startDate . '\' and send > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'))))';
/*if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatch[] = 'DepartmentFK is null';
	$queryBatches .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryBatch);
};*/
$queryBatches .= ' order by sstart';
$dataBatches = odbc_exec($bConn, $queryBatches);
$batch = '<table class="records" id="batch-sort"><thead><tr><th>Label</th><th>Batch ID</th><th>Duration</th><th>Start Time</th><th>End Time</th><th>Product</th><th>Version</th><th>Train</th><th>Status</th></tr></thead><tbody>';
$i = 1;
$dataDuration = array();
$dataStatus = array();
while(odbc_fetch_row($dataBatches)) {
	$phasename = $_GET['phase'];
	if (substr(odbc_result($dataBatches, 12), 0, 8) == "Received") {
		$batchStatus = trim(substr(odbc_result($dataBatches, 12), 8));
	} elseif (substr(odbc_result($dataBatches, 12), 0, 10) == "Transition") {
		$batchStatus = trim(substr(odbc_result($dataBatches, 12), 10));
	} else {
		$batchStatus = trim(odbc_result($dataBatches, 12));
	};
	$dataDuration[odbc_result($dataBatches, 14)][] = '[' . strtotime(odbc_result($dataBatches, 9)) * 1000 . ',' . odbc_result($dataBatches, 6) . ',\'' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . '/' . odbc_result($dataBatches, 14) . ' (' . fToTime(odbc_result($dataBatches, 6)) . ')\', \'phase.php?' . $departmentURL . 'phase=' . urlencode(odbc_result($dataBatches, 15)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '\']';
	if (!isset($dataStatus[$batchStatus])) {
		$dataStatus[$batchStatus] = 1;
	} else {
		$dataStatus[$batchStatus]++;
	};
	if ($i % 2 == 0) {
		$batch .= '<tr class="oddRow">';
	} else {
		$batch .= '<tr class="evenRow">';
	};
	$i++;
	$batch .= '<td><a href="phase.php?' . $departmentURL . 'phase=' . urlencode(odbc_result($dataBatches, 15)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '">' . odbc_result($dataBatches, 14) . '</a></td><td><a href="batchsummary.php?' . $departmentURL . 'batch=' . urlencode(odbc_result($dataBatches, 4)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '" >' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . '</a></td><td data-duration="';
	if (odbc_result($dataBatches, 6) == "") {
		$batch .= 0;
	} else {
		$batch .= odbc_result($dataBatches, 6);
	};
	$batch .= '">' . fToTime(odbc_result($dataBatches, 6)) . '</td><td>' . substr(odbc_result($dataBatches, 9), 0, -4) . '</td><td>' . substr(odbc_result($dataBatches, 10), 0, -4) . '</td><td>' . odbc_result($dataBatches, 7) . '</td><td>' . odbc_result($dataBatches, 11) . '</td><td>' . odbc_result($dataBatches, 13) . '</td><td>' . $batchStatus . '</td></tr>';
};
$batch .= '</tbody>' . fTableFooter(['id' => 'batch-sort', 'cols' => 9, 'totals' => [[0 => "count", 2 => 'time-mean'], [2 => 'time-sum']]]) . '</table>';
if ($i > 1) {
	$stdOut .= '<h2>Showing ' . $phasename . ' Phases between ' . $startDate . ' and ' . $endDate . '</h2>';
	$pieData = Array();
	foreach ($dataStatus as $key => $value) {
		$pieData[] = '{ label: "' . $key . '" , data: ' . $value . '}';
	};
	$sDataDuration = '';
	$sep = '';
	foreach ($dataDuration as $key => $array) {
		$sDataDuration .= $sep . '{label:"' . $key . '", data:[' . implode(',', $array) . ']}';
		$sep = ', ';
	};
$stdOut .= '<script type="text/javascript">

	var searchArray = new Array();

	$(function() {
	
		fTableSorter({sorttable: "#batch-sort", 
			sortorder: [[3,0]],
			rowheaders: "phase",
			headers: {5 : { columnSelector: false },
					2 : { sorter: "duration" }}
		});
		
		function plotAccordingToChoice(container, data, plot) {
			var checkbox = new Array();
			container.find("input").each(function () {
					if (this.checked) {
						checkbox.push("^" + data[this.name].label + "$");
					};
					data[this.name].lines = { show: this.checked };
					data[this.name].points = { show: this.checked };
			});
			var sCheckbox = checkbox.join("|");
			searchArray[0] = "/" + sCheckbox + "/i";
			$("#batch-sort").trigger(\'search\', [searchArray]);
				plot.getOptions().legend.container = null;
				plot.getOptions().legend.show = false;
				plot.setData(data);
				plot.draw();
		};
		
		function plotAccordingToChoicePie(container, data, plot) {
			var checkbox = new Array();
			var newData = [];
			container.find("input:checked").each(function () {
				checkbox.push("^" + data[this.name].label + "$");
				var key = $(this).attr("name");
				if (key && data[key]) {
					newData.push(data[key]);
				}
			});
			var sCheckbox = checkbox.join("|");
			searchArray[8] = "/" + sCheckbox + "/i";
			$("#batch-sort").trigger(\'search\', [searchArray]);
				plot.getOptions().legend.container = null;
				plot.getOptions().legend.show = false;
				plot.setData(newData);
				plot.draw();
		};

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
				axisLabel: "Date/Time",
				tickLength: 0
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
				container: $("#mainlegend"),
				labelFormatter: function(label, series) { 
							return label + " (" + series.data.length + ")";
						}
			}
		};

		var plot = $.plot("#line", dataDuration, options);
		options.legend.container = null;
				options.legend.show = false;
		
		var i = 0;
			$.each(dataDuration, function(key, val) {
				val.color = i;
				++i;
			});
			
			// insert checkboxes 
			var choiceContainer = $("#mainlegend");
			var legendChoiceContainer = choiceContainer.find(".legendColorBox");
			$.each(dataDuration, function(key, val) {
				iText = "<td><input type=\'checkbox\' name=\'" + key + "\' checked=\'checked\' id=\'id" + key + "\'></input></td>";
				legendChoiceContainer.eq(key).before(iText);
			});
			
			choiceContainer.find("input").click(function() { plotAccordingToChoice(choiceContainer, dataDuration, plot); });

		var overview = $.plot("#overview", dataDuration, {
			colors: trendcolors,
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				shadowSize: 0
			},
			xaxis: {
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
		
			var iDataSmall = plot.getAxes().xaxis.min;
		var iDataBig = plot.getAxes().xaxis.max;
	

		// now connect the two

		$("#line").bind("plotselected", function (event, ranges) {

			// do the zooming

			plot = $.plot("#line", dataDuration, $.extend(true, {}, options, {
				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to
				}
			}));

			// don"t fire event on the overview to prevent eternal loop

			overview.setSelection(ranges, true);
		});

		$("#overview").bind("plotselected", function (event, ranges) {
			plot.setSelection(ranges);
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
		
		var dataStatus = [' . implode(',', $pieData) . '];
		
		var minipie = $.plot("#minipie", dataStatus, {
			colors: trendcolors,
			series: {
				pie: {
					show: true,
					radius: 1,
					stroke: {
						width: 0
					},
					label: {
						show: false
					}
				}
			},
			grid: {
				hoverable: true
			},
			legend: {
				container:$("#minipielegend"),
				labelFormatter: legendFormatter
			}
		});
		
		
		var i = 0;
		$.each(dataStatus, function(key, val) {
			val.color = i;
			++i;
		});
		
		// insert checkboxes 
		var choiceContainerPie = $("#minipielegend");
		var legendChoiceContainerPie = choiceContainerPie.find(".legendColorBox");
		$.each(dataStatus, function(key, val) {
			iText = "<td><input type=\'checkbox\' name=\'" + key + "\' checked=\'checked\' id=\'id" + key + "\'></input></td>";
			legendChoiceContainerPie.eq(key).before(iText);
		});
		
		choiceContainerPie.find("input").click(function() { plotAccordingToChoicePie(choiceContainerPie, dataStatus, minipie); });
		
		
		
		function legendFormatter(label, series) {
			return label + \' [\' + series.data[0][1] + \'] (\' + Math.round(series.percent) + \'%)\';
		}
		
		$("#minipie").bind("plothover", function(event, pos, item) {
			if (item) {
				if ($(this).data("previous-post") != item.seriesIndex) {
					$(this).data("previous-post", item.seriesIndex);
				}
				$("#tooltip").remove();
				showTooltip(pos.pageX, pos.pageY, item.series.label + " [" + item.series.data[0][1] + "] (" + Math.round(item.series.percent) + "%)");
			} else {
				$("#tooltip").remove();
				previousPost = $(this).data("previous-post", -1);
			}
		});
		
		
		
		$("#reset").click(function () {
			plot = $.plot("#line", dataDuration, $.extend(true, {}, options, {
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
			<div id="mainlegend"></div><div id="line"></div>
			<div id="resetcontainer"><a href="#" id="reset">Reset&nbsp;Zoom</a></div>
			<div id="clear">
			<div id="minipiecontainer"><h3>Phase Status</h3><div id="minipielegend"></div><div id="minipie"></div></div>
			<div id="overviewcontainer"><h3>Overview</h3><div id="overview"></div></div>
			</div><h3 class="clear">Records <a data-text="Table Headers" data-id="phase" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="phase-table-rows"></div>' . $batch;
} else {
	$stdOut .= '<h2>No phases found</h2>';
};
odbc_close($bConn);
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehover'] . '<a href="#">Line Graph Clicking</a><div>Clicking a Point in the Line Graph will take you to the selected phase page.</div>' . $helptext['linedrag'] . $helptext['linemarkings'] . $helptext['piehover'] . $helptext['default30'] . $helptext['tablesorter'] . $helptext['tablesorterflot'] . $helptext['recordsetcolumns'] ;
require_once 'includes/footer.php'; ?>