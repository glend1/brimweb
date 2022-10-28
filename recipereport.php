<?PHP 
$title = 'Recipe Report';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][4] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '<form>
<h3>Title</h3>
<label for="name">Name:</label> <input type="text" id="name" name="name"/>
<h3>Conditions</h3>
<div class="row-condition" data-condition="0">
<label for="type0">Type:</label> <select id="type0" name="condition[0][\'type\']"><option>None</option><option><</option><option>></option><option>=</option></select> <label for="value0">Value:</label> <input type="text" id="value0" name="condition[0][\'value\']"/><input class="remove-condition" type="button" value="Remove" /></div>
<input id="add-condition" type="button" value="Add" />
<h3>Options</h3>
<label for="option0">Option:</label> <input id="option0" type="text" name="option"/>
<div class="centersubmit"><input type="submit" value="Submit!"/></div>
</form>
<script type="text/javascript">
$(function() {
	$("#add-condition").click(function() {
		val = $(".row-condition").last().data("condition") + 1;
		console.log(val);
		row = $(\'<div class="row-condition" data-condition="\' + val + \'"><input type="hidden" /><label for="type\' + val + \'">Type:</label> <select id="type\' + val + \'" name="condition[\' + val + \'][\\\'type\\\']"><option>None</option><option><</option><option>></option><option>=</option></select> <label for="value\' + val + \'">Value:</label> <input type="text" id="value\' + val + \'" name="condition[\' + val + \'][\\\'value\\\']"/><input class="remove-condition" type="button" value="Remove" /></div>\');
		$(this).before(row);
		$(row).click(function() {
			$(this).remove();
		});
		console.log(row);
	});
	$(".remove-condition").click(function() {
		$(this).parent().remove();
	});
});
</script>';
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>