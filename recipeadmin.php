<?PHP 
// select Batch_Log_ID, Campaign_ID, Lot_ID, recipe_id, Recipe_Version, Train_id, Log_Close_DT from batchidlog
$title = 'Recipe Report Admin';
require_once 'includes/header.php';
if (!fCanSee(@$_SESSION['permissions']['page'][4] >= 200)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$hookReplace['searchicon'] = '<a href="#" data-text="Search" class="menucontext"><span class="icon-search icon-hover-hint icon-large"></span></a>';
$hookReplace['searchform'] = '<form id="search" action="recipeadmin.php" method="get">';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= '<a href="recipeadmin.php"><span class="icon-remove-sign icon-large"></span></a> ';
};
$hookReplace['searchform'] .= '<input name="search" type="text"';
if (isset($_GET['search'])) {
	$hookReplace['searchform'] .= ' value="' . $_GET['search'] . '" ';
};
$hookReplace['searchform'] .= '/><input type="submit" value="Search!" /></form>';
$bConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=BatchHistory;', $dbUsername, $dbPassword);
$queryRecipe = 'select distinct recipe_id from (select train_id, recipe_id from (select distinct recipe_id, Train_ID
from [BatchHistory].[dbo].[batchidlog]
group by recipe_id, Train_ID
union
select recipe_id, train_id
from [oldBatchHistory].[dbo].[batchidlog]
group by recipe_id, train_id) as ttable) as tttable
join train on train = train_id';
if ($_SESSION['id'] != 1) {
	fOrThem($_SESSION['permissions']['department'], 200, 'departmentfk', $aQueryRecipe);
	fGenerateWhere($queryRecipe, $aQueryRecipe);
};
if (isset($_GET['search'])) {
	if ($_SESSION['id'] != 1) {
		$queryRecipe .= ' and recipe_id like \'%' . $_GET['search'] . '%\'';
	} else {
		$queryRecipe .= ' where recipe_id like \'%' . $_GET['search'] . '%\'';
	};
};
$queryRecipe .= ' order by recipe_id';
$dataRecipe = odbc_exec($conn, $queryRecipe);
while(odbc_fetch_row($dataRecipe)) {
	$aRecipe[odbc_result($dataRecipe, 1)] = true;
};
function fRecipeSelect($exclude = NULL, $selected = NULL) {
	GLOBAL $aRecipe;
	$out = '<select name="recipe"><option>None</option>';
	foreach ($aRecipe as $key => $value) {
		$out .= '<option>' . $key . '</option>';
	};
	$out .= '</select>';
	return $out;
};
$stdOut .= '<form class="createform" action="recipereport.php" method="post"><h3>Create Recipe Report</h3>
<input class="validatetextbutton" type="submit" name="add" value="Create"></form>';
$stdOut .= '<table class="recordssmall"><thead><tr><th>Reports</th><th>Recipes</th></tr></thead><tbody>';
$row = 0;
	/*if ($row % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$row++;
	$stdOut .= '<td></td><td>' . odbc_result($dataRecipe, 1) . '</td>';*/
$stdOut .= '</tbody></table>';
$stdOut .= '<div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] = '';
if (fCanSee(isset($_SESSION['edit']['departmentedit']))) {
	$hookReplace['help'] .= '<div class="notice">To Create a Department Equipment <a href="managedepartmentequipment.php">Click Here</a>.</div>';
};
$hookReplace['help'] .= $helptext['search'] . $helptext['update'] . $helptext['delete'] . $helptext['add'];
odbc_close($bConn);
require_once 'includes/footer.php'; ?>