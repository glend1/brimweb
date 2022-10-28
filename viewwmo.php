<?PHP $title = 'View WMO';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'WMO ID missing!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<h2>Viewing WMO #' . $_GET['id'] . '</h2>';
$queryGetType = 'select typefk
from wmo 
join WMOPriorityCode on WMO.PriorityFK = WMOPriorityCode.ID
where WMO.ID = ' . $_GET['id'];
$dataGetType = odbc_exec($conn, $queryGetType);
if (odbc_fetch_row($dataGetType)) {
	$typeId = odbc_result($dataGetType, 1);
} else {
	$_SESSION['sqlMessage'] = 'WMO not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryWmo = 'select top 1 wmo.ID, case when users.name IS NULL then \'Anonymous\' else users.name end, ForeignWMOFK, WMOStatusCode.Description, temppriority.Description, version, OpenDateTime, CloseDateTime, EstTimeSec, SameAsFK, case when cast(temppriorityorder as float) / pricodecount.count * sumvotes IS NULL then 0 else cast(temppriorityorder as float) / pricodecount.count * sumvotes end as priority, PermissionAbstract.AbstractName, commentcounttable.commentcount, wmojson.json, rating, wmo.submitteduserfk
from WMO
join (select COUNT(ID) as count from WMOPriorityCode where typefk = ' . $typeId . ' ) as pricodecount on 1 = 1
left join Users on users.ID = SubmittedUserFK
left join PermissionAbstract on PermissionAbstract.ID = WMO.PermissionAbstractFK
left join WMOStatusCode on WMOStatusCode.ID = WMO.StatusFK
left join 
	(select ID, description, ROW_NUMBER() over(order by priorityorder asc) as temppriorityorder, typefk
	from WMOPriorityCode where typefk = ' . $typeId . ' ) as temppriority on temppriority.ID = WMO.PriorityFK
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
left join (select wmofk, rating from WMOVotes where UserFK = ' . $_SESSION['id'] . ') as myvotes on myvotes.wmofk = WMO.ID
where wmo.id = ' . $_GET['id'] . '
order by opendatetime desc';
$row = 0;
$dataWmo = odbc_exec($conn, $queryWmo);
if (odbc_fetch_row($dataWmo)) {
	$json = json_decode(odbc_result($dataWmo, 14), true);
	$openTime = date_timezone_set(date_create(odbc_result($dataWmo, 7), timezone_open('UTC')), timezone_open('Europe/London'));
	if (odbc_result($dataWmo, 8)) {
		$closeTime = date_timezone_set(date_create(odbc_result($dataWmo, 8), timezone_open('UTC')), timezone_open('Europe/London'));
	};
	$stdOut .= '<table class="records"><thead><tr><th>WMO ID</th><th>Status</th><th>Submitted By</th><th>Priority (Type)</th></tr></thead>
	<tbody><tr class="oddRow"><td class="wmoid"><a data-text="Edit WMO" href="editwmo.php?id=' . odbc_result($dataWmo, 1) . '"><span class="icon-edit icon-hover-hint"></span></a><a href="viewwmo.php?id=' . odbc_result($dataWmo, 1) . '">#' . odbc_result($dataWmo, 1) . '</a></td><td>' . odbc_result($dataWmo, 4) . '</td><td>' . odbc_result($dataWmo, 2) . '</td><td>' . fWmoVote(odbc_result($dataWmo, 15), odbc_result($dataWmo, 1), odbc_result($dataWmo, 16)) . odbc_result($dataWmo, 11) . ' (' . odbc_result($dataWmo, 5) . ')</td></tr></tbody></table>
	<table class="records"><thead><tr><th>Frontline ID</th><th>Brimweb Version</th><th>Date Created</th><th>Date Closed</th><th>Estimated Time</th><th>Page Name</th><th>Parent WMO</th></tr></thead>
	<tbody><tr class="oddRow"><td>' . odbc_result($dataWmo, 3) . '</td><td>' . odbc_result($dataWmo, 6) . '</td><td>' . date_format($openTime, 'Y-m-d h:i:s A') . '</td><td>';
	if (odbc_result($dataWmo, 8)) {
		$stdOut .= date_format($closeTime, 'Y-m-d h:i:s A');
	};
	$stdOut .= '</td><td>';
	if (odbc_result($dataWmo, 9)) {
		$stdOut .= fToTime(odbc_result($dataWmo, 9));
	};
	$stdOut .= '</td><td>' . odbc_result($dataWmo, 12) . '</td><td>' . odbc_result($dataWmo, 10) . '</td></tr></tbody></table>';
	if ($json) {
		$stdOut .= '<h3>Recorded Variables <a href="#"><span class="icon-caret-right icon-large"></span><span class="icon-caret-down icon-large"></span></a></h3>' . fPrintList($json, 'wmovar');
	};
	if (odbc_result($dataWmo, 13)) {
		$stdOut .= '<h3>Comments<div class="hinttext">(Total: ' . odbc_result($dataWmo, 13) . ')</div></h3>';
		$queryComments = 'select wmocomment.ID, submitteddatetime, case when users.name IS NULL then \'Anonymous\' else users.name end, comment, userfk
		from WMOComment
		left join Users on Users.ID = WMOComment.UserFK
		where WMOFK = ' . $_GET['id'] . '
		order by SubmittedDateTime asc';
		$dataComments = odbc_exec($conn, $queryComments);
		while (odbc_fetch_row($dataComments)) {
			$commentTime = date_timezone_set(date_create(odbc_result($dataComments, 2), timezone_open('UTC')), timezone_open('Europe/London'));
			$stdOut .= '<div class="comment ';
			if ($row % 2 == 0) {
				$stdOut .= 'oddRow';
			} else {
				$stdOut .= 'evenRow';
			};
			$row++;
			$stdOut .= '">';
			if (fCanSee($_SESSION['id'] == odbc_result($dataComments, 5))) {
				$stdOut .= '<div class="wmoedit"><a data-text="Edit Comment" href="editwmocomment.php?id=' . odbc_result($dataComments, 1) . '"><span class="icon-edit icon-hover-hint icon-large"></span></a> <a data-text="Delete Comment" href="includes/processwmocomment.php?delete=true&id=' . odbc_result($dataComments, 1) . '"><span class="icon-remove icon-hover-hint icon-large"></span></a></div>';
			};
			$stdOut .= '<b>' . odbc_result($dataComments, 3) . '</b> said at <b>' . date_format($commentTime, 'Y-m-d h:i:s A') . '</b> (#' . odbc_result($dataComments, 1) . ')<div></div>';
			while($commentOut = odbc_result($dataComments, 4)) {
				$stdOut .= $commentOut;
				unset($commentOut);
			};
			$stdOut .= '</div>';
		};
	};
	$stdOut .= '<form action="includes/processwmocomment.php" method="post">
	<input type="hidden" name="id" value="' . $_GET['id'] . '"/>
	<label for="comment" id=comment"><h3>Add Comment</h3></label>
	<textarea name="comment" id="comment"></textarea>
	<div class="bug-notif">
	<span class="subscribe-button"><label for="subscribe-wmo">Subscribe:</label> <input name="subscribe" checked type="checkbox" id="subscribe-wmo"/></span>
	</div>
	<div id="submit"><input type="submit" name="add" class="validatetextbutton" value="Submit!" /></div></form>';
	$queryVotes = 'select case when users.name IS NULL then \'Anonymous\' else users.name end, rating
	from wmovotes
	left join users on Users.ID = WMOVotes.UserFK
	where WMOFK = ' . $_GET['id'] . '
	order by users.name asc';
	$dataVotes = odbc_exec($conn, $queryVotes);
	$sVotes = '<h3>Votes</h3><div class="votes ';
	if ($row % 2 == 0) {
		$sVotes .= 'oddRow';
	} else {
		$sVotes .= 'evenRow';
	};
	$sVotes .= '">';
	$sep = '';
	while (odbc_fetch_row($dataVotes)) {
		$sVotes .= $sep . odbc_result($dataVotes, 1) . ' (' . odbc_result($dataVotes, 2) . ')';
		$sep = ', ';
	};
	$sVotes .= '</div>';
	if (!empty($sep)) {
		$stdOut .= $sVotes;
	};
};
$stdOut .= '<script type="text/javascript">
$(function() {
	$(\'.content a[href="#"]\').click(function() {
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
	$(".validatetextbutton").click(function() {
		var bValid = true;
		$(".ui-state-error").removeClass( "ui-state-error" );
		if ($("#bodybody").find("iframe").contents().find("body").text() == "") {
			bValid = false;
			updateTips("The comment must contain text");
			$("#bodybody").addClass( "ui-state-error" );
		};
		if (bValid == false) {
			return false;
		};
	});
});
</script>';
$hookReplace['help'] = '<a href="#">Variable Traversal</a><div>Clicking the right-arrow beside the Recorded Variables header will open that the variable tree allowing exploration.</div>' . $helptext['wmovoting'];
require_once 'includes/footer.php'; ?>