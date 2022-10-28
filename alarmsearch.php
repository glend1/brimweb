<?PHP 
$title = 'Alarm Search';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][12] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$aConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=WWALMDB;', $dbUsername, $dbPassword);
fSetDates($startDate, $endDate, 7);
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('alarmsplash', '[2013, 06, 12]');
$stdOut .= '<h2>Alarms from ' . $startDate . ' to ' . $endDate . '</h2><div id="scatter"></div><h3 class="clear">Search <a data-text="Table Headers" data-id="alarm" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3>
<div class="tablesorter-row-hider" id="alarm-table-rows"></div>
<table class="records" id="alarm-sort"><thead><tr><th class="filter-false">Start Date/Time</th><th>Priority</th><th>Alarm Type</th><th>Group Name</th><th>Tag Name</th><th>Department</th><th>Equipment</th><th>State</th></tr></thead><tbody></tbody>' . fTableFooter(['id' => 'alarm-sort', 'cols' => 8]) . '</table>
<script type="text/javascript"> 
$(function() {
	$("#alarm-sort").tablesorter({
		widthFixed:true,
		headers: {0 : { sorter: false },
				1 : { sorter: false, columnSelector: false },
				2 : { sorter: false, columnSelector: false },
				3 : { sorter: false },
				4 : { sorter: false },
				5 : { sorter: false },
				6 : { sorter: false },
				7 : { sorter: false, columnSelector: false }},
		widgets: ["zebra", "filter", "columnSelector", "pager"],
		widgetOptions : {
			//filter_columnFilters:false,
			columnSelector_container: $(\'#alarm-table-rows\'),
			columnSelector_mediaquery: false,
			columnSelector_saveColumns: false,
			columnSelector_layout: \'<div class="columnselectoritem"><input type="checkbox" id="label-sort-{name}"><label for="label-sort-{name}">{name}</label></div>\',
			zebra : [ "oddRow", "evenRow" ],
			pager_selectors: {
				container: $("#alarm-sort-pager"),
			},
			//pager_fixedHeight: true,
			//storageKey:"alarms-sort-table",
			pager_size:100,
			pager_savePages:false,
			pager_output: "{page:input}/{filteredPages} (100)",
			pager_ajaxUrl : "includes/ajax.alarmsearch.php?startdate=' . $startDate . '&enddate=' . $endDate . '&page={page}&{filterList:filter}",
			pager_ajaxProcessing: function(data){
				if (data && data.hasOwnProperty("rows")) {
				  var r, row, c, d = data.rows,
				  // total number of rows (required)
				  total = data.total_rows,
				  // array of header names (optional)
				  headers = data.headers,
				  // all rows: array of arrays; each internal array has the table cell data for that row
				  rows = [],
				  // len should match pager set size (c.size)
				  len = d.length;
				  // this will depend on how the json is set up - see City0.json
				  // rows
				  for ( r=0; r < len; r++ ) {
					row = []; // new row array
					// cells
					for ( c in d[r] ) {
					  if (typeof(c) === "string") {
						row.push(d[r][c]); // add each table cell data to row array
					  }
					}
					rows.push(row); // add new row array to rows array
				  }
				  // in version 2.10, you can optionally return $(rows) a set of table rows within a jQuery object
				  $.plot("#scatter", [data.data], options);
				  return [ total, rows, headers ];
				}
			}
		}
	});
	$(".tablesorter-filter").each(function() {
		var jThis = $(this);
		jThis.autocomplete({
			minLength:0,
			select: function(event, ui, filter) {
				$(\'#alarm-sort\').trigger(\'search\', filter);
			},
			source: function(request, response) {
				if (jThis.data("column") != 4) {
					//console.log([jThis, request]);
					var filter = [];
					$(".tablesorter-filter").each(function() {
						filter.push($(this).val());
					});
					$.ajax({
						// the URL for the request
						url: "includes/ajax.alarmcat.php",
						// the data to send (will be converted to a query string)
						data: {startdate: \'' . $startDate . '\', enddate: \'' . $endDate . '\', filter: filter, column: jThis.data("column")},
						// whether this is a POST or GET request
						type: "GET",
						// the type of data we expect back
						dataType : "json",
						beforeSend: function() {
							},
						// code to run if the request succeeds;
						// the response is passed to the function
						success: function( json ) {
								response(json);
							},
						// code to run if the request fails; the raw request and
						// status codes are passed to the function
						error: function( xhr, status, errorThrown ) {
							},
						// code to run regardless of success or failure
						complete: function( xhr, status ) {
							}
					});
				};
			}
		}).focus(function(event) {
			$(this).autocomplete(\'search\');
		});
	});
	var options = {
			colors: trendcolors,
			series: {
				points: {
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
				show:false
			}
		};
	$("#scatter").bind("plothover", function(event, pos, item) {
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
		$("#scatter").bind("plotclick", function(event,pos,item) {
			if (item) {
				if (item.series.data[item.dataIndex][3]) {
					window.location.href = item.series.data[item.dataIndex][3];
				};
			};
		});
});
</script>';
$hookReplace['help'] = $helptext['default7'] . $helptext['scatterhover'] . $helptext['searchtable'] . $helptext['columntoggle'] . $helptext['tablepager'];
require_once 'includes/footer.php'; 
?>