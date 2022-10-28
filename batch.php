<?PHP 
$title = 'Batch Selector';
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
	$_SESSION['sqlMessage'] = 'Select a Recipe!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batch', '[2004, 05, 18]');
$hookReplace['downloadicon'] = '<a data-text="Download" href="#" class="menucontext"><span class="icon-download-alt icon-hover-hint icon-large"></span></a>';
$hookReplace['downloadmenu'] = '<div id="downloadmenu"></div>';
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryBatches = 'select distinct Campaign_ID, Lot_ID, Batch_ID, Batch_Log_ID, dbname, duration, product_id, recipe_id, log_open_dt, log_close_dt, int_recipe, Description, train_id, departmentequipmentfk 
from (
	select Campaign_ID, Lot_ID, Batch_ID, [oldBatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID], \'oldbatchhistory\' as dbname, datediff(ss, log_open_dt, log_close_dt) as duration, product_id, recipe_id, log_open_dt, log_close_dt, cast(recipe_version as int) int_recipe, CodeTable.Description, train_id 
	from [oldBatchHistory].[dbo].[BatchIdLog]
	left join 
		(select Batch_Log_ID, Action_CD
			from (
				select Batch_Log_ID, action_cd, ROW_NUMBER() over (partition by batch_log_id order by datetime desc, action_cd asc) as number 
				from [oldBatchHistory].[dbo].[batchdetail]
				where (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405) 
				) as statustable
			where number = 1
		) as statustable on [oldBatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID] = statustable.Batch_Log_ID
	left join CodeTable on Action_CD = Code
	where Recipe_ID = \'' . $_GET['recipe'] . '\'
	union
	select Campaign_ID, Lot_ID, Batch_ID, [BatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID], \'batchhistory\' as dbname, datediff(ss, log_open_dt, log_close_dt) as duration, product_id, recipe_id, log_open_dt, log_close_dt, cast(recipe_version as int) int_recipe, CodeTable.Description, train_id 
	from [BatchHistory].[dbo].[BatchIdLog]
	left join 
		(select Batch_Log_ID, Action_CD
			from (
				select Batch_Log_ID, action_cd, ROW_NUMBER() over (partition by batch_log_id order by datetime desc, action_cd asc) as number 
				from [BatchHistory].[dbo].[batchdetail]
				where (action_cd = 205 or action_cd = 209 or action_cd = 400 or action_cd = 401 or action_cd = 402 or action_cd = 404 or action_cd = 405) 
				) as statustable
			where number = 1
		) as statustable on [BatchHistory].[dbo].[BatchIdLog].[Batch_Log_ID] = statustable.Batch_Log_ID
	left join CodeTable on Action_CD = Code
	where Recipe_ID = \'' . $_GET['recipe'] . '\'
) as batchtable
left join [plantavail].[dbo].[train] on train_id = train 
where ';
if (isset($aDE['department'])) {
	$queryBatches .= 'departmentfk = ' . $aDE['department'];
} else {
	$queryBatches .= 'departmentfk is NULL';
};
$queryBatches .= ' and ((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))';
/*if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryBatch[] = 'DepartmentFK is null';
	$queryBatches .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryBatch);
};*/
$queryBatches .= ' order by log_open_dt, train_id';
$dataBatches = odbc_exec($bConn, $queryBatches);
$batch = '<table data-name="Batches" class="records" id="batch-sort"><thead><tr><th>Batch ID</th><th>Duration</th><th>Start Time</th><th>End Time</th><th>Product</th><th>Recipe</th><th>Version</th><th>Train</th><th>Status</th></tr></thead><tbody>';
$i = 1;
$dataDuration = array();
$dataStatus = array();
while(odbc_fetch_row($dataBatches)) {
	$recipeName = urldecode($_GET['recipe']);
	$batchStatus = trim(substr(odbc_result($dataBatches, 12), 10));
	$equipmentUrl = '';
	if (odbc_result($dataBatches, 14)) {
		$equipmentUrl = 'equipment=' . odbc_result($dataBatches, 14) . '&';
	};
	$dataDuration[odbc_result($dataBatches, 13)][] = '[' . strtotime(odbc_result($dataBatches, 9)) * 1000 . ',' . odbc_result($dataBatches, 6) . ',\'' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . ' (' . fToTime(odbc_result($dataBatches, 6)) . ')\', \'batchsummary.php?' .  $departmentURL . $equipmentUrl . 'batch' . '=' . urlencode(odbc_result($dataBatches, 4)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '\']';
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
	$batch .= '<td><a href="batchsummary.php?' . $departmentURL . $equipmentUrl . 'batch=' . urlencode(odbc_result($dataBatches, 4)) . '&dbname=' . urlencode(odbc_result($dataBatches, 5)) . '" >' . odbc_result($dataBatches, 1) . '/' . odbc_result($dataBatches, 2) . '/' . odbc_result($dataBatches, 3) . '</a></td><td data-duration="' . odbc_result($dataBatches, 6) . '">' . fToTime(odbc_result($dataBatches, 6)) . '</td><td>' . substr(odbc_result($dataBatches, 9), 0, -4) . '</td><td>' . substr(odbc_result($dataBatches, 10), 0, -4) . '</td><td>' . odbc_result($dataBatches, 7) . '</td><td>' . odbc_result($dataBatches, 8) . '</td><td>' . odbc_result($dataBatches, 11) . '</td><td>' . odbc_result($dataBatches, 13) . '</td><td>' . $batchStatus . '</td></tr>';
};
$batch .= '</tbody>' . fTableFooter(['id' => 'batch-sort', 'cols' => 9, 'totals' => [[0 => "count", 1 => 'time-mean'], [1 => 'time-sum']]]) . '</table>';
if ($i > 1) {
	$stdOut .= '<h2>Showing ' . $recipeName . ' Batches between ' . $startDate . ' and ' . $endDate . '</h2>';
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
			sortorder: [[2,0]], 
			rowheaders: "batch",
			headers: {4 : { columnSelector: false },
					1 : { sorter: "duration" }}
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
			searchArray[7] = "/" + sCheckbox + "/i";
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
			//console.log(searchArray);
			//$.tablesorter.setFilters($("#batch-sort"), [searchArray], true);
			//console.log($.tablesorter.getFilters($("#batch-sort")));
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
				hoverable: true/*,
				clickable: true*/
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
			<div id="mainlegend"></div>
			<div id="line" ></div>
			<div id="resetcontainer"><a href="#" id="reset">Reset&nbsp;Zoom</a></div>
			<div id="clear">
			<div id="minipiecontainer"><h3>Batch Status</h3><div id="minipielegend"></div><div id="minipie"></div></div>
			<div id="overviewcontainer"><h3>Overview</h3><div id="overview"></div></div>
			</div><h3 class="clear">Records <a data-text="Table Headers" data-id="batch" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="batch-table-rows"></div>' . $batch;
} else {
	$stdOut .= '<h2>No batches available</h2>';
};
odbc_close($bConn);
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehover'] . '<a href="#">Line Graph Clicking</a><div>Clicking a Point in the Line Graph will take you to the selected batch page.</div>' . $helptext['linedrag'] . $helptext['linemarkings'] . $helptext['piehover'] . $helptext['default30'] . $helptext['tablesorter'] . $helptext['tablesorterflot'] . $helptext['recordsetcolumns'] ;
require_once 'includes/footer.php'; ?>