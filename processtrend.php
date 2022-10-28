<?PHP 
$title = 'Process Trend';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][10] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$idInput = '';
if (isset($_GET['id'])) {
	$joinWhere = 'where userfk = 0';
	if (isset($_SESSION['id'])) {
		if ($_SESSION['id'] != 1) {
			$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
		};
	};
	if (isset($_GET['id'])) {
		$idInput = '<input name="id" type="hidden" value="' . $_GET['id'] . '" />';
		$queryTrend = 'select top 1 json, name, publicbool, departmentfk, departmentequipmentfk, userfk, share from ProcessTrend 
		left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
		where id = ' . $_GET['id'];
		$dataTrend = odbc_exec($conn, $queryTrend);
		if (odbc_fetch_row($dataTrend)) {
			if (odbc_result($dataTrend, 6) == @$_SESSION['id'] || odbc_result($dataTrend, 7) || @$_SESSION['id'] == 1 || odbc_result($dataTrend, 3)) {
				$json = json_decode(odbc_result($dataTrend, 1), true);
				ksort($json);
				$title = odbc_result($dataTrend, 2);
				$publicBool = odbc_result($dataTrend, 3);
				$aDE['department'] = odbc_result($dataTrend, 4);
				$aDE['equipment'] = odbc_result($dataTrend, 5);
				/*if (odbc_result($dataTrend, 5)) {
					$queryEquipmentGet = 'select top 1 departmentfk from departmentequipment where id = ' . odbc_result($dataTrend, 5);
					$dataEquipmentGet = odbc_exec($conn, $queryEquipmentGet);
					if (!odbc_fetch_row($dataEquipmentGet)) {
						$_SESSION['sqlMessage'] = 'Department Equipment not found!';
						$_SESSION['uiState'] = 'error';
					};
				} else {
					$_SESSION['sqlMessage'] = 'Department not found!';
					$_SESSION['uiState'] = 'error';
				};*/
			} else {
				$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
			};
		} else {
			$_SESSION['sqlMessage'] = 'Trend not found!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
		};
	};
} elseif (isset($_GET['data']) && isset($_GET['title']) && isset($_GET['department'])) {
	if (isset($_GET['department'])) {
		$aDE['department'] = $_GET['department'];
	} else {
		$aDE['department'] = 0;
	};
	if (isset($_GET['equipment'])) {
		if (!($_GET['equipment'] === 'none')) {
			$aDE['equipment'] = $_GET['equipment'];
			$queryEquipmentGet = 'select top 1 departmentfk from departmentequipment where id = ' . $_GET['equipment'];
			$dataEquipmentGet = odbc_exec($conn, $queryEquipmentGet);
			if (!odbc_fetch_row($dataEquipmentGet)) {
				$_SESSION['sqlMessage'] = 'Department Equipment not found!';
				$_SESSION['uiState'] = 'error';
			};
		} else {
			$aDE['equipment'] = 0;
		};
	} else {
		$aDE['equipment'] = 0;
	};
	$json = $_GET['data'];
	$title = $_GET['title'];
	if (isset($_GET['public'])) {
		$publicBool = 1;
	} else {
		$publicBool = 0;
	};
} else {
	$_SESSION['sqlMessage'] = 'No Trend Selected!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
fSetDates($startDate, $endDate, 1);
$buffer = (strtotime($endDate) - strtotime($startDate)) / 3;
$startDateBuffer = date('Y-m-d H:i:s', strtotime($startDate) - $buffer);
$endDateBuffer = date('Y-m-d H:i:s', strtotime($endDate) + $buffer);
if ( (strtotime($endDate) + $buffer) > strtotime($localTime) ) {
	$endDateBuffer = $localTime;
};
$rConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=runtime;', $dbUsername, $dbPassword);
foreach ($json as $tagnames => $tagdata) {
	$tags[] = $tagnames;
};
$tagSep = '';
$querySep = '';
$tTags = '';
$qTags = '';
foreach ($tags as $value) {
	$tTags .= $tagSep . $value;
	$qTags .= $querySep . 'tag.tagname = \'' . $value . '\'';
	$tagSep = ', ';
	$querySep = ' or ';
};
$diff = round((strtotime($endDate) - strtotime($startDate)) / $res * 1000);
if ($diff < 1000 || $diff > 2146999999) {
	$_SESSION['sqlMessage'] = 'Trend out of range!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryTags = 'select datetime, ' . $tTags . ' from openquery(insql,\'select datetime, ' . $tTags . ' from widehistory where datetime >= "' . $startDateBuffer . '" and datetime <= "' . $endDateBuffer . '" and wwretrievalmode = "cyclic" and wwresolution = ' . $diff . '\')';
$dataTags = odbc_exec($rConn, $queryTags);
$row = 0;
while(odbc_fetch_row($dataTags)) {
	$row++;
	foreach ($tags as $key => $value) {
		$dataValues[$value][] = '[' . strtotime(odbc_result($dataTags, 1)) * 1000 . ',' . odbc_result($dataTags, $key + 2) . ']';
	};
};
$queryTagsDesc = 'select TagName, tag.Description
from tag
join IOServer on IOServer.IOServerKey = Tag.IOServerKey
join Topic on Topic.TopicKey = Tag.TopicKey
join TagType on TagType.TagTypeKey = Tag.TagType
where ' . $qTags;
$dataTagsDesc = odbc_exec($rConn, $queryTagsDesc);
while(odbc_fetch_row($dataTagsDesc)) {
	if (!isset($sTagDesc)) {
		$sTagDesc = odbc_result($dataTagsDesc, 2);
	};
	$tagDesc[odbc_result($dataTagsDesc, 1)] = odbc_result($dataTagsDesc, 2);
};
$sDataValues = '';
$sep = '';
$iAxis = 1;
foreach ($dataValues as $key => $array) {
	if ($json[$key]['type'] == 'step') {
		$step = "true";
	} else {
		$step = "false";
	}
	$sDataValues .= $sep . '{desc:"' . $tagDesc[$key] . '", dp:' . $json[$key]['dp'] . ', type:"' . $json[$key]['type'] . '", invert:"' . $json[$key]['invert'] . '", label:"' . $key . '", lines:{show:true, steps:' . $step . '}, yaxis: ' . $iAxis . ', data:[' . implode(',', $array) . ']}';
	$sep = ', ';
	$iAxis++;
};
$stdOut .= '<h2>Showing ' . $title . ' Process Trend</h2>
<script type="text/javascript">
	var ajaxnotification, datetimestamp, tagEditCell, tagListCell, currentTags, trendCell, choiceContainer, tagDescription, saveBar, legendChoiceContainer, graphTags, fullTags, loading, liveStatus, panStatus, plotData, zeropoint, removedTags, newTags, panLimit, updateLegendTimeout, latestPosition, delay, data, initialOptions, refreshRate = 10, liveTimeout, options;
	function setStartEnd(start, end) {
		recordSwapTarget.getAxes().xaxis.max = end;
		options.xaxes[0].max = end;
		recordSwapTarget.getAxes().xaxis.min = start;
		options.xaxes[0].min = start;
		//redraw?
	};
	function checkResolution(i) {
		if (res != i) {
			if (i > 2048 || i < 16) {
				updateNotifications("Resolution out of range!", "failed", ajaxnotification);
				return false;
			} else {
				if (i > 512 && res <= 512) {
					updateNotifications("Resolution above 512, Brimweb may become unresponsive!", "failed", ajaxnotification);
				};
			};
		};
		return true;
	};
	function setResolution(i) {
		if (checkResolution(i)) {
			if (res != i || $("#resolution").val(i) == "") {
				clearTimeout(liveTimeout);
				$("#resolution").val(i);
				res = i;
			};
			return true;
		};
		return false;
	};
	function setResolutionFull(start, end, i) {
		var intervalCalc = Math.round((end- start) / i);
		if (checkInterval(intervalCalc)) {
			if (setResolution(i)) {
				setInterval(intervalCalc);
				setStartEnd(start, end);
				return true;
			};
		};
		return false;
	};
	function checkInterval(i) {
		if (refreshRate != i) {
			if (i > 2146999999 || i < 1000) {
				updateNotifications("Interval out of range!", "failed", ajaxnotification);
				return false;
			};
		};
		return true;
	};
	function setInterval(i) {
		if (checkInterval(i)) {
			if (refreshRate != i  || $("#interval").val(i) == "") {
				clearTimeout(liveTimeout);
				$("#interval").val(i);
				//console.log("interval changed");
				refreshRate = i;
			};
			return true;
		};
		return false;
	};
	function setIntervalFull(start, end, i) {
		var resolutionCalc = Math.round((end - start) / i);
		console.log("res: " + resolutionCalc);
		if (checkResolution(resolutionCalc)) {
			if (setInterval(i)) {
				setResolution(resolutionCalc);
				setStartEnd(start, end);
				return true;
			};
		};
		return false;
	};
	function buffer(startTimestamp, endTimestamp) {
		var buffer = (endTimestamp - startTimestamp) / 3;
		var start = startTimestamp - buffer;
		var adjustedEnd = endTimestamp + buffer;
		panLimit = getPanLimit();
		if (adjustedEnd > panLimit) {
			adjustedEnd = panLimit;
		};
		//var out = {startBuffer: startTimestamp, start: startTimestamp, endBuffer: endTimestamp, end: endTimestamp};
		var out = {startBuffer: start, start: startTimestamp, endBuffer: adjustedEnd, end: endTimestamp};
		return out; 
	};
	function checkRange(start, end) {
		if (setResolutionFull(start, end, res)) {
			return true;
		} else {
			return false;
		};
	};
	function getPanLimit() {
		panLimitDate = new Date();
		offset = panLimitDate.getTimezoneOffset() * 60 * 1000;
		utctime = Date.now();
		pan = utctime - offset;
		if (typeof options != "undefined") {
			options.xaxes[0].panRange[1] = pan;
		};
		if (typeof initialOptions != "undefined") {
			initialOptions.xaxis.panRange[1] = pan;
		};
		return pan;
	};
	function panRedraw() {
		getPanLimit();
		redrawPlots();
		if (panStatus == 1) {
			setTimeout(panRedraw, 300000);
		}
	};
	function getAjaxData(startTimestamp, endTimestamp, type) {
		var oBuffer = buffer(startTimestamp, endTimestamp);
		mode = false;
		if (typeof(type) != "undefined") {
			if (type == "bestfit") {
				mode = true;
			};
		};
		if (setResolutionFull(oBuffer.start, oBuffer.end, res)) {
			//console.log(["gettingajax", startTimestamp, endTimestamp, refreshRate]);
			$.ajax({
				// the URL for the request
				url: "includes/ajax.historical.runtime.php",
				// the data to send (will be converted to a query string)
				data: { tags: graphTags, interval: refreshRate, startdate: oBuffer.startBuffer, enddate: oBuffer.endBuffer, mode:mode},
				// whether this is a POST or GET request
				type: "GET",
				// the type of data we expect back
				dataType : "json",
				beforeSend: function() {
						loading.remove();
						$("#tagdescription").after(loading);
						updateNotifications("something", "complete", ajaxnotification);
					},
				// code to run if the request succeeds;
				// the response is passed to the function
				success: function( json ) {
						updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
						if (typeof(json.oreturn) != "undefined") {
							for (key in data) {
								data[key].data =  json.oreturn[data[key].label];
							};
							if (typeof(type) == "undefined") {
								recordSwapTarget.setData(data);
								redrawPlots();
							} else {
								if (type == "newtags") {
									options.legend.show = true;
									options.legend.container = choiceContainer;
									initialOptions.legend.show = true;
									initialOptions.legend.container = choiceContainer;
									for (i in removedTags) {
										for (j in data) {
											if (data[j].label == removedTags[i]) {
												data.splice(j, 1);
												initialOptions.yaxes.splice(j, 1);
												break;
											};
										};
									};
									for (i in data) {
										data[i].yaxis = +i + 1;
									};
									for (i in newTags) {
										var step = false;
										if (newTags[i][2][3][1] == "step") {
											step = true;
										};
										data.push( { lines: {show:true, steps:step}, yaxis: data.length + 1, label: newTags[i][0], desc: newTags[i][1], data: json.oreturn[newTags[i][0]] } );
										initialOptions.yaxes.push( { panRange: false, axisLabel: newTags[i][0], show: false } );
									};
									var noShow = true;
									for (i in initialOptions.yaxes) {
										if (initialOptions.yaxes[i].show == true) {
											noShow = false;
											break;
										};
									};
									if (noShow == true) {
										initialOptions.yaxes[0].show = true;
									};
									console.log({del:removedTags, add:newTags, curr:graphTags, full:fullTags});
									//console.log(fullTags);
									for (i in data) {
										for (j in fullTags) {
											if (data[i].label == fullTags[j][0]) {
												data[i].dp = fullTags[j][2][2][1];
												data[i].type = fullTags[j][2][3][1];
												if (fullTags[j][2][3][1] == "step") {
													data[i].lines.steps = true;
												} else {
													data[i].lines.steps = false;
												};
												data[i].invert = fullTags[j][2][4][1];
												if (data[i].invert == "true") {
													initialOptions.yaxes[i].transform = function(v) { return -v;};
													initialOptions.yaxes[i].inverseTransform = function(v) { return -v;};
												} else {
													delete initialOptions.yaxes[i].transform;
													delete initialOptions.yaxes[i].inverseTransform;
												};
												initialOptions.yaxes[i].min = fullTags[j][2][0][1];
												initialOptions.yaxes[i].max = fullTags[j][2][1][1];
												//console.log(fullTags[j]);
												break;
											};
										};
									};
									// can saftely update objects with new data here
									//console.log(data);
									//console.log(initialOptions);
									initPlots();
									fAddCheckbox();
									options.legend.show = false;
									delete options.legend.container;
									initialOptions.legend.show = false;
									delete initialOptions.legend.container;
								} else if (type == "bestfit") {
									recordSwapTarget.setData(data);
									redrawPlots();
								};
							};
						};
					},
				// code to run if the request fails; the raw request and
				// status codes are passed to the function
				error: function( xhr, status, errorThrown ) {
						updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
					},
				// code to run regardless of success or failure
				complete: function( xhr, status ) {
						loading.remove();
					}
			});
		};
	};
	function redrawPlots() {
		//console.log("redrawn");
		recordSwapTarget.setupGrid();
		recordSwapTarget.draw();
		options = recordSwapTarget.getOptions();
		plotData = recordSwapTarget.getData();
	};
	function initPlots() {
		//console.log("initplot");
		if (typeof(recordSwapTarget) != "undefined") {
			initialOptions.xaxis.min = options.xaxes[0].min;
			initialOptions.xaxis.max = options.xaxes[0].max;
		};
		recordSwapTarget = $.plot("#process1", data, initialOptions);
		options = recordSwapTarget.getOptions();
		plotData = recordSwapTarget.getData();
		zeropoint = Math.round(recordSwapTarget.getAxes().xaxis.min);
	};
	function bindLegend() {
		//console.log("bindinglegend");
		$("#processlegend [type=\'checkbox\']").change(function () {
			plotData[this.name].lines.show = this.checked;
			data[this.name].lines.show = this.checked;
			//console.log(data[this.name].lines);
			redrawPlots();
		});
		$("input[type=\'radio\'][name=\'axes\']").change(function() {
			var key;
			for (key in options.yaxes) {
				if (key == this.value) {
					initialOptions.yaxes[key].show = true;
					options.yaxes[key].show = true;
					tagDescription.text(data[key].desc);
				} else {
					initialOptions.yaxes[key].show = false;
					options.yaxes[key].show = false;
				};
			};
			redrawPlots();
		});
	};
	function fAddCheckbox() {
		legendChoiceContainer = $("#processlegend .legendColorBox");
		$.each(plotData, function(key, val) {
			iText = "<td><input type=\'radio\' name=\'axes\' value=\'" + key + "\'";
			if (options.yaxes[key].show) {
				iText += " checked=\'checked\'";
			};
			iText += "></input></td><td><input type=\'checkbox\' name=\'" + key + "\'";
			if (val.lines.show) {
				iText += " checked=\'checked\'";
			};
			iText += "></input></td>";
			legendChoiceContainer.eq(key).before(iText);
			legendChoiceContainer.eq(key).next().after("<td></td>");
		});
		bindLegend();
	};
	$(function() {
		ajaxnotification = $("#ajax-notification");
		datetimestamp = $("#datetimestamp");
		tagEditCell = $(".tageditcell");
		tagListCell = $("#taglistcell");
		currentTags = $("#currenttags");
		trendCell = $("#trendcell");
		choiceContainer = $("#processlegend");
		tagDescription = $("#tagdescription");
		saveBar = $("#savebar");
		settingsBar = $("#bar-settings");
		graphTags = new Array();
		fullTags = new Array();
		loading = $(\'<div id="loading"><img src="images/loading.gif" alt="Loading" title="Loading" /></div>\');
		liveStatus = 0;
		panStatus = 0;
		//hmmmm
		panLimit = getPanLimit();
		updateLegendTimeout = null;
		latestPosition = null;
		delay = null;
		data = [' . $sDataValues . '];
		initialOptions = {
			colors: trendcolors,
			series: {
				lines: {
					show: true,
					lineWidth:1
				}
			},
			crosshair: {
				color: trendcrosshair,
				mode: "x"
			},
			/*pan: {
				interactive: true,
				frameRate: 20
			},
			selection: {
				color: trendselection,
				mode: "x"
			},*/
			grid: {
				hoverable: true,
				clickable: true,
				autoHighlight: false,
				markings: fMarkings
			},
			legend: {
				container:choiceContainer
			},
			xaxis: {
				mode: "time",
				timeformat: "%d/%m/%y<br />%H:%M:%S",
				ticks: 5,
				tickLength: 0,
				axisLabel: "Date/Time",
				panRange: [0, panLimit],
				min: ' . strtotime($startDate) * 1000 . ',
				max: ' . strtotime($endDate) * 1000 . '
			},
			yaxes: [';
				$axesSep = '';
				foreach ($tags as $key => $value) {
					if ($json[$value]['invert'] == "true") {
						$stdOut .= $axesSep . '{transform: function(v) { return -v;}, inverseTransform: function(v) { return -v;}, panRange:false, min:' . $json[$value]['min'] . ', max:' . $json[$value]['max'] . ', axisLabel: "' . $value . '", ';
					} else {
						$stdOut .= $axesSep . '{panRange:false, min:' . $json[$value]['min'] . ', max:' . $json[$value]['max'] . ', axisLabel: "' . $value . '", ';
					};
					if ($key == 0) {
						$stdOut .= 'show:true}';
					} else {
						$stdOut .= 'show:false}';
					};
					$axesSep = ', ';
				};
				$stdOut .= '
			]
		};
		$("#process1").bind("plotselected", function (event, ranges) {
			//console.log("mainselected");
			if (checkRange(ranges.xaxis.from, ranges.xaxis.to)) {
				options.xaxes[0].min = ranges.xaxis.from;
				options.xaxes[0].max = ranges.xaxis.to;
				redrawPlots();
				getAjaxData(ranges.xaxis.from, ranges.xaxis.to);
			};
			recordSwapTarget.clearSelection();
		}).bind("plothover",  function (event, pos, item) {
			//console.log("hovered");
			latestPosition = pos;
			if (!updateLegendTimeout) {
				updateLegendTimeout = setTimeout(updateLegend, 50);
			}
		}).bind("plotpan", function(event, item, pos) {
			//console.log(event, pos, item);
			//console.log("panned!");
			if (delay) {
				clearTimeout(delay);
			};
			delay = setTimeout(function() {
				getAjaxData(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max);
				delay = null;
			}, 500);
		}).bind("mouseup", function(event) {
			if (shiftDown && event.which == 1) {
			//if (event.which == 3) {
				//console.log("rightclick");
				zoomOut();
			};
		}).bind("contextmenu", function() {
			return false;
		});
		function zoomOut() {
			if (liveStatus == 0) {
				var diff = (recordSwapTarget.getAxes().xaxis.max - recordSwapTarget.getAxes().xaxis.min) / 3
				var plotMin = recordSwapTarget.getAxes().xaxis.min - diff;
				var plotMax = recordSwapTarget.getAxes().xaxis.max + diff;
				panLimit = getPanLimit();
				if (plotMax > panLimit) {
					plotMax = panLimit;
				}
				if (checkRange()) {
					options.xaxes[0].min = plotMin;
					options.xaxes[0].max = plotMax;
					redrawPlots();
					getAjaxData(plotMin, plotMax);
				};
			};
		};
		function updateLegend() {
			var legends = $("#processlegend .legendLabel");
			updateLegendTimeout = null;
			var pos = latestPosition;
			x = new Date(pos.x);
			datetimestamp.html(x.toUTCString());
			var axes = recordSwapTarget.getAxes();
			if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
				pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
				return;
			}
			var i, j, dataset = recordSwapTarget.getData();
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
				legends.eq(i).next().text(y.toFixed(series.dp));
			}
		};
		$("#fullscreen").click(function () {
			if ($("#processtrentcont").hasClass("flotmax")) {
				$("#processtrentcont").removeClass("flotmax");
			} else {
				$("#processtrentcont").addClass("flotmax");
			};
			return false;
		});	
		$("#zoom").click(function () {
			//console.log("zoomed");
			liveStatus = 0;
			panStatus = 0;
			delete initialOptions.pan;
			initialOptions.selection = {color: trendselection, mode: "x"};
			initPlots();
			return false;
		});
		$("#zoomout").click(function () {
			zoomOut();
			return false;
		});
		$("#pan").click(function () {
			//console.log("panned");
			liveStatus = 0;
			panStatus = 1;
			delete initialOptions.selection;
			initialOptions.pan = {interactive: true,frameRate: 20};
			initPlots();
			panRedraw();
			return false;
		});	
		$("#filtersubmit").bind("click", function () {
			var tagSearch = $(this).prev().val();
			var excludeTags = new Array();
			$("#currenttags li").each(function() {
				excludeTags.push($(this).find("b").text());	
			});
			//console.log(excludeTags);
			$.ajax({
				// the URL for the request
				url: "includes/ajax.taglist.php",
				// the data to send (will be converted to a query string)
				data: { filter: tagSearch, exclude: excludeTags },
				// whether this is a POST or GET request
				type: "GET",
				// the type of data we expect back
				dataType : "json",
				beforeSend: function() {
						$("#filteredtags").html(loading);
						updateNotifications("something", "complete", ajaxnotification);
					},
				// code to run if the request succeeds;
				// the response is passed to the function
				success: function( json ) {
						updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
						$("#filteredtags").html(json.oreturn);
						$("#filteredtags a").click(function () {
							var jthis = $(this).parent();
							var tagname = jthis.find(".tagname").text();
							var movedElem = $("<li data-desc=\'" + jthis.data(\'desc\') + "\'><b>" + tagname + "</b><a href=\'#\'><span class=\'icon-trash\'></span></a><div>Min: <input name=\'data[" + tagname + "][min]\' type=\'text\' /><span class=\'required\'> * </span> Max: <input name=\'data[" + tagname + "][max]\' type=\'text\' /><span class=\'required\'> * </span> DP: <select name=\'data[" + tagname + "][dp]\'><option value=\'0\'>0</option><option value=\'1\'>1</option><option value=\'2\'>2</option><option value=\'3\'>3</option><option value=\'4\'>4</option></select> Type: <select name=\'data[" + tagname + "][type]\'><option value=\'smooth\'>Smooth</option><option value=\'step\'>Step</option></select> Invert: <select name=\'data[" + tagname + "][invert]\'><option value=\'false\'>False</option><option value=\'true\'>True</option></select></div></li>");
							movedElem.appendTo(currentTags);
							movedElem.children("a").click(function() {
								$(this).parent().remove();
							});
							jthis.remove();
							return false;
						});
					},
				// code to run if the request fails; the raw request and
				// status codes are passed to the function
				error: function( xhr, status, errorThrown ) {
						updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
					},
				// code to run regardless of success or failure
				complete: function( xhr, status ) {
						loading.remove();
					}
			});
			return false;
		});
		function liveUpdate() {
			if (liveStatus == 1) {
				$.ajax({
					// the URL for the request
					url: "includes/ajax.live.runtime.php",
					// the data to send (will be converted to a query string)
					data: { tags: graphTags },
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							loading.remove();
							$("#tagdescription").after(loading);
							updateNotifications("something", "complete", ajaxnotification);
						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
							for (i in data) {
								data[i].data.push(json.oreturn[data[i].label]);
								data[i].data.splice(0,1);
								var currentLong = json.oreturn[data[i].label][0];
							};
							var currentDiff = currentLong - recordSwapTarget.getAxes().xaxis.max
							if (setResolutionFull(recordSwapTarget.getAxes().xaxis.min + currentDiff, recordSwapTarget.getAxes().xaxis.max + currentDiff, res)) {
								recordSwapTarget.setData(data);
								redrawPlots();
								liveTimeout = setTimeout(liveUpdate, refreshRate);
							};
						},
					// code to run if the request fails; the raw request and
					// status codes are passed to the function
					error: function( xhr, status, errorThrown ) {
							updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
						},
					// code to run regardless of success or failure
					complete: function( xhr, status ) {
							loading.remove();
						}
				});
			};
		};
		$("#live").click(function () {
			panLimit = getPanLimit();
			var oBuffer = buffer(panLimit - (recordSwapTarget.getAxes().xaxis.max - recordSwapTarget.getAxes().xaxis.min), panLimit);
			var startDelay = new Date();
			//console.log(liveStartAxes);
			liveStatus = 1;
			panStatus = 0;
			delete initialOptions.pan;
			delete initialOptions.selection;
			redrawPlots();
			if (setResolutionFull(oBuffer.start, oBuffer.end, res)) {
				initPlots();
				$.ajax({
					// the URL for the request
					url: "includes/ajax.historical.runtime.php",
					// the data to send (will be converted to a query string)
					data: { tags: graphTags, interval: refreshRate, startdate: oBuffer.startBuffer, enddate: oBuffer.endBuffer},
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							loading.remove();
							$("#tagdescription").after(loading);
							updateNotifications("something", "complete", ajaxnotification);
						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
							for (key in data) {
								data[key].data =  json.oreturn[data[key].label];
							};
							recordSwapTarget.setData(data);
							/*var dataLength = data[0].data.length;
							options.xaxes[0].min = data[0].data[Math.round(dataLength / 3) - 1][0];
							options.xaxes[0].max = data[0].data[dataLength - 1][0];*/
							redrawPlots();
							/*var endDelay = new Date();
							var delay = endDelay - startDelay;
							if (refreshRate - delay >= 0) {
								liveTimeout = setTimeout(liveUpdate, refreshRate - delay);
							} else {
								liveTimeout = setTimeout(liveUpdate, 0);
							};*/
							liveTimeout = setTimeout(liveUpdate, refreshRate);
						},
					// code to run if the request fails; the raw request and
					// status codes are passed to the function
					error: function( xhr, status, errorThrown ) {
							updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification);
						},
					// code to run regardless of success or failure
					complete: function( xhr, status ) {
							loading.remove();
						}
				});
				return false;
			};
		});
		$("#mode").click(function() {
			if (recordSwapTarget.getAxes().xaxis.max - recordSwapTarget.getAxes().xaxis.min <= 172800000) {
				getAjaxData(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max, "bestfit");
			} else {
				updateNotifications("Date/Time Range greater than 48 hours!", "failed", ajaxnotification);
			};
		});
		$("#taglist").click(function () {
			if (tagListCell.css("display") == "none") {
				tagListCell.css("display", "table-cell");
				trendCell.css("display", "table-cell");
				tagEditCell.css("display", "none");
			} else {
				tagEditCell.find("ul").html("");
				var typeList = ["smooth", "step"];
				var invertList = ["false", "true"];
				for (i in data) {
					var tempObj = {int:i, label:data[i].label, dp:data[i].dp, type:data[i].type, invert:data[i].invert, optionlabel:initialOptions.yaxes[i].axisLabel, min:initialOptions.yaxes[i].min, max:initialOptions.yaxes[i].max};
					var addedTagHtml = "<li><b>" + tempObj.label + "</b><a href=\'#\'><span class=\'icon-trash\'></span></a><div>Min: <input value=\'" + tempObj.min + "\'  name=\'data[" + tempObj.label + "][min]\' type=\'text\' /><span class=\'required\'> * </span> Max: <input value=\'" + tempObj.max + "\' name=\'data[" + tempObj.label + "][max]\' type=\'text\' /><span class=\'required\'> * </span> DP: <select name=\'data[" + tempObj.label + "][dp]\'>";
					for (var j = 0; j < 5; j++) {
						addedTagHtml += "<option ";
						if (j == tempObj.dp) {
							addedTagHtml += " selected ";
						};
						addedTagHtml += "value=\'" + j + "\'>" + j + "</option>";
					};
					addedTagHtml += "</select> Type: <select name=\'data[" + tempObj.label + "][type]\'>";
					for (var k = 0; k < typeList.length; k++) {
						addedTagHtml += "<option ";
						if (tempObj.type == typeList[k]) {
							addedTagHtml += "selected ";
						};
						addedTagHtml += "value=\'" + typeList[k] + "\'>" + toUpperCaseFirst(typeList[k]) + "</option>";
					};
					addedTagHtml += "</select> Invert: <select name=\'data[" + tempObj.label + "][invert]\'>";
					for (var k = 0; k < invertList.length; k++) {
						addedTagHtml += "<option ";
						if (tempObj.invert == invertList[k]) {
							addedTagHtml += "selected ";
						};
						addedTagHtml += "value=\'" + invertList[k] + "\'>" + toUpperCaseFirst(invertList[k]) + "</option>";
					};
					addedTagHtml += "</select></div></li>";
					var addedTag = $(addedTagHtml);
					addedTag.appendTo(currentTags);
					addedTag.children("a").click(function() {
						$(this).parent().remove();
						return false;
					});
				};				
				$("#processlegend .legendLabel").each(function() {
					var addedTag = $("<li>" + this.innerHTML + "<a href=\'#\'><span class=\'icon-trash\'></span></a></li>")
				});
				tagListCell.css("display", "none");
				trendCell.css("display", "none");
				tagEditCell.css("display", "table-cell");
			};
			return false;
		});
		$("#apply").bind("click", function () {
			var bValid = true;
			$("#currenttags input").each(function() {
				bValid = bValid && checkNumber($(this), "Trend preferences must be Numeric." );
			});
			if (bValid == false) {
				return false;
			};
			var editList = new Array();
			newTags = new Array();
			removedTags = new Array();
			var currTags = new Array();
			fullTags = new Array();
			$("#currenttags li").each(function() {
				var jthis = $(this);
				var type;
				var userData = new Array();
				jthis.find("input, select").each(function(index) {
					userData.push([index, this.value]);
				});
						console.log(jthis);
				editList.push([jthis.children("b").text(), jthis.data(\'desc\'), userData, type]);	
			});
			for (var i = 0; i < editList.length; i++) {
				var bNewTags = false;
				for (var j = 0; j < graphTags.length; j++) {
					if (editList[i][0] == graphTags[j]) {
						bNewTags = true;
					};
				};
				if (bNewTags === false) {
					newTags.push(editList[i]);
					graphTags.push(editList[i][0]);
				};
			};
			for (var i = 0; i < graphTags.length; i++) {
				var bRemovedTags = false;
				for (var j = 0; j < editList.length; j++) {
					if (graphTags[i] == editList[j][0]) {
						bRemovedTags = true;
						fullTags.push(editList[j]);
						currTags.push(graphTags[i]);
						break;
					};
				};
				if (bRemovedTags === false) {
					//console.log("remove");
					removedTags.push(graphTags[i]);
					//graphTags.splice(i, 1);
				};
			};
			graphTags = currTags;
			//console.log({removed:removedTags, added:newTags, full:graphTags});
			tagListCell.css("display", "table-cell");
			trendCell.css("display", "table-cell");
			tagEditCell.css("display", "none");
			$("#taglist").toggleClass("active");
			getAjaxData(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max, "newtags" );
			return false;
		});
		//options.yaxes.push({axisLabel:"tag", show:false});
		initPlots();
		var oBuffer = buffer(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max);	
		setResolutionFull(oBuffer.start, oBuffer.end, res);
		$(".legendLabel").each(function() {
			graphTags.push($(this).text());	
		});
		fAddCheckbox();
		options.legend.show = false;
		delete options.legend.container;
		initialOptions.legend.show = false;
		delete initialOptions.legend.container;';
		if (isset($_SESSION['id'])) {
			$saveBar = '<form action="includes/changeprocesstrend.php" method="post" id="savebar"><input type="submit" id="commit" value="Save!" />' . $idInput . '<label for="title">Title: </label><input value="' . $title . '" type="text" name="title" id="title" /><span class="required"> * </span><label for="department">Department: </label><select name="department" id="department"><option value="none">None</option>';
			$queryDepartment = 'select department.id, department.name, departmentequipment.id, departmentequipment.name 
			from department
			left join DepartmentEquipment on departmentfk = department.id ';
			if (@$_SESSION['id'] != 1 && isset($_SESSION['id'])) {
				$queryDepartment .= ' where ' . fOrThemReturn(@$_SESSION['permissions']['department'], 100, 'department.id');
			};  
			$queryDepartment .= ' order by department.name asc, departmentequipment.name asc';
			$dataDepartment = odbc_exec($conn, $queryDepartment);
			while(odbc_fetch_row($dataDepartment)) {
				$departmentLookup[odbc_result($dataDepartment, 1)] = odbc_result($dataDepartment, 2);
				$equipmentLookup[odbc_result($dataDepartment, 3)] = odbc_result($dataDepartment, 4);
				$departmentEquipment[odbc_result($dataDepartment, 1)][] = odbc_result($dataDepartment, 3);
			};
			$equipmentOption[0] = '<option value="none">None</option>';
			foreach ($departmentEquipment as $departmentKey => $equipmentArray) {
				$equipmentOption[$departmentKey] = '<option value="none">None</option>';
				foreach($equipmentArray as $equipmentKey) {
					if ($equipmentKey) {
						$selected = '';
						if (isset($aDE['equipment'])) {
							if ($aDE['department'] == $departmentKey && $aDE['equipment'] == $equipmentKey) {
								$selected = 'selected';
							};
						};
						$equipmentOption[$departmentKey] .= '<option ' . $selected . ' value="' . $equipmentKey . '">' . $equipmentLookup[$equipmentKey] . '</option>';
					};
				};
				$saveBar .= '<option ';
				if ($aDE['department'] == $departmentKey) {
					$saveBar .= 'selected ';
				};
				$saveBar .= 'value="' . $departmentKey . '">' . $departmentLookup[$departmentKey] . "</option>";
			};
			$stdOut .= 'var equipmentOption = ' . json_encode($equipmentOption) . ';
			$("#department").change(function() {
				$("#equipment").html(equipmentOption[this.options[this.selectedIndex].value]);
			});';
			$saveBar .= '</select><span class="required"> * </span> <label for="equipment">Equipment: </label><select name="equipment" id="equipment">' . $equipmentOption[$aDE['department']] . '</select>
			<label for="public">Public:</label><input id="public" type="checkbox" ';
			if ($publicBool == 1) {
				$saveBar .= 'checked';
			};
			$saveBar .= ' value="true" name="public"/></form>';
			$stdOut .= '$("#save").bind("click", function() {
			if (saveBar.css("display") == "none") {
				saveBar.css("display", "block");
			} else {
				saveBar.css("display", "none");
			};
			return false;';
		} else {
			$saveBar = '';
			$stdOut .= '$("#save").bind("click", function() {
			alert("You must be logged in to perform this action!");
			return false;';
		};
		$stdOut .= '});
		$("#settings").bind("click", function() {
			if (settingsBar.css("display") == "none") {
				settingsBar.css("display", "block");
			} else {
				settingsBar.css("display", "none");
			};
			return false;
		});
		$("#apply-interval").bind("click", function() {
			var oBuffer = buffer(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max);
			if (setIntervalFull(oBuffer.start, oBuffer.end, $("#interval").val())) {
				getAjaxData(oBuffer.start, oBuffer.end);
			};
			return false;
		});
		$("#apply-resolution").bind("click", function() {
			var oBuffer = buffer(recordSwapTarget.getAxes().xaxis.min, recordSwapTarget.getAxes().xaxis.max);
			if (setResolutionFull(oBuffer.start, oBuffer.end, $("#resolution").val())) {
				getAjaxData(oBuffer.start, oBuffer.end);
			};
			return false;
		});
		$("#open").bind("click", function() {
			window.location.href = "processtrendsplash.php";
			return false;
		});
		$("#new").bind("click", function() {
			window.location.href = "formprocesstrend.php";
			return false;
		});
		$("#commit").bind("click", function() {
			$(".delme").remove();
			var selectDepartment = $("#department");
			var selectEquipment = $("#equipment");
			$("input, #department, #equipment").removeClass( "ui-state-error" );
			var bValid = true;
			var tagFound = false;
			console.log(data);
			for (i in data) {
				tagFound = true;
				//console.log(data[i].type);
				saveBar.append("<input class=\'delme\' type=\'hidden\' name=\'data[" + data[i].label + "][min]\' value=\'" + initialOptions.yaxes[i].min + "\' /><input class=\'delme\' type=\'hidden\' name=\'data[" + data[i].label + "][max]\' value=\'" + initialOptions.yaxes[i].max + "\' /><input class=\'delme\' type=\'hidden\' name=\'data[" + data[i].label + "][dp]\' value=\'" + data[i].dp + "\' /><input class=\'delme\' type=\'hidden\' name=\'data[" + data[i].label + "][type]\' value=\'" + data[i].type + "\' /><input class=\'delme\' type=\'hidden\' name=\'data[" + data[i].label + "][invert]\' value=\'" + data[i].invert + "\' />");
			};
			if (tagFound == false) {
				bValid = false;
				updateTips("No Tagnames found!");
				return false;
			};
			bValid = bValid && checkOptionSelected( $("#department"), "Department must be set.");
			bValid = bValid && checkLength( $("#title"), "Title", 1, 60 );
			if (bValid == false) {
				return false;
			};
			$("#save").toggleClass("active");
		});
		$(".trend-toggle").click(function() {
			$(this).toggleClass("active");
		});
		$(".trend-toggle-group").find("a").click(function() {
			$(".trend-toggle-group").find("a").removeClass("active");
			$(this).toggleClass("active");
		});
	});
</script>
<div id="processtrentcont">
<div class="sceditor-toolbar">
	<div class="sceditor-group">
		<a id="new" class="trend-button trend-button-new trend-toggle">
			<div></div>
		</a>
		<a  id="taglist" class="trend-button trend-button-edit trend-toggle">
			<div></div>
		</a>
		<a id="open" class="trend-button trend-button-open trend-toggle">
			<div></div>
		</a>
		<a id="save" class="trend-button trend-button-save trend-toggle">
			<div></div>
		</a>
	</div>
	<div class="sceditor-group trend-toggle-group">
		<a id="pan" class="trend-button trend-button-pan">
			<div></div>
		</a>
		<a id="zoom" class="trend-button trend-button-zoom">
			<div></div>
		</a>
		<a id="live" class="trend-button trend-button-live">
			<div></div>
		</a>
		<a id="mode" class="trend-button trend-button-mode">
			<div></div>
		</a>
	</div>
	<div class="sceditor-group">
		<a id="zoomout" class="trend-button trend-button-zoom-out">
			<div></div>
		</a>
	</div>
	<div class="sceditor-group">
		<a id="settings" class="trend-button trend-button-settings trend-toggle">
			<div></div>
		</a>
	</div>
	<div class="sceditor-group">
		<a id="fullscreen" class="trend-button trend-button-fullscreen trend-toggle">
			<div></div>
		</a>
	</div>
</div>' . $saveBar . '
<div id="bar-settings">
	<form>
		<label for="interval">Interval (ms): </label>
		<input id="interval" type="text" name="interval" />
		<input type="submit" id="apply-interval" value="Apply!" />
	</form>
	<form>
		<label for="resolution">Resolution: </label>
		<input id="resolution" type="text" name="resolution" />
		<input type="submit" id="apply-resolution" value="Apply!" />
	</form>
</div>
<table id="processtrentcont2"><tr>
<td class="tageditcell">
<h3>Available Tags</h3>
<ul id="filteredtags">
</ul>
<form action="includes/ajax.taglist.php" method="get">
<input type="text" name="filter" />
<input type="submit" value="Filter!" id="filtersubmit" />
</form>
</td>
<td class="tageditcell">
<h3>Current Tags</h3>
<ul id="currenttags">
</ul>
<form action="includes/ajax.placeholder.php" method="get">
<input type="submit" id="apply" value="Apply!" />
</form>
</td>
<td id="trendcell">
<div class="printsafe processtrend" id="process1" ></div>
<div id="datetimestampcontainer"><div id="datetimestamp"></div></div>
</td>
<td class="processcells" id="taglistcell">
<h3>Tagnames</h3>
<div id="processlegend"></div>
<div id="tagdescription" >' . $sTagDesc . '</div>
</td></tr></table></div>';

$joinWhere = 'where userfk = 0';
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] != 1) {
		$joinWhere = 'where userfk = ' . $_SESSION['id'] . ' or ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
	};
};
$queryTrends = 'select temp1.id, trendname, publicbool, username, share, realdepartmentfk, departmentequipmentfk, departmentequipmentname, department.name
from (select processtrend.userfk, processtrend.ID, processtrend.name as trendname, publicbool, users.name as username, share, case 
		when processtrend.DepartmentEquipmentFK IS NOT NULL 
			then departmentequipment.DepartmentFK 
			else processtrend.DepartmentFK end as realdepartmentfk,
	DepartmentEquipmentFK, DepartmentEquipment.Name as departmentequipmentname
	from ProcessTrend 
	left join (select distinct processtrendfk, 1 as share from ProcessTrendShare ' . $joinWhere . ') as shares on ProcessTrend.id = shares.ProcessTrendFK
	left join departmentequipment on processtrend.departmentequipmentfk = departmentequipment.ID
	join users on users.id = processtrend.userfk) as temp1
left join department on realdepartmentfk = department.id';
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] > 1) {
		$queryTrends .= ' where (publicbool = 1 or temp1.userfk = ' . $_SESSION['id'] . ') and realdepartmentfk = ' . $aDE['department'];
	} else {
		$queryTrends .= ' where realdepartmentfk = ' . $aDE['department'];
	};
} else {
	$queryTrends .= ' where realdepartmentfk = ' . $aDE['department'];
};
$queryTrends .= ' order by department.name asc, departmentequipmentfk asc, trendname asc';
$aTrends = ['My Trends' => array(), 'Shared Trends' => array(), 'Public Trends' => array()];
$dataTrends = odbc_exec($conn, $queryTrends);
while (odbc_fetch_row($dataTrends)) {
	$lookup['e' . odbc_result($dataTrends, 7)] = odbc_result($dataTrends, 8);
	$row = [odbc_result($dataTrends, 1), odbc_result($dataTrends, 2), odbc_result($dataTrends, 4), odbc_result($dataTrends, 6), odbc_result($dataTrends, 7)];
	if (isset($_SESSION['id'])) {
		if (fCanSee(odbc_result($dataTrends, 4) == $_SESSION['user'])) {
			$aTrends['My Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
		};
	};
	if (odbc_result($dataTrends, 3) == 1) {
		$aTrends['Public Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
	};
	if (odbc_result($dataTrends, 5) == 1) {
		$aTrends['Shared Trends']['e' . odbc_result($dataTrends, 7)][] = $row;
	};
};
$stdOut .= fRecordSwap(['rename' => 'Trends', 'exclude' => ['trend']]) . '<div class="records" id="ajax-trend"><script type="text/javascript">
	$(function() {
		$(".listheader").click(function() {
			var jThis = $(this);
			var listItem = jThis.next("ul");
			if (listItem.css("display") == "none") {
				listItem.css("display", "block");
				jThis.children(".icon-caret-right").css("display", "none");
				jThis.children(".icon-caret-down").css("display", "inline");
			} else {
				listItem.css("display", "none");
				jThis.children(".icon-caret-right").css("display", "inline");
				jThis.children(".icon-caret-down").css("display", "none");
			};
			return false;
		});
	});
</script>';
$rowId = 1;
foreach ($aTrends as $key => $array) {
	if (!empty($array)) {
		if ($rowId % 2 == 0) {
			$rowType = 'oddRow';
		} else {
			$rowType = 'evenRow';
		};
		$rowId++;
		$stdOut .= '<div class="inlineclass ' . $rowType . '"><h3>' . $key . '</h3><ul>';
			foreach ($array as $equipKey => $equipArray) {
				if (isset($lookup[$equipKey])) {
					$stdOut .= '<li><a class="listheader" href="#">' . $lookup[$equipKey] . ' <span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a><ul>';
					foreach ($equipArray as $trend) {
						$stdOut .= '<li><a href="processtrend.php?department=' . $trend[3] . '&id=' . $trend[0] . '">' . $trend[1] . '</a><div class="hinttext">Author:' . $trend[2] . '</div></li>';
					};
					$stdOut .= '</ul></li>';
				} else {
					foreach ($equipArray as $trend) {
						$stdOut .= '<li><a href="processtrend.php?department=' . $trend[3] . '&id=' . $trend[0] . '">' . $trend[1] . '</a><div class="hinttext">Author:' . $trend[2] . '</div></li>';
					};
				};
			};
		$stdOut .= '</ul></div>';
	};
};
$stdOut .= '</div><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('processtrend', '[2002, 03, 23]');
$hookReplace['help'] = $helptext['standardcal'] . $helptext['linehoverany'] . $helptext['linemarkings'] . $helptext['default24h'] . '<a href="#">Trend Panning</a><div>To Pan the Trend the binoculars tool must be selected. Clicking and Dragging on the Trend will scroll the Trend across the X axis and new data will be polled.</div><a href="#">Zooming In</a><div>To Zoom the Trend in, the Zoom tool must be selected. Clicking and Dragging on the Trend will mark an area showing the selected area to be Zoomed, releasing the mouse will complete the action and new data will be polled.</div><a href="#">Zooming Out</a><div>Holding shift while left clicking on the trend, or by clicking the Zoom Minus tool in the toolbar, will zoom out the trend. New data will be polled.</div><a href="#">Live View</a><div>Clicking the clock in the toolbar will turn on the Live View. The Live View will turn off all trend controls. The Live View shows uses the range shown on the trend view, the refresh rate is dynamic based on the range.</div><a href="#">Trend Settings</a><div>There are currently two options; Interval (in milliseconds) and Resolution. Both options are intrinsically linked and you cannot change one without changing the other one. Interval (1000 - 2146999999) refers to the duration between each datapoint while Resolution (16 - 2048. Less than 512 recommended) refers to the number of datapoints on the trend.</div><a href="#">On-The-Fly Trend Editing</a><div>Clicking the Edit button (second from the left) will hide the Tagnames and give you aditional controls. These controls give you the ability to filter tagnames/description and add them to the trend or the ability to remove existing pens as well as editing the pens properties. Once you have finished editing, click apply and new data will be polled. Edits you make this way will not save the trend.</div>' . $helptext['recordswap']  . $helptext['dynrecordswap'] . $helptext['autohighlight'] . $helptext['tablesorter'] . $helptext['recordsetcolumns'];
odbc_close($rConn);
require_once 'includes/footer.php'; ?>