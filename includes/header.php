<?PHP 
header('Content-Type: text/html; charset=iso-8859-1');
//header('Location:maintenance.php');
require_once 'includes/functions.php';
$res = 128;
$stdOut = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Brimweb: ' . $title . '</title>
	<link rel="stylesheet" href="css/normalize.css" type="text/css">
	<link rel="stylesheet" href="css/start/jquery-ui.css" type="text/css">
	<link rel="stylesheet" href="css/jquery-ui-timepicker-addon.css" type="text/css">
	<link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
	<!-- <link rel="stylesheet" href="css/select2.css">-->
	<!--[if IE 7]><link rel="stylesheet" href="css/font-awesome/css/font-awesome-ie7.min.css"><![endif]-->
	<link rel="stylesheet" href="js/sceditor/jquery.sceditor.default.min.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/brimweb.css" type="text/css">
	<link rel="stylesheet" href="js/sceditor/default.min.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/mint-bubblegum.css" type="text/css">
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print">
	<script type="text/javascript">
		var res = ' . $res . ';
	</script>
	<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery-dateFormat.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery-ui.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery-ui-sliderAccess.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.sceditor.bbcode.min.js"></script>
	<!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.orderBars.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.flot.stackpercent.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.flot.stack.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.pie.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.flot.categories.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.JUMlib.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.gantt.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.mouse.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.time.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.selection.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.crosshair.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.dashes.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.axislabels.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.navigate.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.flot.resize.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.widgets.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.pager.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.columnSelector.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.math.js"></script>
	<script language="javascript" type="text/javascript" src="JS/jquery.tablesorter.output.js"></script>
	<!--<script language="javascript" type="text/javascript" src="JS/select2.js"></script>-->
	<script language="javascript" type="text/javascript" src="js/brimweb.js"></script>
</head>
<body>
<div id="navbar"><div><span id="userbar"><a data-text="Submit Bug" href="#"><span class="icon-bug icon-hover-hint icon-large"></span></a>';
			if (!isset($_SESSION['id'])) {
				$stdOut .= '<a id="login-dialog-click" data-text="Sign in" href="loginpage.php"><span class="icon-signin icon-hover-hint icon-large"></span></a>';
			} else {
				$stdOut .= '<a data-text="Mail" href="inbox.php">';
				$queryUnreadMail = 'select count(*)
				from Message
				where readbit = 0 and toid = ' . $_SESSION['id'];
				$dataUnreadMail = odbc_exec($conn, $queryUnreadMail);
				if (odbc_fetch_row($dataUnreadMail)) {
					if (odbc_result($dataUnreadMail, 1)) {
						$stdOut .= '<span class="inboxsize"><span>' . odbc_result($dataUnreadMail, 1) . '</span></span>';
					};
				};
				$stdOut .= '<span class="icon-envelope icon-hover-hint icon-large"></span></a>';
				$stdOut .= '<a data-text="Administration" href="admin.php"><span class="icon-cog icon-hover-hint icon-large"></span></a><a data-text="Sign Out" href="includes/logout.php"><span class="icon-signout icon-hover-hint icon-large"></span></a>';
			};
		$stdOut .= '<a data-text="Help" href="#"><span id="icon-questionhints" class="icon-question-sign icon-hover-hint icon-large"></span></a></span><span id="quicknav"><a data-text="Goto Top" href="#up"><span class="icon-arrow-up icon-hover-hint icon-large"></span></a><a data-text="Goto Bottom" href="#down"><span class="icon-arrow-down icon-hover-hint icon-large"></span></a><a data-text="Home" href="index.php"><span class="icon-home icon-hover-hint icon-large"></span></a><a data-text="Print" onclick="printpage()" href="#"><span class="icon-print icon-hover-hint icon-large"></span></a>' . fHookInsert('calicon') . fHookInsert('contexticon') . fHookInsert('downloadicon') . fHookInsert('searchicon') . '</span>' . fMenu($navList) . '</div></div><a name="up"></a>' . fHookInsert('contextmenu') . fHookInsert('downloadmenu') . fHookInsert('calform') . fHookInsert('searchform') . fHookInsert('bugform') . '<div id="help">' . fHookInsert('help') . '</div><div class="content">';
		$helptext['standardcal'] = '<a href="#">Date/Time Picker</a><div>Click the calendar in the top left corner to open the date/time picker. There are two calendars, the left one chooses a start point and the right chooses an end point, once you have selected the date range select complete the action by clicking "Use Range" and the results will be filtered appropriately.<br />Note: The date range filter is inclusive.</div>';
		$helptext['default30'] = '<a href="#">Default Selection</a><div>The default date/time selection is 30 days.</div>';
		$helptext['default7'] = '<a href="#">Default Selection</a><div>The default date/time selection is 7 days.</div>';
		$helptext['default24h'] = '<a href="#">Default Selection</a><div>The default date/time selection is 24 hours.</div>';
		$helptext['piehover'] = '<a href="#">Pie Graph Hovering</a><div>Hovering the Mouse Cursor over the Pie Graph will show you the chosen slices data.</div>';
		$helptext['barhover'] = '<a href="#">Bar Graph Hovering</a><div>Hovering the Mouse Cursor over a bar in the Bar Graph will show you the chosen bars data.</div>';
		$helptext['timelinehover'] = '<a href="#">Timeline Graph Hovering</a><div>Hovering the Mouse Cursor over a bar in the Timeline Graph will show you the chosen bars data.</div>';
		$helptext['linehover'] = '<a href="#">Line Graph Hovering</a><div>Hovering the Mouse Cursor over a point in the Line Graph will show you the chosen points data.</div>';
		$helptext['scatterhover'] = '<a href="#">Scatter Graph Hovering</a><div>Hovering the Mouse Cursor over a point in the Scatter Graph will show you the chosen points data.</div>';
		$helptext['linehoverany'] = '<a href="#">Line Graph Hovering</a><div>Hovering the Mouse Cursor within the Line Graph will show you the highlighted data in the legend and a timestamp in the lower left hand side of the chart.</div>';
		$helptext['linedrag'] = '<a href="#">Line Graph Click and Dragging</a><div>Clicking and dragging within the smaller, "Overview", line chart will zoom in into the selected area.</div>';
		$helptext['linemarkings'] = '<a href="#">Line Graph Markings</a><div>Marking on the line chart show daily intervals.</div>';
		$helptext['graphtoggle'] = '<a href="#">Graph Data Toggling</a><div>Unchecking the legends item checkbox will remove that legends item visibility from the graph. Checking the legends item checkbox will show that legends item visibility.</div>';
		$helptext['search'] = '<a href="#">Searching</a><div>Click the Magnifying Glass in the top left hand corner to open the search bar, focus will be automatically given, once you\'ve typed your search query submit the form and the page will be filtered based on your query, to clear the search filter open the search bar once again and click the large "X".</div>';
		$helptext['update'] = '<a href="#">Updating</a><div>Updating renames the object across the site and/or changes its relationship to other objects.</div>';
		$helptext['delete'] = '<a href="#">Deleting</a><div>Deleting removes the object from the site and all references to it across the site.</div>';
		$helptext['add'] = '<a href="#">Creating/Adding</a><div>Adding an object to the site makes it available to other functions of the site.</div>';
		$helptext['groupadmin'] = '<a href="#">What is a Group Owner?</a><div>Group Owner preventative measure to stop objects from becoming orphaned. Without setting a Group Owner no one, except the admin, has access to modifying the object. Setting a Group Owner gives the group initial ownership over the object.</div>';
		$helptext['groupconflict'] = '<a href="#">Group Conflict</a><div>If a User is in two or more User Groups access is granted as if that User is a member of those groups. If there is a conflict of permission the highest level of permission is applied.</div>';
		$helptext['groupadmingroup'] = '<a href="#">Significence of Group "Group Admins"</a><div>When applied to Groups, Group Admin determines hierarchy. Group Admins of Groups are members of that group and as such all permissions applied to the child group are applied to its parent group aswell. There is no limit to how many generations a parent can have.</div>';
		$helptext['editpermissions'] = '<a href="#">Edit Permissions</a><div>Editing Permissions allow you to change what Users of that User Group can do.</div>';
		$helptext['recordswap'] = '<a href="#">Recordset Swapping</a><div>Clicking on the headers will swap between recordsets, if the records cannot be loaded the original recordset will be shown to you. clicking the refresh icon will reattain the selected records.</div>';
		$helptext['dynrecordswap'] = '<a href="#">Dynamic Recordset Swapping</a><div>When appropriate, table icons will appear within the recordset. Clicking these will obtain the related recordset as a new header. Data is polled from the visible start of the graph to the visible end of the graph.</div>';
		$helptext['autohighlight'] = '<a href="#">Automatic Graph Highlighting</a><div>When appropriate, graph icons will appear within the recordset. Clicking these will highlight or zoom, if available, the graph showing the selected recordset date/time range. No selection will be made if the range is too small.</div>';
		$helptext['wmovoting'] = '<a href="#">Voting</a><div>Voting using the up/down/minus icons change the priority rank, pushing the most voted WMOs to the top of the active work list. Voting will also subscribe you to the Work Order. Please note you are unable to vote on WMOs you\'ve created</div>';
		$helptext['trendsave'] = '<a href="#">Saving Trends</a><div>If you are a trends original creator saving will overright the existing version, otherwise a duplicate copy is made under your username.</div>';
		$helptext['departmentequip'] = '<a href="#">Department or Equipment</a><div>The set Department is discarded if Equipment is set.</div>';
		$helptext['tablesorter'] = '<a href="#">Table Manipulation</a><div>You can sort the data on any column by clicking on the column header, the graphic in the header will change to reflect the new sort order. Holding Shift while clicking the header will allow you to sort multiple columns simultaneously.<br />
		You are permitted to change the Recordset page by clicking the buttons below the table or by changing the page number in the footer area.</div>';
		$helptext['tablesorterflot'] = '<a href="#">Table/Graph Interation</a><div>Selecting or deselecting legend items via the provided checkboxes will also update the records table to reflect the selection.</div>';
		$helptext['recordsetcolumns'] = '<a href="#">Recordset Visibility</a><div>Clicking the list icon beside a recordset title will make visible a list of column headers, selecting or deselecting the provided checkboxes will remove/add data columns to the recordset.</div>';
		$helptext['searchtable'] = '<a href="#">Table Searching</a><div>Searching tables is done by clicking and typing in the input fields of the table. Each column has a input field, once interacted with you will be provided with a dropdown menu with the ability to refine your selection, or to scroll throw the results and select the option desired.<br/>Only 20 records are shown at a time.</div>';
		$helptext['columntoggle'] = '<a href="#">Column Toggle</a><div>You can toggle visibility of table columns by clicking the list icon next to the search title, this will provide you with a list of checkboxes, once checked/unchecked visibility for the selected column will be toggled.</div>';
		$helptext['tablepager'] = '<a href="#">Table Pages</a><div>You are permitted to change the Recordset page by clicking the buttons below the table or by changing the page number in the footer area.</div>';
		$helptext['mailcontext'] = '<a href="#">Context Sensitive "Briefcase" Menu</a><div>Clicking the menu icon allows you to swap between mail folders and allows you to send mail to other Brimweb users.</div>';
		$helptext['status'] = '<a href="#">Status</a><div>Where possible, status changes are highlighed in the detail and are refected in the Gantt.</div>';
		//$helptext['shiftcheckboxtrend'] = '<a href="#">"Quick Select"</a><div>If you hold down the shift key and click on any legend checkbox all other pens wills will be turned off.</div>';
		$stdOut .= '<h1>' . $title . '</h1>';
		/*if (isset($_SESSION['sqlMessage'])) {
			$sqlMessage = '<div class="ui-notif-' . $_SESSION['uiState'] . '"><span class="icon-remove-sign"></span>' . $_SESSION['sqlMessage'] . '</div>';
			unset($_SESSION['uiState']);
			unset($_SESSION['sqlMessage']);
		};*/
		$extraNotifications = '';
		if ($versionFile = @fopen('c:\\stdout.txt', 'r')) {
			while ($line = fgets($versionFile)) {
				$version = trim($line);
			};
			fclose($versionFile);
		};
		//continues in content pages ?>