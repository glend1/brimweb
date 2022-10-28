<?PHP
$title = 'OEE Overview';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$navItems = '';
$modes = ['area', 'discipline', 'department', 'departmentequipment'];
if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
} else {
	$mode = 'departmentequipment';
};
$navItems = '';
switch ($mode) {
	case 'department':
		$queryTop =  'select distinct department.id, department.name
		from type';
		break;
	case 'discipline':
		$queryTop =  'select distinct discipline.id, discipline.name
		from type
		left join Discipline on Discipline.ID = DisciplineFK';
		break;
	case 'area':
		$queryTop =  'select distinct Area.ID, Area.name
		from type
		left join Discipline on Discipline.ID = DisciplineFK
		left join Area on Area.ID = AreaFK';
		break;
	default:
		$queryTop =  'select distinct DepartmentEquipment.ID, DepartmentEquipment.name
		from type';
		break;
};
foreach ($modes as $modename) {
	switch ($modename) {
		case 'department':
			$realMode = 'Department';
			break;
		case 'discipline':
			$realMode = 'Discipline';
			break;
		case 'area':
			$realMode = 'Area';
			break;
		default:
			$realMode = 'Department Equipment';
			break;
	};
	if ($mode != $modename) {
		$navItems .= '<li><a href="oeeoverview.php' . fQueryString(['include' => ['mode' => $modename]]) . '">' . $realMode . '</a></li>';
	} else {
		$modeAlias = $realMode;
		if ($realMode == 'Department') {
			$linkMode = 'department';
		} elseif ($realMode == 'Department Equipment') {
			$linkMode = 'equipment';
		};
	};
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$hookReplace['contextmenu'] = '<div id="subnav"><div>Mode:</div><ul>' . $navItems . '</ul></div>';
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('oeeoverview', '[2004, 05, 18]');
fSetDates($startDate, $endDate, 30);
$queryActiveDepartments = $queryTop . ' left join DepartmentEquipment on DepartmentEquipmentFK = DepartmentEquipment.ID
left join Department on departmentfk = department.ID';
if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
	$aQueryDepartments[] = 'DepartmentFK is null';
	$queryActiveDepartments .= ' where ' . fOrThemReturn($_SESSION['permissions']['department'], 100, 'departmentfk', $aQueryDepartments);
};
$dataActiveDepartments = odbc_exec($conn, $queryActiveDepartments);
$sep = '';
$sTicks = '';
while(odbc_fetch_row($dataActiveDepartments)) {
	$aDepartments[] = [odbc_result($dataActiveDepartments, 1), odbc_result($dataActiveDepartments, 2)];
	$sTicks .= $sep . '[' . (count($aDepartments) - 1) . ', "' . odbc_result($dataActiveDepartments, 2) . '"]';
	$sep = ', ';
};
$queryCategories = 'select id, name from OEECategory order by typeorder desc';
$dataCategories = odbc_exec($conn, $queryCategories);
while(odbc_fetch_row($dataCategories)) {
	$aCategories[] = [odbc_result($dataCategories, 1), odbc_result($dataCategories, 2)];
};
foreach ($aCategories as $catKey => $catVal) {
	foreach ($aDepartments as $deptKey => $deptVal) {
		$aVals[$catKey][$deptKey] = rand(110,1100);
	};
};
$json = '';
$cSep = '';
foreach ($aVals as $catKey => $deptArray) {
	$json .= $cSep . '{ label: "' . $aCategories[$catKey][1] . '", data: [';
	$dSep = '';
	foreach ($deptArray as $deptKey => $value) {
		$json .= $dSep . '[' . $value . ', ' . $deptKey . ', "' . $aCategories[$catKey][1] . '"';
		if (isset($linkMode)) {
			switch ($aCategories[$catKey][0]) {
				//quality
				case 4:
					break;
				//performance
				case 5:
					break;
				//downtime
				case 1:
				case 8:
					$json .= ', "oeedowntime.php' . fQueryString(['include' => ['step' => 'area', 'mode' => $linkMode, $mode => $aDepartments[$deptKey][0]]]) . '"';
					//?step=area&mode=' . $linkMode . '&' . $mode . '=' . $aDepartments[$deptKey][0] . '"';
					break;
				//uptime
				case 2:
				case 3:
					if ($mode == 'departmentequipment') {
						$json .= ', "oeetimeline.php' . fQueryString(['include' => ['id' => $aDepartments[$deptKey][0]]]) . '"';
					};
					break;
			};
		};
		$json .= ']';
		$dSep = ', ';
	};
	$json .= ']}';
	$cSep = ', ';
};
$output = '<table>';
	$stdOut .= '<h2>Showing OEE data between ' . $startDate . ' and ' . $endDate . '</h2>
	<script type="text/javascript">

			
		$(function() {
			var data = [' . $json . '];
			
			var options = {
				colors: trendcolors,
				series: {
					stackpercent: true,
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
					axisLabel: "' . $modeAlias . '",
					ticks: [ ' . $sTicks . ' ]
				},
				xaxis: {
					axisLabel: "Count [Time] (Percent) {Total Percent}"
				},

				grid: {
					hoverable: true,
					clickable: true
				},
				legend: {
					noColumns: 6,
					container:$("#legend"),
					labelFormatter: function(label, series ) { 
						//console.log(series);
						//return \'<a href="\' + series.data[0][3] + \'">\' + label + \'</a>\';
						return label;
					}
				}
			};

			//var bar = $.plot("#bar", data, options);
			$.plot("#bar", data, options);
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
					//console.log(item);
					showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][2] + " [" + msToTime(item.series.data[item.dataIndex][0] * 1000) + "] (" + Math.round(item.series.percents[item.dataIndex]) + "%) {" + Math.round(item.datapoint[0]) + "%}");
				} else {
					$("#tooltip").remove();
					previousPost = $(this).data("previous-post", -1);
				}
			});
			
			choiceContainer.find("input").click(plotAccordingToChoices);
		function plotAccordingToChoices() {
			var newData = [];
			choiceContainer.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && data[key]) {
					newData.push(data[key]);
				}
			});
			if (newData.length > 0) {
				options.legend.container = null;
				options.legend.show = false;
				//bar.setData(newData);
				//console.log(newData);
				//bar.draw();
				$.plot("#bar", newData, options);
			}
		};
	plotAccordingToChoices();	
			
			
		});
		</script>
				<div id="bar"></div>
				<h3>Types</h3>
				<div id="legend"></div>';
$hookReplace['help'] = $helptext['standardcal'] . $helptext['barhover'] . $helptext['graphtoggle'] . $helptext['default30'] . '<a href="#">Context Sensitive "Briefcase" Menu</a><div>Click the context sensitive menu to change the mode used as a primary filter</div><a href="#">Downtime Analysis</a><div>You are able to jump to Downtime Analsis from the Overivew page, to do this you must be in Department or Department Equipment mode. Clicking one of the Downtime type bars will take you to that objects Downtime Analysis page.</div><a href="#">Timeline</a><div>You can naviate to the Timeline from the Overview page, to do this you must be in Department Equipment mode. Clicking one of the Uptime type bars will take you to that objects Timeline page.</div><a href="#">Quality Analysis</a><div>You are able to jump to Quality Analsis from the Overivew page. Clicking one of the Quality type bars will take you to that objects Quality Analysis page.</div><a href="#">Performance Analysis</a><div>You are able to jump to Performance Analsis from the Performance page. Clicking one of the Performance type bars will take you to that objects Performance Analysis page.</div>
';
require_once 'includes/footer.php'; ?>