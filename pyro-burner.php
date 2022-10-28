<?PHP 
$title = 'Primary Burner Trend';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][18] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['startdate'])) {
	$startdate = date("Y m d", strtotime($_GET['startdate']));
} else {
	$startdate = date_sub(new DateTime(), date_interval_create_from_date_string('6 Days'));
	$startdate = $startdate->format('Y m d');
};
if (isset($_GET['enddate'])) {
	$enddate = date("Y m d", strtotime($_GET['enddate']));
} else {
	$enddate = date("Y m d");
};
require_once 'includes/dbf_class.php';
$filelist = dir('\\\\pyrocat_hp\Datalogging\Incin & After\\');
$bWriteArray = False;
$bFirstRow = True;
while (false !== ($filename = $filelist->read())) {
	if (preg_match('/[(][w][i][d][e][)][.][D][B][F]$/i', $filename)) {
		if ($bFirstRow) {
			$sFirst = '[' . substr($filename, 0, 4) . ', ' . (substr($filename, 5, 2) - 1) . ', ' . substr($filename, 8, 2) . ']';
			$bFirstRow = False;
		};
		if (substr($filename, 0, 10) == $startdate) {
			$bWriteArray = True;
		};
		if ($bWriteArray) {
			$dbFiles[] = ['filename' => $filename];
		};
		if (substr($filename, 0, 10) == $enddate) {
			$bWriteArray = False;
		};
	};
};
foreach ($dbFiles as $key => $array) {
	$array['resource'] = new dbf_class($filelist->path . $array['filename']);
	$array['num'] = $array['resource']->dbf_num_rec - 1;
	if (isset($dbFiles[$key - 1]['offsetend'])) {
		$offset = $dbFiles[$key - 1]['offsetend'];
	} else {
		$offset = 0;
	};
	$offset++;
	$array['offset'] = $offset;
	$array['offsetend'] = $array['num'] + $offset;
	$iTotal = $array['offsetend'];
	$dbFiles[$key] = $array;
};
$iInterval = $iTotal / 250;
$iLoops = 1;
$iDataPoint = 1;
$iCount = 0;
while ($iDataPoint < $iTotal) {
	$iDataPoint = round($iInterval * $iLoops, 0);
	$iLoops++;
	foreach ($dbFiles as $key => $array) {
		if ($iDataPoint >= $array['offset'] && $iDataPoint <= $array['offsetend']) {
			if ($iDataPoint - $array['offset'] == 0) {
				$iRow = 1;
			} else {
				$iRow = $iDataPoint - $array['offset'];
			};
			$row = $array['resource']->getRowAssoc($iRow);
			$timestamp = (strtotime(substr($row['Date'], 0, 4) . '-' . substr($row['Date'], 4, 2) . '-' . substr($row['Date'], 6, 2) . ' ' . $row['Time']) * 1000) . ' ';
			$aDataPoints[$timestamp] = ['Incinerator 1' => $row['INCIN1\TT1'], 'Incinerator 2' => $row['INCIN2\TT2'], 'Incinerator 3' => $row['INCIN3\TT3'], 'Setpoint' => 70];
			$iCount++;
			break;
		};
	};
};
ksort($aDataPoints);
/*foreach ($dbFiles as $key => $array) {
	dbase_close($array['resource']);
};*/
$data1 = '{ label:"Incinerator 1 = 0", data:[';
$data2 = '{ label:"Incinerator 2 = 0", data:[';
$data3 = '{ label:"Incinerator 3 = 0", data:[';
$data4 = '{ dashes: { show: true }, lines: { show: false }, data:[';
$sep = '';
foreach ($aDataPoints as $key => $array) {
	$data1 .= $sep . '[' . trim($key) . ', ' . $array['Incinerator 1'] . ']';
	$data2 .= $sep . '[' . trim($key) . ', ' . $array['Incinerator 2'] . ']';
	$data3 .= $sep . '[' . trim($key) . ', ' . $array['Incinerator 3'] . ']';
	$data4 .= $sep . '[' . trim($key) . ', ' . $array['Setpoint'] . ']';
	$sep = ', ';
};
$data1 .= ' ] }, ';
$data2 .= ' ] }, ';
$data3 .= ' ] }, ';
$data4 .= ' ] }';
$data = '[ ' . $data1 . $data2 . $data3 . $data4 . ' ]';
$stdOut .= '<h2>Showing data from ' . $startdate . ' till ' . $enddate . '</h2>
<script type="text/javascript">
	$(function() {

		var data = ' . $data . '; 

		var options = {
			colors: trendcolors,
			series: {
				lines: {
					show: true
				}
			},
			crosshair: {
				color: trendcrosshair,
				mode: "x"
			},
			grid: {
				hoverable: true,
				autoHighlight: false,
				markings: fMarkings
			},
			xaxis: {
				mode: "time",
				timeformat: "%d/%m/%y<br />%H:%M:%S",
				ticks: 5,
				tickLength: 0,
				axisLabel: "Date/Time"
			},
			yaxis: {
				axisLabel: "Temperature (&degC)"
			}
		};

		var plot = $.plot("#pyro-burner", data, options);
		
		var overview = $.plot("#pyro-overview", data, {
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				shadowSize: 0
			},
			yaxis: {
				show:false
			},
			xaxis: {
				mode: "time",
				timeformat: "%d/%m/%y<br />%H:%M:%S",
				ticks: 5,
				tickLength: 0
			},
			selection: {
				color: trendselection,
				mode: "x"
			},
			grid: {
				markings: fMarkings
			},
			legend: {
				show:false
			}
		});
		var iDataSmall = plot.getAxes().xaxis.min;
		var iDataBig = plot.getAxes().xaxis.max;
		
		// now connect the two

		$("#pyro-burner").bind("plotselected", function (event, ranges) {

			// do the zooming

			plot = $.plot("#pyro-burner", data, $.extend(true, {}, options, {
				xaxis: {
					min: ranges.xaxis.from,
					max: ranges.xaxis.to
				}
			}));
			// don"t fire event on the overview to prevent eternal loop

			overview.setSelection(ranges, true);
		});

		$("#pyro-overview").bind("plotselected", function (event, ranges) {
			plot.setSelection(ranges);
		});
		

		var updateLegendTimeout = null;
		var latestPosition = null;

		function updateLegend() {

			var legends = $("#pyro-burner .legendLabel");
			updateLegendTimeout = null;

			var pos = latestPosition;
			x = new Date(pos.x);
			$("#datetimestamp").html(x.toUTCString());

			var axes = plot.getAxes();
			if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
				pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
				return;
			}

			var i, j, dataset = plot.getData();
			for (i = 0; i < dataset.length; ++i) {

				var series = dataset[i];

				// Find the nearest points, x-wise

				for (j = 0; j < series.data.length; ++j) {
					if (series.data[j][0] > pos.x) {
						break;
					}
				}

				// Now Interpolate

				var y,
					p1 = series.data[j - 1],
					p2 = series.data[j];

					
				if (p1 == null) {
					y = p2[1];
				} else if (p2 == null) {
					y = p1[1];
				} else {
					y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
				}

				legends.eq(i).text(series.label.replace(/=.*/, "= " + y.toFixed(2)));
			}
		}

		$("#pyro-burner").bind("plothover",  function (event, pos, item) {
			latestPosition = pos;
			if (!updateLegendTimeout) {
				updateLegendTimeout = setTimeout(updateLegend, 50);
			}
		});
		
		$("#reset").click(function () {
			plot = $.plot("#pyro-burner", data, $.extend(true, {}, options, {
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
<div class="printsafe" id="pyro-burner"></div>
<div id="datetimestampcontainer"><div id="datetimestamp"></div></div>
<div id="resetcontainer"><a href="#" id="reset">Reset&nbsp;Zoom</a></div>
<h3>Overview</h3>
<div class="printsafe" id="pyro-overview"></div>';
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('pyro-burner', $sFirst, 'date');
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehoverany'] . $helptext['linemarkings'] . $helptext['linedrag'] . $helptext['default7'];
require_once 'includes/footer.php'; ?>