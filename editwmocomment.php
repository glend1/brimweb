<?PHP 
$title = 'Edit WMO Comment';
require_once 'includes/header.php';
if (!isset($_SESSION['id'])) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (!isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'Form incomplete!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$queryGetComment = 'select top 1 userfk, comment from wmocomment where id = ' . $_GET['id'];
$dataGetComment = odbc_exec($conn, $queryGetComment);
if (odbc_fetch_row($dataGetComment)) {
	if (!fCanSee($_SESSION['id'] == odbc_result($dataGetComment, 1))) {
		$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
		$_SESSION['uiState'] = 'error';
		fRedirect();
	};
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			if ($("#bodybody").find("iframe").contents().find("body").text() == "") {
				bValid = false;
				updateTips("The body must contain text");
				$("#bodybody").addClass( "ui-state-error" );
			};
			if (bValid == false) {
				return false;
			};
		});
	});
</script>
<form class="subjectbody" action="includes/processwmocomment.php" method="post">';
if (isset($_GET['id'])) {
	$stdOut .= '<input type="hidden" name="id" value="' . $_GET['id'] . '" />';
};

$stdOut .= '<label for="comment"><h3>Edit Comment</h3></label>
<textarea name="comment" id="comment">';
if (isset($_GET['id'])) {
	while($commentOut = odbc_result($dataGetComment, 2)) {
		$stdOut .= $commentOut;
		unset($commentOut);
	};
};
$stdOut .= '</textarea>
<input type="submit" class="validatetextbutton" name="edit" value="Save!" /></form>';
require_once 'includes/footer.php'; ?>