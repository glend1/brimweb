<?PHP $title = 'Manual WMO Submission';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['type'])) {
	$_SESSION['sqlMessage'] = 'Type missing!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryGetTypes = 'select top 1 id, description from wmotype where id = ' . $_GET['type'];
$dataGetTypes = odbc_exec($conn, $queryGetTypes);
$sViewType = '';
$sManualType = '';
if (!odbc_fetch_row($dataGetTypes)) {
	$_SESSION['sqlMessage'] = 'Type not found!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<form action="includes/changewmo.php" method="post">
<input type="hidden" name="version" value=\'' . $version . '\'/>
<h3><label for="foreignwmo" >Frontline ID</label></h3><input id="foreignwmo" type="text" name="ForeignWMO"/>
<h3><label for="priority" >Priority<span class="required"> * </span></label></h3><select id="manualpriority" name="priority"><option value="none">None</option>';
$queryWmoPri = 'select id, description from wmoprioritycode where typefk = ' . $_GET['type'] . ' order by description asc';
$dataWmoPri = odbc_exec($conn, $queryWmoPri);
while (odbc_fetch_row($dataWmoPri)) {
	$stdOut .= '<option value="' . odbc_result($dataWmoPri, 1) . '">' . odbc_result($dataWmoPri, 2) . '</option>';
};
$stdOut .= '</select>
<h3><label for="comment" >Comment</label><span class="required"> * </span></h3>
<textarea name="comment" id="comment"></textarea>
<div class="bug-notif">
<span class="subscribe-button"><label for="subscribe-manual">Subscribe:</label> <input name="subscribe" checked type="checkbox" id="subscribe-manual"/></span>
</div>
<div class="centersubmit"><input id="manualbug" type="submit" name="add" value="Submit!" /></div><div class="requiredhint"><span class="required"> * </span>are required fields.</div>
<script type="text/javascript">
$("#manualbug").click(function() {
	var bValid = true;
	$("input, select").removeClass( "ui-state-error" );
	bValid = bValid && checkOptionSelected($("#manualpriority"), "Priority must be selected.");
	bValid = bValid && checkTextBox($("#bodybody"), "The Comment must contain text.");
	if (bValid == false) {
		return false;
	};
});
</script
</form>';
require_once 'includes/footer.php'; ?>