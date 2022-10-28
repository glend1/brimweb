<?PHP
$title = 'Batch Recipe Selector';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aDE = fPermissionDE();
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batchrecipe', '[2004, 05, 18]');
fSetDates($startDate, $endDate, 30);
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryBatchTrain = 'select train_id, SUM(count) as count, Recipe_ID, departmentfk
from (select Recipe_ID, train_id, COUNT(*) as count
from oldbatchhistory.dbo.BatchIdLog
where 
((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))
group by Recipe_ID, Train_ID
union
select Recipe_ID, train_id, COUNT(*) as count
from batchhistory.dbo.BatchIdLog
where 
((Log_Open_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\'
or Log_Close_DT between \'' . $startDate . '\' and \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
or ((Log_Open_DT < \'' . $startDate . '\' and Log_Open_DT < \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\')
and ((Log_Close_DT > \'' . $startDate . '\' and Log_Close_DT > \'' . date('Y-m-d H:i:s', strtotime($endDate) + 1) . '\') or Log_Close_DT is null)))
group by Recipe_ID, Train_ID) as temp
left join [plantavail].[dbo].[train] on train_id = train ';
if (!isset($aDE['department'])) {
	$queryBatchTrain .= 'where departmentfk is null';
} else {
	$queryBatchTrain .= 'where departmentfk = ' . $aDE['department'];
};
$queryBatchTrain .= ' group by Recipe_ID, Train_ID, departmentfk
order by train_id desc, count desc';
$data = odbc_exec($bConn, $queryBatchTrain);
$output = '<table>';
$chartData = Array();
$aLabelName = Array();
$iRowNum = 0;
$cellsRemain = '';
$aUnused = array();
$columns = 3;
$linkPage = 'batch.php';
while(odbc_fetch_row($data)) {
	$iRowNum++;
	if (!in_array(odbc_result($data, 1), $aLabelName)) {
		$aLabelName[] = odbc_result($data, 1);
	};
	$currPos = array_search(odbc_result($data, 1), $aLabelName);
	$cellsRemain = $columns - ($iRowNum % $columns);
	if ($iRowNum % $columns == 1) {
		$output .= '<tr>';
	};
	$chartData[odbc_result($data, 3)][odbc_result($data, 1)] = '[' . odbc_result($data, 2) . ',' . $currPos . ', "' . odbc_result($data, 1) . '/' . odbc_result($data, 3) . '", "' . $linkPage . fQueryString(['include' => ['recipe' => odbc_result($data, 3), 'department' => odbc_result($data, 4)]]) . '"]';
	$output .= '<td>' . $iRowNum . '.<a href="' . $linkPage . fQueryString(['include' => ['recipe' => odbc_result($data, 3), 'department' => odbc_result($data, 4)]]) . '" >' . odbc_result($data, 1) . '/' . odbc_result($data, 3) . '</a> (' . odbc_result($data, 2) . ')</td>';
	if ($iRowNum % $columns == $columns) {
		$output .= '</tr>';
	};
	if (!odbc_result($data, 4)) {
		$aUnused[odbc_result($data, 1)] = odbc_result($data, 1);
	};
};
$sTicks = '';
$sep = '';
foreach ($aLabelName as $key => $value) {
	$sTicks .= $sep . '[' . $key . ', "' . $value . '"]';
	$sep = ', ';
};
if ($cellsRemain != $columns) {
	for ($i = 0; $i < $cellsRemain; $i++) {
		$output .= '<td>';
	};
};
$output .= '</tr></table>';
if ($output != '<table></tr></table>') {
	$formattedData = array();
	foreach ($chartData as $key => $array) {
		$formattedData[$key] = '{ label:"' . $key . '" , data: [ ' . implode(', ', $array) . ' ] }';
	};
};
if (isset($formattedData)) {
	$stdOut .= '<h2>Showing recipe data between ' . $startDate . ' and ' . $endDate . '</h2>
	<script type="text/javascript">

			
		$(function() {

			var data =[' . implode(', ', $formattedData) . '] //[ ["January", 10], ["February", 8], ["March", 4], ["April", 13], ["May", 17], ["June", 9] ];
			/*if (data.length > 20) {
				data = $.plot.JUMlib.prepareData.pareto(data, "#", true, 19);
			} else {
				data = $.plot.JUMlib.prepareData.pareto(data, false);
			};*/
			//console.log(data);
			var bar = $.plot("#bar", data, {
				colors: trendcolors,
				series: {
					stack: true,
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
					axisLabel: "Train",
					ticks: [ ' . $sTicks . ' ]
				},
				xaxis: {
					axisLabel: "Count (Value) [Current Total]"
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
						var iCount = 0;
						for (i = 0; i < series.data.length; i++) {
							iCount += series.data[i][0];
						};
						return \'<a href="\' + series.data[0][3] + \'">\' + label + \'</a> [\' + i + \'] (\' + iCount + \')\';
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
					showTooltip(pos.pageX, pos.pageY, item.series.data[item.dataIndex][2] + " (" + (item.datapoint[0] - item.datapoint[2]) + ") [" + item.datapoint[0] + "]");
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
				<h3>Recipes<span id="dynamic-totals"></span><div class="hinttext">[Trains] (Total)</div></h3>
				<div id="legend"></div>';
} else {
	$stdOut .= '<h2>No data available</h2>';
};

odbc_close($bConn);
$hookReplace['help'] = $helptext['standardcal'] . $helptext['barhover'] . '<a href="#">Bar Graph Clicking</a><div>Clicking a Bar in the Bar Graph will take you to the selected recipes page.</div>'
 . $helptext['graphtoggle'] . $helptext['default30'];
require_once 'includes/footer.php'; ?>