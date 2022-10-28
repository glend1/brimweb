<?PHP 
$title = 'OEE Timeline';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('oeetimeline', '[2014, 01, 27]');
fSetDates($startDate, $endDate, 30);
if (isset($_GET['trim'])) {
	switch ($_GET['trim']) {
		default:
		case 'no':
			$trim = 'yes';
			break;
		case 'yes':
			$trim = 'no';
			break;
	};
} else {
	$trim = 'no';
};
/*switch($trim) {
	default:
	case 'yes';
		$queryTrim = 'no';
		break;
	case 'no';
		$queryTrim = 'yes';
		break;
};*/
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$querySubnav = 'select id, name 
from departmentequipment
cross apply (select departmentfk from DepartmentEquipment where id = ' . $_GET['id'] . ') as lookupt
where lookupt.departmentfk = departmentequipment.DepartmentFK';
$stdOut .= '<div id="subnav"><div>Trim Data</div>
<ul><li><a href="oeetimeline.php' . fQueryString(['include' => ['trim' =>  $trim]]) . '">' . ucwords($trim) . '</a></li></ul>
<div>Other Equipment</div><ul>';
$dataSubnav = odbc_exec($conn, $querySubnav);
while(odbc_fetch_row($dataSubnav)) {
	if (odbc_result($dataSubnav, 1) == $_GET['id']) {
		$thisEquip = odbc_result($dataSubnav, 2);
	};
	$stdOut .= '<li><a href="oeetimeline.php' . fQueryString(['include' => ['id' =>  odbc_result($dataSubnav, 1)], 'exclusive' => ['startdate', 'enddate']]) . '">' . odbc_result($dataSubnav, 2) . '</a></li>';
};
$stdOut .= '</ul></div>';
$queryOeeUptime = 'select oeename.name, StartDateTime, enddatetime, Duration, comment, oeenamefk, oeecategory.name, oeecategory.id
from records
join type on type.id = typefk
join oeename on oeename.id = oeenamefk
join oeecategory on oeecategory.id = oeecategoryfk
where DepartmentEquipmentFK = ' . $_GET['id'] . ' and ((StartDateTime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\' or enddatetime between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or ((StartDateTime < \'' . $startDate . '\' and StartDateTime < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') and (enddatetime > \'' . $startDate . '\' and enddatetime > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')))';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$queryOeeUptime .= ' and ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk');
};
$queryOeeUptime .= 'order by startdatetime';
$dataOeeUptime = odbc_exec($conn, $queryOeeUptime);
$oeeTable = '<h3>Records <a data-id="oee" class="table-row-button" href="none.php"><span class="icon-list"></span></a></h3><div class="tablesorter-row-hider" id="oee-table-rows"></div><table class="records" id="oee-sort"><thead><tr><th>Reason</th><th>Category</th><th>Start Date/Time</th><th>End Date/Time</th><th>Duration</th><th>Comment</th></tr></thead><tbody>';
$iRowNo = 0;
$aGantt = '';
$aGanttY[] = 1;
while(odbc_fetch_row($dataOeeUptime)) {
	if ($iRowNo % 2 == 0) {
		$rowHeader = 'oddRow';
	} else {
		$rowHeader = 'evenRow';
	};
	$iRowNo++;
	if ($trim == 'no') {
		if (strtotime(odbc_result($dataOeeUptime, 2)) < strtotime($startDate)) {
			$oeeStart = strtotime($startDate) * 1000;
		} else {
			$oeeStart = strtotime(odbc_result($dataOeeUptime, 2)) * 1000;
		};
		if (strtotime(odbc_result($dataOeeUptime, 3)) > strtotime($endDate)) {
			$oeeEnd = strtotime($endDate) * 1000;
		} else {
			$oeeEnd = strtotime(odbc_result($dataOeeUptime, 3)) * 1000;
		};
	} else {
		$oeeStart = strtotime(odbc_result($dataOeeUptime, 2)) * 1000;
		$oeeEnd = strtotime(odbc_result($dataOeeUptime, 3)) * 1000;
	};
	if (!isset($oeeStartGraph)) {
		$oeeStartGraph = $oeeStart;
	};
	if (!isset($oeeEndGraph)) {
		$oeeEndGraph = $oeeEnd;
	} elseif ($oeeEndGraph < $oeeEnd) {
		$oeeEndGraph = $oeeEnd;
	};
	$oeeTable .= '<tr class="' . $rowHeader . '"><td><a data-text="Compare Similar" href="oeecompare.php' . fQueryString(['include' => ['id' =>  odbc_result($dataOeeUptime, 6)], 'exclusive' => ['startdate', 'enddate']]) . '"><span class="icon-link icon-hover-hint"></span></a> ' . odbc_result($dataOeeUptime, 1) . '</td><td>' . odbc_result($dataOeeUptime, 7) . '</td><td>' . substr(odbc_result($dataOeeUptime, 2), 0, -4) . '</td><td>' . substr(odbc_result($dataOeeUptime, 3), 0, -4) . '</td><td data-duration="';
	if (odbc_result($dataOeeUptime, 4) == "") {
		$oeeTable .= 0;
	} else {
		$oeeTable .= odbc_result($dataOeeUptime, 4);
	};
	$oeeTable .= '">' . fToTime(odbc_result($dataOeeUptime, 4)) . '</td>';
	if (odbc_result($dataOeeUptime, 5)) {
		$oeeTable .= '<td><a href="#" class="toggle-table-sorter">Show</a></td></tr><tr class="' . $rowHeader . ' hiddenrow"><td colspan="6">' . odbc_result($dataOeeUptime, 5) . '</td></tr>';
	} else {
		$oeeTable .= '<td class="emptyCell">None</td>';
	};
	'</tr>';
	$bGantt = true;
	foreach ($aGanttY as $key => $value) {
		if ((strtotime(odbc_result($dataOeeUptime, 2)) * 1000) >= $value) {
			$aGanttY[$key] = strtotime(odbc_result($dataOeeUptime, 3)) * 1000;
			$bGantt = false;
			$iGanttPos = $key + 1;
			break;
		};
	};
	if ($bGantt) {
		$aGanttY[] = strtotime(odbc_result($dataOeeUptime, 3)) * 1000;
		$iGanttPos = count($aGanttY);
	};
	if (!isset($categoryLookup[odbc_result($dataOeeUptime, 8)])) {
		$categoryLookup[odbc_result($dataOeeUptime, 8)] = odbc_result($dataOeeUptime, 7);
	};
	if (odbc_result($dataOeeUptime, 4) != 0) {
		/*$aGantt[odbc_result($dataOeeUptime, 8)][] = '[' . (strtotime(odbc_result($dataOeeUptime, 2)) * 1000) . ',' . (12 - $iGanttPos) . ',' . (strtotime(odbc_result($dataOeeUptime, 3)) * 1000) . ',"' . odbc_result($dataOeeUptime, 1) . '", "' . 'link' . '"]';*/
		$aGantt[odbc_result($dataOeeUptime, 8)][] = '[' . $oeeStart . ',' . (12 - $iGanttPos) . ',' . $oeeEnd . ',"' . odbc_result($dataOeeUptime, 1) . '", "' . 'link' . '", ' . ((strtotime(odbc_result($dataOeeUptime, 3)) - strtotime(odbc_result($dataOeeUptime, 2))) * 1000) . ']';
	};
};
$oeeTable .= '</body>' . fTableFooter(['id' => 'oee-sort', 'cols' => 6]) . '</table>';
$aData = array();
if (!empty($aGantt)) {
	foreach ($aGantt as $key => $array) {
		$aData[] = '{"label":"' . $categoryLookup[$key] . '","data":
		[' . implode(',', $array) . ']}
		';
	};
	$stdOut .= '<h2>Showing ' . $thisEquip . ' between ' . $startDate . ' and ' . $endDate . '</h2><div id="gantt"></div>
	<div><h3>Types</h3>
	<div id="legend"></div></div>
	<script language="javascript">
	$(function() {
	var searchArray = new Array();
	fTableSorter({sorttable: "#oee-sort", 
		sortorder: [[2,0]],
		rowheaders: "oee",
		headers: {4 : { sorter: "duration" }}
	});
	var data,o;
	data = [' . implode(',', $aData) . '];
	o = {
		colors: trendcolors,
		xaxis:{
			min: ' . $oeeStartGraph . ',
			max: ' . $oeeEndGraph . ',
			mode:"time",
			timeformat: "%d/%m/%y<br />%H:%M:%S",
			ticks: 5,
			twelveHourClock:true,
			axisLabel: "Date/Time"
		},
		yaxis:{
			show:false,
			min:0,
			max:12
		},
		series:{
			gantt:{
				active:true,
				show:true,
				barHeight:1
			}
		},
		selection: {
			color: trendselection,
			mode: "x"
		},
		grid:{
			hoverable:true/*,
			clickable:true*/
		},
		legend:{
			show:true,
			container:$("#legend"),
			noColumns:5,
			labelFormatter: function(label, series) { 
				return label + " (" + series.data.length + ")";
			}
		}	
	}

	recordSwapTarget = $.plot($("#gantt"),data,o);

	/*$("#gantt").bind("plotclick", function(event,pos,item) {
		if (item) {
			window.location.href = data[item.seriesIndex].data[item.dataIndex][4];
		};
	});*/

	$("#gantt").bind("mouseout", function() {
		$("#tooltip").remove();
		$(this).data("previous-post", -1);
	});
	$("#gantt").bind("plothover", function(event, pos, item) {
		if (item) {
			if ($(this).data("previous-post") != item.seriesIndex) {
				$(this).data("previous-post", item.seriesIndex);
			}
			$("#tooltip").remove();
			showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][3] + " (" + msToTime(item.series.data[item.dataIndex][5]) + ")");
		} else {
			$("#tooltip").remove();
			previousPost = $(this).data("previous-post", -1);
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
			$(legendChoiceContainer.eq(key)).before(iText);
		});
		o.legend.show = false;
		o.legend.container = false;
		choiceContainer.find("input").click(plotAccordingToChoices);
		function plotAccordingToChoices() {
			var checkbox = new Array();
			var newData = [];
			choiceContainer.find("input:checked").each(function () {
				checkbox.push("^" + data[this.name].label + "$");
				var key = $(this).attr("name");
				if (key && data[key]) {
					newData.push(data[key]);
				}
			});
			if (newData.length > 0) {
				var sCheckbox = checkbox.join("|");
				searchArray[1] = "/" + sCheckbox + "/i";
				$("#oee-sort").trigger(\'search\', [searchArray]);
				recordSwapTarget = $.plot($("#gantt"),newData,o);
			}
		};';
		$stdOut .= '});</script>';
	$stdOut .= $oeeTable;
} else {
	$stdOut .= '<h2>No OEE data available</h2>';
};
$hookReplace['help'] = $helptext['timelinehover'] . $helptext['graphtoggle'] . '<a href="#">OEE Comparison</a><div>Clicking the link icon in the Reason column of the records will take you to the OEE comparison page for the selected reason</div><a href="#">Context Sensitive "Briefcase" Menu</a><div>Using the Breifcase menu you are able to swap between Equipment for the selected Department.<br />In addition you are able to toggle if the data should be trimmed or not. When trimmed the Timeline Bars will not match the data 100%. The data shown while hovering the mouse over the bar and the tabular data is accurate. However, when untrimmed OEE records before or after the selected date range will not be visible.</div>' . $helptext['default30'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'] . $helptext['graphtoggle'] . $helptext['tablesorterflot'];
require_once 'includes/footer.php'; ?>