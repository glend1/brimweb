<?php 
$title = 'Test';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};

phpinfo(); 
require_once 'includes/footer.php'; 
?>