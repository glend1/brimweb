<?PHP $title = 'View Active WMOs';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryWmo = 'select top 20 wmo.ID, case when users.name IS NULL then \'Anonymous\' else users.name end, ForeignWMOFK, WMOStatusCode.Description, temppriority.Description, version, OpenDateTime, CloseDateTime, EstTimeSec, SameAsFK, case when cast(temppriorityorder as float) / pricodecount.count * sumvotes IS NULL then 0 else cast(temppriorityorder as float) / pricodecount.count * sumvotes end as priority, PermissionAbstract.AbstractName, comment, commentcounttable.commentcount, wmojson.json, wmoprioritycode.typefk, rating, wmo.submitteduserfk 
from WMO
left join wmoprioritycode on wmoprioritycode.id = wmo.priorityfk
left join (select COUNT(ID) as count, typefk 
	from WMOPriorityCode
	group by WMOPriorityCode.TypeFK) as pricodecount on pricodecount.TypeFK = wmoprioritycode.typefk
left join Users on users.ID = SubmittedUserFK
left join PermissionAbstract on PermissionAbstract.ID = WMO.PermissionAbstractFK
left join WMOStatusCode on WMOStatusCode.ID = WMO.StatusFK
left join 
	(select ID, description, ROW_NUMBER() over(partition by typefk order by priorityorder asc) as temppriorityorder, typefk
	from WMOPriorityCode ) as temppriority on temppriority.ID = WMO.PriorityFK
left join WMOjson on wmojson.wmofk = WMO.id
left join 
	(select wmo.id , COUNT(wmocomment.id) as commentcount 
	from WMO 
	join wmocomment on wmofk = WMO.ID
	group by wmo.ID) as commentcounttable on commentcounttable.id = WMO.ID
left join 
	(select wmo.id , sum(WMOVotes.rating) as sumvotes 
	from WMO 
	join wmovotes on wmofk = WMO.ID
	group by wmo.ID) as sumvotestable on sumvotestable.id = WMO.ID
left join wmocomment on WMOComment.ID = 
	(select top 1 WMOComment.ID
	from WMOComment
	where WMOComment.WMOFK = WMO.ID
	order by SubmittedDateTime asc)
left join (select wmofk, rating from WMOVotes where UserFK = ' . $_SESSION['id'] . ') as myvotes on myvotes.wmofk = WMO.ID
where statusfk <> 2
order by priority desc, opendatetime asc';
$row = 0;
$dataWmo = odbc_exec($conn, $queryWmo);
while(odbc_fetch_row($dataWmo)) {
	$json = json_decode(odbc_result($dataWmo, 15), true);
	$openTime = date_timezone_set(date_create(odbc_result($dataWmo, 7), timezone_open('UTC')), timezone_open('Europe/London'));
	if (odbc_result($dataWmo, 8)) {
		$closeTime = date_timezone_set(date_create(odbc_result($dataWmo, 8), timezone_open('UTC')), timezone_open('Europe/London'));
	};
	$workOrder[odbc_result($dataWmo, 16)][0] = odbc_result($dataWmo, 4);
	$workOrder[odbc_result($dataWmo, 16)][1][$row] = '<table><thead><tr><th>WMO ID</th><th>Status</th><th>Submitted By</th><th>Priority (Type)</th></tr></thead>
	<tbody><tr><td class="wmoid"><a data-text="Edit" href="editwmo.php?id=' . odbc_result($dataWmo, 1) . '"><span class="icon-edit icon-hover-hint"></span></a><a href="viewwmo.php?id=' . odbc_result($dataWmo, 1) . '">#' . odbc_result($dataWmo, 1) . '</a></td><td>' . odbc_result($dataWmo, 4) . '</td><td>' . odbc_result($dataWmo, 2) . '</td><td>' . fWmoVote(odbc_result($dataWmo, 17), odbc_result($dataWmo, 1), odbc_result($dataWmo, 18)) . odbc_result($dataWmo, 11) . ' (' . odbc_result($dataWmo, 5) . ')</td></tr></tbody></table>
	<table><thead><tr><th>Frontline ID</th><th>Brimweb Version</th><th>Date Created</th><th>Date Closed</th><th>Estimated Time</th><th>Page Name</th><th>Parent WMO</th></tr></thead>
	<tbody><tr><td>' . odbc_result($dataWmo, 3) . '</td><td>' . odbc_result($dataWmo, 6) . '</td><td>' . date_format($openTime, 'Y-m-d h:i:s A') . '</td><td>';
	if (odbc_result($dataWmo, 8)) {
		$workOrder[odbc_result($dataWmo, 16)][1][$row] .= date_format($closeTime, 'Y-m-d h:i:s A');
	};
	$workOrder[odbc_result($dataWmo, 16)][1][$row] .= '</td><td>';
	if (odbc_result($dataWmo, 9)) {
		$workOrder[odbc_result($dataWmo, 16)][1][$row] .= fToTime(odbc_result($dataWmo, 9));
	};
	$workOrder[odbc_result($dataWmo, 16)][1][$row] .= '</td><td>' . odbc_result($dataWmo, 12) . '</td><td>' . odbc_result($dataWmo, 10) . '</td></tr></tbody></table>';
	if ($json) {
		$workOrder[odbc_result($dataWmo, 16)][1][$row] .= '<h3>Recorded Variables <a href="#"><span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></a></h3>' . fPrintList($json);
	};
	$workOrder[odbc_result($dataWmo, 16)][1][$row] .= '<h3>Original Comment <a href="#"><span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></a><div class="hinttext">(Total: ' . odbc_result($dataWmo, 14) . ')</div></h3>';
	while($commentOut = odbc_result($dataWmo, 13)) {
		$workOrder[odbc_result($dataWmo, 16)][1][$row] .= $commentOut;
		unset($commentOut);
	};
	$row++;
};
$divRow = 0;
if (isset($workOrder[3])) {
	$stdOut .= '<h3>' . $workOrder[3][0] . '</h3>';
	foreach ($workOrder[3][1] as $div) {
		$stdOut .= '<div class="wmo ';
		if ($divRow % 2 == 0) {
			$stdOut .= 'oddRow';
		} else {
			$stdOut .= 'evenRow';
		};
		$stdOut .= '">' . $div . '</div>';
		$divRow++;
	};
};
if (isset($workOrder[2])) {
	$stdOut .= '<h3>' . $workOrder[2][0] . '</h3>';
	foreach ($workOrder[2][1] as $div) {
		$stdOut .= '<div class="wmo ';
		if ($divRow % 2 == 0) {
			$stdOut .= 'oddRow';
		} else {
			$stdOut .= 'evenRow';
		};
		$stdOut .= '">' . $div . '</div>';
		$divRow++;
	};
};
$stdOut .= '<script type="text/javascript">
$(function() {
$(\'.wmo a[href="#"]\').click(function() {
	var clicked = $(this).parent();
	switch (clicked.prop("tagName")) {
		case "H3":
			if (clicked.next().css("display") == "none") {
				$(this).find(".icon-caret-down").css("display", "inline");
				$(this).find(".icon-caret-right").css("display", "none");									
				clicked.next().css("display", "block");
			} else {
				$(this).find(".icon-caret-down").css("display", "none");
				$(this).find(".icon-caret-right").css("display", "inline");
				clicked.next().css("display", "none");
			};
			break;
		case "LI":
			if (clicked.children("ul").css("display") == "none") {
				$(this).find(".icon-caret-down").css("display", "inline");
				$(this).find(".icon-caret-right").css("display", "none");									
				clicked.children("ul").css("display", "block");
			} else {
				$(this).find(".icon-caret-down").css("display", "none");
				$(this).find(".icon-caret-right").css("display", "inline");
				clicked.children("ul").css("display", "none");
			};
			break;
		default:
			alert(clicked.prop("tagName"));
			break;
	};
	return false;
});
});
</script>';
$hookReplace['help'] = '<a href="#">Comment/Variable Traversal</a><div>Clicking the right-arrow beside the header name will open that group revealing its contents.</div>' . $helptext['wmovoting'];
require_once 'includes/footer.php'; ?>