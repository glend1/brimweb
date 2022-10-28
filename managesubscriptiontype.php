<?PHP 
$title = 'Manage Subscription Types';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="managesubscriptiontype.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="managesubscriptiontype.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'sname\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "subscription type", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$querySubscription = 'select id, Name from SubscriptionType';
if (isset($_GET['search'])) {
	$querySubscription .= ' where name like \'%' . $_GET['search'] . '%\'';
};
$dataSubscription = odbc_exec($conn, $querySubscription);
$sSubscriptionForms = '<form class="createform" action="includes/changesubscriptiontype.php" method="post"><h3>Create Subscription Type</h3>
<div class="formelement"><label for="sname">Subscription Name</label><br /><input type="text" id="sname" name="sname" value=""><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><tr><th>Rename Subscription Type</th><th>Delete Subscription Type</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataSubscription)) {
	if ($row % 2 == 0) {
		$sSubscriptionForms .= '<tr class="oddRow">';
	} else {
		$sSubscriptionForms .= '<tr class="evenRow">';
	};
	$row++;
	$sSubscriptionForms .= '<td><form action="includes/changesubscriptiontype.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataSubscription, 1) . '"><input type="text" name="sname" value="' . odbc_result($dataSubscription, 2) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changesubscriptiontype.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataSubscription, 1) . '"><input type="submit" value="Delete"></form></td>
	</tr>';
};
$sSubscriptionForms .= '</tbody></table>';
$stdOut .= $sSubscriptionForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>