<?PHP 
$title = 'Manage Pages';
require_once 'includes/header.php';
if (!fCanSee(false)) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryPagesAdmin = 'select id, Name, path, parentid, "order", defaultpermissions from Pages order by "order"';
$dataPagesAdmin = odbc_exec($conn, $queryPages);;
$pagesRefs = array();
$pagesList = array();
$aPages = array();
while (odbc_fetch_row($dataPagesAdmin)) {
	$thisref = &$pagesRefs[ odbc_result($dataPagesAdmin, 1) ];
	$aPages[odbc_result($dataPagesAdmin, 1)] = odbc_result($dataPagesAdmin, 2);
	$thisref['parent_id'] = odbc_result($dataPagesAdmin, 4);
	$thisref['name'] = odbc_result($dataPagesAdmin, 2);
	$thisref['path'] = odbc_result($dataPagesAdmin, 3);
	$thisref['order'] = odbc_result($dataPagesAdmin, 5);
	$thisref['id'] = odbc_result($dataPagesAdmin, 1);
	$thisref['permissions'] = odbc_result($dataPagesAdmin, 6);
	if (odbc_result($dataPagesAdmin, 4) == 0) {
		$pagesList[ odbc_result($dataPagesAdmin, 1) ] = &$thisref;
	} else {
		$pagesRefs[ odbc_result($dataPagesAdmin, 4) ]['children'][ odbc_result($dataPagesAdmin, 1) ] = &$thisref;
	}
};		
function fPagesTable($menu) {
	$item = array();
	if (is_array($menu)) {
		foreach ($menu as $key => $array) {
			if (isset($array['name'])) {
				$item[] = '<div>' . $array['id'] . '<form action="includes/changepages.php" method="post">
				<input type="hidden" name="update" value="' . $array['id'] . '" />
				<input type="text" name="name" value="' . $array['name'] . '" /><span class="required"> * </span>
				<input type="text" name="path" value="' . $array['path'] . '" /><span class="required"> * </span>
				' . fPagesOptionParent($array['parent_id'], $array['id']) . '
				<input type="text" name="order" value="' . $array['order'] . '" /><span class="required"> * </span>
				' . fPagesOptionPermission($array['permissions']) . '
				<input class="validatetextbutton" type="submit" value="Update!" /></form>
				<form action="includes/changepages.php" method="post">
				<input type="hidden" name="delete" value="' . $array['id'] . '" />
				<input type="submit" value="Delete!" /></form>';
				if (isset($array['children'])) {
					$item[] .= '<span class="icon-plus icon-large"></span><span class="icon-minus icon-large"></span><div class="headerdiv">ID/Name/URL/Parent/Order/Default Level</div>' . fPagesTable($array['children']) . '</div>';
				} else {
					$item[] .= '</div>';
				};
				$item[] .= '';
			};
		};
	};
	return '' . implode($item) . '';
};
function fPagesOptionParent ($select = false, $exclude = false) {
	global $aPages;
	$sOptions = '<select name="parent"><option value="0"';
	if ($select == 0) {
		$sOptions .= ' selected ';
	};
	$sOptions .= '>None</option>';
	foreach ($aPages as $key => $value) {
		if ($exclude != $key) {
			$sOptions .= '<option value="' . $key . '"';
			if ($select == $key) {
				$sOptions .= ' selected ';
			};
			$sOptions .= '>' . $value . '</option>';
		};
	};
	$sOptions .= '</select>';
	return $sOptions;
};
function fPagesOptionPermission ($select) {
	$sOptions = '<select name="permissions">';
	$aPermissions = [[0,'None'],[100,'View'],[200,'Edit'],[300,'Admin']];
	foreach ($aPermissions as $key => $array) {
		$sOptions .= '<option value="' . $array[0] . '"';
		if ($select == $array[0]) {
			$sOptions .= ' selected ';
		};
		$sOptions .= '>' . $array[1] . '</option>';
	};
	$sOptions .= '</select>';
	return $sOptions;
};
//remove me
$stdOut .= '<style> .icon-plus, .icon-minus  {margin-left:10px;cursor:pointer;}</style>';
//remove me
$stdOut .= '<script type="text/javascript">
$(function() {
	$(".validatetextbutton").click(function() {
		fields = $(this).prevAll("input");
		var bValid = true;
		$("input").removeClass( "ui-state-error" );
		bValid = bValid && checkLength( fields.eq(2), "name", 1, 60 );
		bValid = bValid && checkLength( fields.eq(1), "path", 1, 60 );
		bValid = bValid && checkRegexp( fields.eq(0), /^[1234567890]{1,}$/ ,"order must be number");
		if (bValid == false) {
				return false;
		};
	});
	
	$(".icon-plus").click(function() {
		$(this).css("display", "none");
		$(this).next(".icon-minus").css("display", "inline");
		$(this).nextAll("div").css("display", "block");
	});
	
	$(".icon-minus").click(function() {
		$(this).css("display", "none");
		$(this).prev(".icon-plus").css("display", "inline");
		$(this).nextAll("div").css("display", "none");
	});
});
</script>
<div class="divpages"><div class="headerdiv">ID/Name/URL/Parent/Order/Default Level</div>' . fPagesTable($pagesList)
 . '</div><div class="divpages"><div class="headerdiv">Name/URL/Parent/Order/Default Level</div>
 <div><form action="includes/changepages.php" method="post">
	<input type="text" name="name" value="" /><span class="required"> * </span>
	<input type="text" name="path" value="" /><span class="required"> * </span>
	' . fPagesOptionParent() . '
	<input type="text" name="order" value="" /><span class="required"> * </span>
	' . fPagesOptionPermission(0) . '
	<input class="validatetextbutton" type="submit" value="Add!" name="add"/></form></div></div><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
	$hookReplace['help'] = $helptext['update'] . $helptext['delete'] . $helptext['add'];
require_once 'includes/footer.php'; ?>