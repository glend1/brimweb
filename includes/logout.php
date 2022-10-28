<?PHP
session_start();
if (isset($_COOKIE['id']) || isset($_SESSION['id'])) {
	$userName = $_SESSION['user'];
	setcookie('id', '', time() - 1);
	session_destroy();
	session_start();
	$_SESSION['sqlMessage'] = 'Goodbye, ' . $userName . '!';
	$_SESSION['uiState'] = 'active';
};
header('Location:' . $_SERVER['HTTP_REFERER']); ?>