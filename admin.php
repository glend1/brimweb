<?PHP 
$title = 'Control Panel';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryUsername = 'select top 1 name from users where id = ' . $_SESSION['id'];
$dataUsername = odbc_exec($conn, $queryUsername);
odbc_fetch_row($dataUsername);
$stdOut .= 'Hello, ' . odbc_result($dataUsername, 1) . '. Please select an action.';
if ($_SESSION['id'] == 1) {
	$stdOut .= '<h3><a href="" class="controlpanelheader">Admin Tools</a></h3><ul class="controlpanel"><li><a href="managepages.php">Manage Pages</a><div class="hinttext">Add/Remove/Rename/Sort Pages.</div></li>
	<li><a href="managenews.php">Manage News</a><div class="hinttext">Add/Remove/Edit News Items.</div></li>
	<li><a href="manageabstract.php">Manage Abstract Permissions</a><div class="hinttext">Add/Remove/Edit Abstract (undefinded) Permissions.</div></li>
	<li><a href="managesubscriptiontype.php">Manage Subscription Types</a><div class="hinttext">Add/Remove/Edit Subscription Types.</div></li>
	<li><a href="emailtest.php">Email Test</a><div class="hinttext">Send test emails.</div></li>
	<li><a href="colortest.php">CSS Colour Test</a><div class="hinttext">View CSS color styles.</div></li>
	<li><a href="test.php">Test Page</a><div class="hinttext">View the Test Page.</div></li>
	<li><a href="info.php">PHP info</a><div class="hinttext">View the PHP info page.</div></li>
	</ul>
	<h3><a href="" class="controlpanelheader">Scheduled Reports Admin</a></h3>
	<ul class="controlpanel">
	<li><a href="managecaltypes.php">Manage Schedule Types</a><div class="hinttext">Add/Update/Delete Schedule Types.</div></li>
	<li><a href="managecalrepeat.php">Manage Repeat Options</a><div class="hinttext">Add/Update/Delete Repeat Options.</div></li>
	<li><a href="managecalrepeattype.php">Manage Repeat Types</a><div class="hinttext">Add/Update/Delete Repeat Types.</div></li>
	<li><a href="managecalfunction.php">Manage Function Names</a><div class="hinttext">Add/Update/Delete Function Names.</div></li>
	<li><a href="managecalreportssplash.php">Manage Reports</a><div class="hinttext">Add/Update/Delete Scheduled Reports.</div></li>
	</ul>';
};
$stdOut .= '<h3><a href="" class="controlpanelheader">User Management</a></h3>
<ul class="controlpanel">
	<li><a href="changedetails.php">My Account</a><div class="hinttext">Change Mobile/Password/Ect.</div></li>
	<li><a href="managesubscription.php">My Subscriptions</a><div class="hinttext">Manage Subscriptions.</div></li>
	<li><a href="viewpermissions.php">My Permissions</a><div class="hinttext">View Current Permission set, useful for debugging.</div></li>
</ul>';
$stdOut .= '<h3><a href="" class="controlpanelheader">WMO</a></h3>
<ul class="controlpanel">
<li><a href="activewmo.php">View Active Work Orders</a><div class="hinttext">View Active Work Maintenence Orders.</div></li>';
$queryGetTypes = 'select id, description from wmotype order by description asc';
$dataGetTypes = odbc_exec($conn, $queryGetTypes);
$sViewType = '';
$sManualType = '';
while (odbc_fetch_row($dataGetTypes)) {
	$sViewType .= '	<li><a href="viewhistwmo.php?type=' . odbc_result($dataGetTypes, 1) . '">View Historical ' . ucwords(odbc_result($dataGetTypes, 2)) . 's</a><div class="hinttext">View Historic ' . ucwords(odbc_result($dataGetTypes, 2)) . ' Work Maintenence Orders.</div></li>';
	$sManualType .= '<li><a href="manualwmo.php?type=' . odbc_result($dataGetTypes, 1) . '">Submit Manual ' . ucwords(odbc_result($dataGetTypes, 2)) . '</a><div class="hinttext">Create ' . ucwords(odbc_result($dataGetTypes, 2)) . ' Work Maintenence Orders.</div></li>';
};
$stdOut .= $sManualType . $sViewType;
if ($_SESSION['id'] == 1) {
	$stdOut .= '<li><a href="managewmopriority.php">Manage Priority Codes</a><div class="hinttext">Add/Remove/Rename WMO Priorities and assign to WMO Types.</div></li>';
	$stdOut .= '<li><a href="managewmotype.php">Manage WMO Types</a><div class="hinttext">Add/Remove/Rename WMO Types.</div></li>';
	$stdOut .= '<li><a href="managewmostatus.php">Manage Status Codes</a><div class="hinttext">Add/Remove/Rename WMO Status Codes.</div></li>';
};
$stdOut .= '</ul>';
if (fCanSee(isset($_SESSION['edit']))) {
	$stdOut .= '<h3><a href="" class="controlpanelheader">Site Management</a></h3><ul class="controlpanel">';
	if (fCanSee(isset($_SESSION['edit']['areaedit']))) {
		$stdOut .= '<li><a href="managearea.php">Manage Areas</a><div class="hinttext">Add/Remove/Rename Areas</div></li>';
	};
	if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
		$stdOut .= '<li><a href="managedepartment.php">Manage Departments</a><div class="hinttext">Add/Remove/Rename Departments</div></li>
		<li><a href="managedepartmentequipment.php">Manage Department Equipment</a><div class="hinttext">Add/Remove/Rename/Reassign Department Equipment.</div></li>';
	};
	if (fCanSee(isset($_SESSION['edit']['disciplineedit']))) {
		$stdOut .= '<li><a href="managediscipline.php">Manage Disciplines</a><div class="hinttext">Add/Remove/Rename Disciplines and Associate with Areas</div></li>';
	};
	if (fCanSee(isset($_SESSION['admin']['groupadmin']))) {
		$stdOut .= '<li><a href="manageuser.php">Manage Users</a><div class="hinttext">Userlist and Assign Groups</div></li>';
	}; 
	if (fCanSee(isset($_SESSION['edit']['groupedit']))) {
		$stdOut .= '<li><a href="managegroup.php">Manage User Groups</a><div class="hinttext">Add/Remove Groups, Grouplist and Assigned Users, Remove Users from Groups and Group Permission editing</div></li>
		<li><a href="managetasktypes.php">Manage Common Task Types</a><div class="hinttext">Add/Remove/Edit Common Task Types.</div></li>
		<li><a href="managetasks.php">Manage Common Tasks</a><div class="hinttext">Assign Common Tasks to User Groups.</div></li>';
	};
	$stdOut .= '</ul>';
};
if (fCanSee(@$_SESSION['permissions']['page'][1] >= 100)) {
	if (fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
		$isTypes = True;
	} elseif (isset($_SESSION['permissions']['department'])) {
		$queryOeeType = 'select top 1 type.id
		from Type
		left join departmentequipment on departmentequipmentfk = departmentequipment.id 
		left join department on departmentfk = department.id';
		$oeeSep = ' where ';
		foreach ($_SESSION['permissions']['department'] as $departmentId => $level) {
			$queryOeeType .= $oeeSep . 'departmentfk = ' . $departmentId;
			$oeeSep = ' or ';
		};
		$dataOeeType = odbc_exec($conn, $queryOeeType);
		if (odbc_fetch_row($dataOeeType)) {
			$isTypes = True;
		};
	};
};
if (isset($isTypes)) {
	$stdOut .= '<h3><a href="" class="controlpanelheader">OEE</a></h3><ul class="controlpanel">';
	if (fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
		$stdOut .= '<li><a href="manageoeesuspicious.php">Suspicious OEE Data</a><div class="hinttext">Remove/Edit Suspicious OEE Data</div></li>';
		$stdOut .= '<li><a href="manageoeecategory.php">Manage Categories</a><div class="hinttext">View/Add/Remove/Rename/Edit OEE Categories</div></li>';
		$stdOut .= '<li><a href="manageoeename.php">Manage Reasons</a><div class="hinttext">View/Add/Remove/Rename/Edit OEE Reasons</div></li>';
		$stdOut .= '<li><a href="manageoeegroup.php">Manage Groups</a><div class="hinttext">View/Add/Remove/Rename/Edit OEE Groups</div></li>';
	};
	if (fCanSee(@$_SESSION['permissions']['page'][1] >= 200)) {
		$stdOut .= '<li><a href="selectoeetype.php">Manage Types</a><div class="hinttext">View/Add/Remove/Rename/Edit OEE Types</div></li>';
	};
	$stdOut .= '<li><a href="selectoeerecord.php">Manage Records</a><div class="hinttext">View/Add/Remove/Edit OEE Records</div></li></ul>';
};
if (fCanSee(@$_SESSION['permissions']['page'][4] >= 200)) {
	$stdOut .= '<h3><a href="" class="controlpanelheader">Batch Admin</a></h3>
	<ul class="controlpanel"><li><a href="recipeadmin.php">Recipe Report Admin</a><div class="hinttext">Assign/Edit/Remove Batch Recipe Reports</div></li><li><a href="trainadmin.php">Train Admin</a><div class="hinttext">Assign/Edit/Remove Batch Trains from Departments</div></li>
	<li><a href="equipadmin.php">Equipment Admin</a><div class="hinttext">Assign/Edit/Remove Batch Equipment from Departments</div></li>';
	if (fCanSee(@$_SESSION['permissions']['page'][4] >= 300)) {
		$stdOut .= '<li><a href="managebatch.php">Batch Maintenance</a><div class="hinttext">Close Orphaned Batches.</div></li>';
	};
	$stdOut .= '</ul>';
};
if (fCanSee(@$_SESSION['permissions']['page'][12] >= 200)) {
	$stdOut .= '<h3><a href="" class="controlpanelheader">Process Alarm Admin</a></h3>
	<ul class="controlpanel"><li><a href="alarmadmin.php">Alarm Group Admin</a><div class="hinttext">Assign/Edit/Remove Alarm Groups from Departments</div></li>';
	if (fCanSee(@$_SESSION['permissions']['page'][12] >= 300)) {
		$stdOut .= '<li><a href="managealarms.php">Alarm Maintenance</a><div class="hinttext">Delete Orphaned Alarmed.</div></li>';
	};
	$stdOut .= '</ul>';
};
	$stdOut .= '<h3><a href="" class="controlpanelheader">Process Trends Admin</a></h3>
	<ul class="controlpanel">
	<li><a href="manageprocesstrend.php">Manage Process Trends</a><div class="hinttext">Change/Create/Share/Delete one of your Process Trends or use a Shared/Public  Process Trend as a template for your own.</div></li>
	</ul>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		var controlpanel = $(".controlpanel");
		controlpanel.css("display", "none");
		$(".controlpanelheader").click(function() {
			var clickedHelp = $(this).parent().next();
			if (clickedHelp.css("display") == "block") {
				clickedHelp.css("display", "none");
			} else {
				clickedHelp.css("display", "block");
			};
			return false;
		});
	});
</script>';
require_once 'includes/footer.php'; ?>