<?PHP 
$title = 'Manage Repeat Type';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			var fields = $(this).parent();
			var validateText = fields.find("[name=\'rname\']");
			$("input").removeClass( "ui-state-error" );
			$("select").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "repeat options", 1, 60 );
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
function fSelectSelect($reference, $select, $name) {
	$out = '<select name="' . $name . '">';
	foreach ($reference as $key => $value) {
		$out .= '<option ';
		if ($key == $select) {
			$out .= 'selected';
		};
		$out .= ' value="' . $key . '">' . $value . '</option>';
	};
	$out .= '</select>';
	return $out;
};
$queryRepeat = 'select id, Name from ReportCalendarRepeat';
$dataRepeat = odbc_exec($conn, $queryRepeat);
while (odbc_fetch_row($dataRepeat)) {
	$aRepeat[odbc_result($dataRepeat, 1)] = odbc_result($dataRepeat, 2);
};
$queryType = 'select id, Name from ReportCalendarType';
$dataType = odbc_exec($conn, $queryType);
while (odbc_fetch_row($dataType)) {
	$aType[odbc_result($dataType, 1)] = odbc_result($dataType, 2);
};
$queryJoin = 'select id, ReportCalendarTypeFK, ReportCalendarRepeatFK from ReportCalendarTypeRepeat';
$dataJoin = odbc_exec($conn, $queryJoin);
$stdOut .= '<form class="createform" action="includes/changecalrepeattype.php" method="post"><h3>Create Repeat Type</h3>
<div class="formelement"><label>Type</label><br />'  .

	fSelectSelect($aType, 0, 'type') . '</div><div class="formelement"><label>Repeat</label><br />' . fSelectSelect($aRepeat, 0, 'repeat') . ' </div>


<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><th>Reassign Options</th><th>Delete Option</th></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataJoin)) {
	if ($row % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$row++;
	$stdOut .= '<td><form action="includes/changecalrepeattype.php" method="post">
	<input type="hidden" name="update" value="' . odbc_result($dataJoin, 1) . '"/><label>Type:</label> ' . 
	fSelectSelect($aType, odbc_result($dataJoin, 2), 'type') . ' <label>Repeat:</label> ' . fSelectSelect($aRepeat, odbc_result($dataJoin, 3), 'repeat') . '
	<input type="submit" value="Update" />
	</form></td>
	<td>
	<form action="includes/changecalrepeattype.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataJoin, 1) . '"/>
	<input type="submit" value="Delete" />
	</form>
	</td>
	</tr>';
};
$stdOut .= '</tbody></table>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>