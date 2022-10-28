<?PHP 
$title = 'Manage OEE Categories';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][1] >= 300)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="manageoeecategory.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="manageoeecategory.php"><span class="icon-remove-sign icon-large"></span></a> ';
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
			var validateText = fields.find("[name=\'cname\']");
			var validateOrder = fields.find("[name=\'order\']");
			$("input").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( validateText, "category", 1, 60 );
			bValid = bValid && checkRegexp( validateOrder, /^[1234567890]{1,}$/ ,"order must be number");
			if (bValid == false) {
				return false;
			};
		});
	});
</script>';
$queryCategory = 'select id, Name, TypeOrder from oeecategory';
if (isset($_GET['search'])) {
	$queryCategory .= ' where name like \'%' . $_GET['search'] . '%\'';
};
$queryCategory .= ' order by typeorder asc';
$dataCategory = odbc_exec($conn, $queryCategory);
$sCategoryForms = '<form class="createform" action="includes/changeoeecategory.php" method="post"><h3>Create Category</h3>
<div class="formelement"><label for="name">Category Name</label><br /><input type="text" id="name" name="cname" value=""><span class="required"> * </span></div>
<div class="formelement"><label>Order</label><br /><input type="text" name="order"><span class="required"> * </span></div>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>
<table class="recordssmall"><thead><tr><th>Rename Category</th><th>Delete Category</th></tr></thead><tbody>';
$row = 0;
while (odbc_fetch_row($dataCategory)) {
	if ($row % 2 == 0) {
		$sCategoryForms .= '<tr class="oddRow">';
	} else {
		$sCategoryForms .= '<tr class="evenRow">';
	};
	$row++;
	$sCategoryForms .= '<td><form action="includes/changeoeecategory.php" method="post"><input type="hidden" name="update" value="' . odbc_result($dataCategory, 1) . '"><label>Name:</label><input type="text" name="cname" value="' . odbc_result($dataCategory, 2) . '"><span class="required"> * </span> <label>Order:</label><input type="text" name="order" value="' . odbc_result($dataCategory, 3) . '"><span class="required"> * </span><input class="validatetextbutton" type="submit" value="Update"></form></td>
	<td><form action="includes/changeoeecategory.php" method="post">
	<input type="hidden" name="delete" value="' . odbc_result($dataCategory, 1) . '"><input type="submit" value="Delete"></form></td></tr>';
};
$sCategoryForms .= '</tbody></table>';
$stdOut .= $sCategoryForms . '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>