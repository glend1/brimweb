<?PHP
$title = 'Static Reports';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][8] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<div id="reportlivesearch"><label for="reportsearch"><h3>Live Reports Search</h3></label><input id="reportsearch" name="search" placeholder="Search" data-column="0" type="search"/>
<div class="hinttext">e.g. cdas, chemicals</div></div>
<table class="records tablesorter-default" id="report-sort"><thead><th>Name</th><th>Start Date</th><th>End Date</th><th>Frequency</th></thead><tbody></tbody>' . fTableFooter(['id' => 'report-sort', 'cols' => 4]) . '</table>
<script type="text/javascript">
	$(function() {
		var reporttable = $("#report-sort").tablesorter({
			widthFixed:true,
			headers: {0 : { sorter: false, columnSelector: false },
					1 : { sorter: false, columnSelector: false },
					2 : { sorter: false, columnSelector: false },
					3 : { sorter: false, columnSelector: false }},
			widgets: ["zebra", "filter", "pager"],
			widgetOptions : {
				//filter_columnFilters:false,
				zebra : [ "oddRow", "evenRow" ],
				filter_external : "#reportsearch",
				filter_columnFilters : false,
				pager_selectors: {
					container: $("#report-sort-pager"),
				},
				//pager_fixedHeight: true,
				//storageKey:"report-sort-table",
				pager_size:20,
				pager_savePages:false,
				pager_output: "{page:input}/{filteredPages} (20)",
				pager_ajaxUrl : "includes/ajax.reportsearch.php?page={page}&{filterList:filter}",
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
					  return [ total, rows, headers ];
					}
				}
			}
		});	
	});
</script>';
$hookReplace['help'] = '<a href="#">Report Searching</a><div>When searching for a Static Report you can provide a comma seperated list, searching in this way will filter each query simulataniously in any order. e.g. "water, plant" will find "water plant report", "plant water report" and "surface water plant report".</div>' . $helptext['tablepager'];
require_once 'includes/footer.php'; ?>