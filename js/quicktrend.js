$(function() {
			var trendHint = $("#trendhint");
			var sideBar = $("#sidebar");
			var trendHolder = $("#quicktrendholder");
			var loading = $("<div id=\'loading\'><img src=\'images/loading.gif\' alt=\'Loading\' title=\'Loading\' /></div>");
			var choiceContainer = $("#processlegend");
			var tagDescription = $("#tagdescription");
			var ajaxnotification = $("#ajax-notification");
			var initialOptions = {
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
				selection: {
					color: trendselection,
					mode: "x"
				},
				grid: {
					hoverable: true,
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
					axisLabel: "Date/Time"
				}
			};
			var updateLegendTimeout = null;
			var datetimestamp = $("#datetimestamp");
			var options;
			var plotData;
			var data;
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
			function setupTrend() {
				options = recordSwapTarget.getOptions();
				plotData = recordSwapTarget.getData();
				options.legend.show = false;
				delete options.legend.container;
				initialOptions.legend.show = false;
				delete initialOptions.legend.container;
				fAddCheckbox();
				$("#quicktrend").bind("plothover",  function (event, pos, item) {
					latestPosition = pos;
					if (!updateLegendTimeout) {
						updateLegendTimeout = setTimeout(updateLegend, 50);
					}
				});				
			}
			function bindLegend() {
				//console.log("bindinglegend");
				$("#processlegend [type=\'checkbox\']").change(function () {
					plotData[this.name].lines.show = this.checked;
					data[this.name].lines.show = this.checked;
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
			function redrawPlots() {
				//console.log("redrawn");
				recordSwapTarget.setupGrid();
				recordSwapTarget.draw();
				options = recordSwapTarget.getOptions();
				plotData = recordSwapTarget.getData();
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
			$(".trendlink").click(function() {
				var clickedData = $(this).data();
				var interval = Math.round((clickedData.end - clickedData.start) / res);
				$.ajax({
					// the URL for the request
					url: "includes/ajax.historical.runtime.php",
					// the data to send (will be converted to a query string)
					data: { trend: clickedData.id, interval: interval, startdate: clickedData.start, enddate: clickedData.end},
					// whether this is a POST or GET request
					type: "GET",
					// the type of data we expect back
					dataType : "json",
					beforeSend: function() {
							if (trendHint.length > 0) {
								trendHint.remove();
								trendHolder.css("display", "block");
							};
							loading.remove();
							sideBar.append(loading);
							updateNotifications("something", "complete", ajaxnotification);
						},
					// code to run if the request succeeds;
					// the response is passed to the function
					success: function( json ) {
							updateNotifications("Action failed, " + json.status, json.status, ajaxnotification);
							data = Array();
							initialOptions.yaxes = Array();
							var i = 1;
							var show = true;
							var ordered = new Array();
							for (aKey in json.oreturn) {
								ordered.push(aKey);
							};
							ordered.sort(function(a,b) {
								var aLo = a.toLowerCase();
								var bLo = b.toLowerCase();
								if (aLo < bLo) return -1;
								if (aLo > bLo) return 1;
								return 0;
							});
							for (var j = 0; j < ordered.length; j++) {
								var key = ordered[j];
								if (i == 1) {
									tagDescription.text(json.trendprefs[key].desc);
								};
								if (json.trendprefs[key].invert) {
									initialOptions.yaxes.push({transform: function(v) { return -v;}, inverseTransform: function(v) { return -v;}, min:json.trendprefs[key].min, max:json.trendprefs[key].max, axisLabel:key, show:show});
								} else {
									initialOptions.yaxes.push({min:json.trendprefs[key].min, max:json.trendprefs[key].max, axisLabel:key, show:show});
								};
								var step = false;
								if (json.trendprefs[key].type == "step") {
									step = true;
								};
								data.push({desc: json.trendprefs[key].desc, label: key, data: json.oreturn[key], dp:json.trendprefs[key].dp, type:json.trendprefs[key].type, yaxis:i, lines:{show:true, steps:step}});
								i++;
								show = false;
							};
							if (typeof(options) != 'undefined') {
								options.legend.show = true;
								options.legend.container = choiceContainer;
								initialOptions.legend.show = true;
								initialOptions.legend.container = choiceContainer;
							};
							recordSwapTarget = $.plot("#quicktrend", data, initialOptions);
							setupTrend();
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
			});
		});