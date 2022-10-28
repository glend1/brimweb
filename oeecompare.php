<?PHP 
$title = 'OEE Duration Comparison';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'Type not selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 30);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('oeecompare', '[2014, 01, 27]');
$queryMeta = 'select top 1 name
from oeename
where id = ' . $_GET['id'];
$dataMeta = odbc_exec($conn, $queryMeta);
odbc_fetch_row($dataMeta);
$oeeType = odbc_result($dataMeta, 1);
$queryOee = 'select StartDateTime, enddatetime, Duration, comment, discipline.name, departmentequipment.name, disciplinefk, departmentequipmentfk
from records
join type on type.id = typefk
join departmentequipment on departmentequipmentfk = departmentequipment.id
join discipline on disciplinefk = discipline.id
where oeenamefk = ' . $_GET['id'] . ' and ((StartDateTime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\' or enddatetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or ((StartDateTime < \'' . $startDate . '\' and StartDateTime < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') and (enddatetime > \'' . $startDate . '\' and enddatetime > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$queryOee .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk');
};
$queryOee .= 'order by startdatetime';
$dataOee = odbc_exec($conn, $queryOee);
$oee = '<table class="records" id="oee-sort"><thead><tr><th>Equipment</th><th>Discipline</th><th>Start Date/Time</th><th>End Date/Time</th><th>Duration</th><th>Comment</th></tr></thead><tbody>';
$i = 1;
$dataDuration = array();
while(odbc_fetch_row($dataOee)) {
	if (!isset($aDiscipline[odbc_result($dataOee, 7)])) {
		$aDiscipline[odbc_result($dataOee, 7)] = odbc_result($dataOee, 5);
	};
	if (!isset($aDepartment[odbc_result($dataOee, 8)])) {
		$aDepartment[odbc_result($dataOee, 8)] = odbc_result($dataOee, 6);
	};
	/*if (!isset($reverseLookup[odbc_result($dataOee, 8)][odbc_result($dataOee, 7)])) {
		$lookupArray[odbc_result($dataOee, 8)] = [odbc_result($dataOee, 8), odbc_result($dataOee, 7)];
		$reverseLookup[odbc_result($dataOee, 8)][odbc_result($dataOee, 7)] = 'set';
	};*/
	$dataDuration[odbc_result($dataOee, 8)][odbc_result($dataOee, 7)][] = '[' . strtotime(odbc_result($dataOee, 1)) * 1000 . ',' . odbc_result($dataOee, 3) . ',\'' . fToTime(odbc_result($dataOee, 3)) . '\', \'test\']';
	if ($i % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$i++;
	$oee .= '<tr class="' . $rowHeader . '"><td data-checkbox="' . odbc_result($dataOee, 6) . ' ' . odbc_result($dataOee, 5) . '">' . odbc_result($dataOee, 6) . '</td><td>' . odbc_result($dataOee, 5) . '</td><td>' . substr(odbc_result($dataOee, 1), 0, -4) . '</td><td>' . substr(odbc_result($dataOee, 2), 0, -4) . '</td><td data-duration="';
	if (odbc_result($dataOee, 3) == "") {
		$oee .= 0;
	} else {
		$oee .= odbc_result($dataOee, 3);
	};
	$oee .= '">' . fToTime(odbc_result($dataOee, 3)) . '</td><td>';
	if (odbc_result($dataOee, 4)) {
		$oee .= '<a href="#" class="toggle-table-sorter">Show</a></td></tr><tr class="' . $rowHeader . ' hiddenrow"><td colspan="6">' . odbc_result($dataOee, 4) . '</td></tr>';
	} else {
		$oee .= 'None';
	};
	$oee .= '</td></tr>';
};
$oee .= '</tbody>' . fTableFooter(['id' => 'oee-sort', 'cols' => 6]) . '</table>';
if ($i > 1) {
	$stdOut .= '<h2>Showing "' . $oeeType . '" duration<br />between ' . $startDate . ' and ' . $endDate . '</h2>';
	$sDataDuration = '';
	$sep = '';
	foreach ($dataDuration as $kDept => $array) {
		foreach ($array as $kDisc => $arrayArray) {
			$sDataDuration .= $sep . '{label:"' . $aDepartment[$kDept] . ' ' . $aDiscipline[$kDisc] . '", data:[' . implode(',', $arrayArray) . ']}';
			$sep = ', ';
		};
	};
$stdOut .= '<script type="text/javascript">
	var searchArray = new Array();
	$(function() {
	
	
		function plotAccordingToChoice(container, data, plot) {
			var labelcheckbox = new Array();
			container.find("input").each(function () {
					if (this.checked) {
						labelcheckbox.push("^" + data[this.name].label + "$");
					};
					data[this.name].lines = { show: this.checked };
					data[this.name].points = { show: this.checked };
			});
			searchArray[0] = "/" + labelcheckbox.join("|") + "/i";
			$("#oee-sort").trigger(\'search\', [searchArray]);
				plot.getOptions().legend.container = null;
				plot.getOptions().legend.show = false;
				plot.setData(data);
				plot.draw();
		};
		
		fTableSorter({sorttable: "#oee-sort", 
			sortorder: [[2,0]],
			rowheaders: "oee",
			headers: {0 : { sorter: "checkbox", filter: "parsed" },
				4 : { sorter: "duration" }}
		});
		var dataDuration = [' . $sDataDuration . ']; 
		//console.log(dataDuration);
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
							//console.log(series.data);
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

		var overview = $.plot("#fulloverview", dataDuration, {
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

		$("#fulloverview").bind("plotselected", function (event, ranges) {
			plot.setSelection(ranges);
		});
		
		$("#line").bind("plothover", function(event, pos, item) {
				if (item) {
					if ($(this).data("previous-post") != item.seriesIndex) {
						$(this).data("previous-post", item.seriesIndex);
					}
					$("#tooltip").remove();
					showTooltip(pos.pageX, pos.pageY, item.series.label + " (" + item.series.data[item.dataIndex][2] + ")");
				} else {
					$("#tooltip").remove();
					previousPost = $(this).data("previous-post", -1);
				}
			});
		/*$("#line").bind("plotclick", function(event,pos,item) {
			if (item) {
				if (item.series.data[item.dataIndex][3]) {
					window.location.href = item.series.data[item.dataIndex][3];
				};
			};
		});*/
		
		
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
			<h3>Overview</h3><div id="fulloverview"></div>
			</div><h3>Records <a data-text="Table Headers" data-id="oee" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3><div class="tablesorter-row-hider" id="oee-table-rows"></div>' . $oee;
} else {
	$stdOut .= '<h2>No OEE Types found</h2>';
};
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehover'] . $helptext['linedrag'] . $helptext['linemarkings'] . $helptext['default30'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] . $helptext['graphtoggle'] . $helptext['tablesorterflot'];
require_once 'includes/footer.php'; ?>