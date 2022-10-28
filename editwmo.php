<?PHP $title = 'Edit WMO';
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
$stdOut .= '<h2>Editing WMO #' . $_GET['id'] . '</h2>';
$queryWmo = 'select top 1 PermissionAbstractFK, ForeignWMOFK, PriorityFK, SameAsFK, statusfk from wmo where ID = ' . $_GET['id'];
$dataWmo = odbc_exec($conn, $queryWmo);
if (!odbc_fetch_row($dataWmo)) {
	$_SESSION['sqlMessage'] = 'WMO not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$rowId = 1;
function checkOdd(&$row) {
	if ($row % 2 == 0) {
		$row++;
		return 'oddRow';
	} else {
		$row++;
		return 'evenRow';
	};
};
$stdOut .= '<form action="includes/changewmo.php" class="inlineclass ' . checkOdd($rowId) . '" method="post">
<h3>Update</h3>
<input type="hidden" name="id" value="' . $_GET['id'] . '"/>
<div class="formelement"><label for="sameas">Parent WMO:</label><br />
<input type="text" id="sameas" name="SameAs" value="' . odbc_result($dataWmo, 4) . '" id="sameas"/></div>
<div class="formelement"><label for="foreignwmo">Foreign WMO:</label><br />
<input type="text" name="ForeignWMO" value="' . odbc_result($dataWmo, 2) . '" id="foreignwmo"/></div>
<div class="formelement"><label for="page">Page Name:</label><br />
<select name="script" id="page"><option value="">None</option>';
$queryAbstract = 'select id, abstractname from PermissionAbstract where PermissionFK <> 999 order by abstractname';
$dataAbstract = odbc_exec($conn, $queryAbstract);
while (odbc_fetch_row($dataAbstract)) {
	$stdOut .= '<option ';
	if (odbc_result($dataWmo, 1) == odbc_result($dataAbstract, 1)) {
		$stdOut .= 'selected ';
	};
	$stdOut .= 'value="' . odbc_result($dataAbstract, 1) . '">' . odbc_result($dataAbstract, 2) . '</option>';
};
$stdOut .= '</select></div>
<div class="formelement"><label for="priority">Priority:</label><br />
<select name="priority" id="priority"><option value="">None</option>';
$queryPriority = 'select id, description from WMOPriorityCode order by description';
$dataPriority = odbc_exec($conn, $queryPriority);
while (odbc_fetch_row($dataPriority)) {
	$stdOut .= '<option ';
	if (odbc_result($dataWmo, 3) == odbc_result($dataPriority, 1)) {
		$stdOut .= 'selected ';
	};
	$stdOut .= 'value="' . odbc_result($dataPriority, 1) . '">' . odbc_result($dataPriority, 2) . '</option>';
};
$stdOut .= '</select></div>
<input class="validatetextbutton" type="submit" name="update" value="Update!">
</form>
<form action="includes/changewmo.php" class="inlineclass ' . checkOdd($rowId) . '" method="post">
<h3>Delete</h3>
<input type="hidden" name="id" value="' . $_GET['id'] . '" />
<input type="submit" name="delete" value="Delete!">
</form>';
if (odbc_result($dataWmo, 5) != 4) {
	$stdOut .= '<form action="includes/changesinglewmostatus.php" class="inlineclass ' . checkOdd($rowId) . '" method="post">
	<h3>Status: In Progress</h3>
	<input type="hidden" name="id" value="' . $_GET['id'] . '" />
	<div class="formelement"><label for="esttime">Estimated Time (Sec):</label><span class="required"> * </span><br />
	<input type="text" name="sec" id="esttime"/></div>
	<input type="hidden" name="status" value="wip" />
	<input id="wmostatusbutton" type="submit" value="In Progress!">
	</form>';
};
if (odbc_result($dataWmo, 5) != 2) {
	$stdOut .= '<form action="includes/changesinglewmostatus.php" class="inlineclass ' . checkOdd($rowId) . '" method="post">
	<h3>Status: Close</h3>
	<input type="hidden" name="id" value="' . $_GET['id'] . '" />
	<input type="hidden" name="status" value="close" />
	<input type="submit" value="Close!">
	</form>';
};
if (odbc_result($dataWmo, 5) != 3) {
	$stdOut .= '<form action="includes/changesinglewmostatus.php" class="inlineclass ' . checkOdd($rowId) . '" method="post">
	<h3>Status: Open</h3>
	<input type="hidden" name="id" value="' . $_GET['id'] . '" />
	<input type="hidden" name="status" value="open" />
	<input type="submit" value="Open!">
	</form>';
};
$stdOut .= '<script type="text/javascript">
$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var sameAs = $("#sameas")
			$("input").removeClass( "ui-state-error" );
			if (sameAs.val() != "") {
				bValid = bValid && checkRegexp( sameAs, /^[0-9]+$/, "Parent WMO must be Numeric." );
			};
			if (bValid == false) {
				return false;
			};
		});
		$("#wmostatusbutton").click(function() {
			var bValid = true;
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkRegexp( $("#esttime"), /^[0-9]+$/, "Estimated Time must be in Seconds." );
			if (bValid == false) {
				return false;
			};
		});
	});
</script><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
require_once 'includes/footer.php'; ?>