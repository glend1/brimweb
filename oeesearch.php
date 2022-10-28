<?PHP 
$title = 'OEE Search';
require_once 'includes/header.php';
if (!fCanSeePublic(@$_SESSION['permissions']['page'][1] >= 100)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<h3 class="clear">Search <a data-text="Table Headers" data-id="oee" class="table-row-button" href="none.php"><span class="icon-list icon-hover-hint"></span></a></h3>
<div class="tablesorter-row-hider" id="oee-table-rows"></div>
<table class="records" id="oee-sort"><thead><tr><th class="filter-false">Start Date/Time</th><th>Category</th><th>Discipline</th><th>Equipment</th><th>OEE Name</th><th>Area</th><th>Department</th></tr></thead><tbody></tbody>' . fTableFooter(['id' => 'oee-sort', 'cols' => 7]) . '</table>
<script type="text/javascript"> 
$(function() {
	$("#oee-sort").tablesorter({
		widthFixed:true,
		headers: {0 : { sorter: false },
				2 : { sorter: false },
				3 : { sorter: false },
				4 : { sorter: false },
				5 : { sorter: false },
				6 : { sorter: false, columnSelector: false },
				7 : { sorter: false, columnSelector: false }},
		widgets: ["zebra", "filter", "columnSelector", "pager"],
		widgetOptions : {
			//filter_columnFilters:false,
			columnSelector_container: $(\'#oee-table-rows\'),
			columnSelector_mediaquery: false,
			columnSelector_saveColumns: false,
			columnSelector_layout: \'<div class="columnselectoritem"><input type="checkbox" id="label-sort-{name}"><label for="label-sort-{name}">{name}</label></div>\',
			zebra : [ "oddRow", "evenRow" ],
			pager_selectors: {
				container: $("#oee-sort-pager"),
			},
			//pager_fixedHeight: true,
			//storageKey:"oees-sort-table",
			pager_size:20,
			pager_savePages:false,
			pager_output: "{page:input}/{filteredPages} (20)",
			pager_ajaxUrl : "includes/ajax.oeesearch.php?page={page}&{filterList:filter}",
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
	$(".tablesorter-filter").each(function() {
		var jThis = $(this);
		jThis.autocomplete({
			minLength:0,
			select: function(event, ui, filter) {
				$(\'#oee-sort\').trigger(\'search\', filter);
			},
			source: function(request, response) {
				//console.log([jThis, request]);
				var filter = [];
				$(".tablesorter-filter").each(function() {
					filter.push($(this).val());
				});
				$.ajax({
					// the URL for the request
					url: "includes/ajax.oeecat.php",
					// the data to send (will be converted to a query string)
					data: {filter: filter, column: jThis.data("column")},
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
			}
		}).focus(function(event) {
			$(this).autocomplete(\'search\');
		});
	});
	
});
</script>';
$hookReplace['help'] = $helptext['searchtable'] . $helptext['columntoggle'] . $helptext['tablepager'];
require_once 'includes/footer.php'; 
?>