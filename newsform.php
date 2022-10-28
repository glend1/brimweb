<?PHP 
$title = 'News';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	$querySelect = 'select top 1 title, news from news where id = ' . $_GET['id'];
	$dataSelect = odbc_exec($conn, $querySelect);
	odbc_fetch_row($dataSelect);
};
$stdOut .= '<script type="text/javascript">
	$(function() {
		$(".validatetextbutton").click(function() {
			var bValid = true;
			$(".ui-state-error").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( $("#title"), "title", 1, 255 );
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
<form class="subjectbody" action="includes/processnews.php" method="post">';
if (isset($_GET['id'])) {
	$stdOut .= '<input type="hidden" name="id" value="' . $_GET['id'] . '" />';
};
$stdOut .= '<label for="title"><h3>Title</h3></label>
<input type="text" id="title" name="title"'; 
if (isset($_GET['id'])) {
	$stdOut .= ' value="' . odbc_result($dataSelect, 1) . '" ';
};
$stdOut .= '/>
<label for="body"><h3>Body</h3></label>
<textarea name="body" id="comment">';
if (isset($_GET['id'])) {
	while($commentOut = odbc_result($dataSelect, 2)) {
		$stdOut .= $commentOut;
		unset($commentOut);
	};
};
$stdOut .= '</textarea>
<input type="submit" class="validatetextbutton" value="Save!" /></form>';
require_once 'includes/footer.php'; ?>