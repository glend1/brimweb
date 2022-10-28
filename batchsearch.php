<?PHP 
$title = 'Batch Search';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][4] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['calicon'] = '<a data-text="Date/Time Picker" href="#" class="menucontext"><span class="icon-calendar-empty icon-hover-hint icon-large"></span></a>';
$hookReplace['calform'] = fStandardCal('batchsearch', '[2004, 05, 18]');
fSetDates($startDate, $endDate, 30);
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$stdOut .= '<h2>Batches from ' . $startDate . ' to ' . $endDate . '</h2><div id="scatter"></div><h3 class="clear">Search <a data-text="Table Headers" data-id="batch" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3>
<div class="tablesorter-row-hider" id="batch-table-rows"></div>
<table class="records" id="batch-sort"><thead><tr><th class="filter-false">Start Date/Time</th><th>Campaign</th><th>Lot</th><th>Batch</th><th>Product</th><th>Recipe</th><th>Train</th><th>Department</th><th>Equipment</th><th>Status</th></tr></thead><tbody></tbody>' . fTableFooter(['id' => 'batch-sort', 'cols' => 10]) . '</table>
<script type="text/javascript"> 
$(function() {
	$("#batch-sort").tablesorter({
		widthFixed:true,
		headers: {0 : { sorter: false },
				1 : { sorter: false },
				2 : { sorter: false },
				3 : { sorter: false },
				4 : { sorter: false, columnSelector: false },
				5 : { sorter: false },
				6 : { sorter: false, columnSelector: false },
				7 : { sorter: false },
				8 : { sorter: false, columnSelector: false },
				9 : { sorter: false, columnSelector: false }},
		widgets: ["zebra", "filter", "columnSelector", "pager"],
		widgetOptions : {
			//filter_columnFilters:false,
			columnSelector_container: $(\'#batch-table-rows\'),
			columnSelector_mediaquery: false,
			columnSelector_saveColumns: false,
			columnSelector_layout: \'<div class="columnselectoritem"><input type="checkbox" id="label-sort-{name}"><label for="label-sort-{name}">{name}</label></div>\',
			zebra : [ "oddRow", "evenRow" ],
			pager_selectors: {
				container: $("#batch-sort-pager"),
			},
			//pager_fixedHeight: true,
			//storageKey:"alarms-sort-table",
			pager_size:100,
			pager_savePages:false,
			pager_output: "{page:input}/{filteredPages} (100)",
			pager_ajaxUrl : "includes/ajax.batchsearch.php?startdate=' . $startDate . '&enddate=' . $endDate . '&page={page}&{filterList:filter}",
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
				$(\'#batch-sort\').trigger(\'search\', filter);
			},
			source: function(request, response) {
				if (jThis.data("column") != 3) {
					//console.log([jThis, request]);
					var filter = [];
					$(".tablesorter-filter").each(function() {
						filter.push($(this).val());
					});
					$.ajax({
						// the URL for the request
						url: "includes/ajax.batchcat.php",
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
$hookReplace['help'] = $helptext['default30'] . $helptext['scatterhover'] . $helptext['searchtable'] . $helptext['columntoggle'] . $helptext['tablepager'];
require_once 'includes/footer.php'; 
?>