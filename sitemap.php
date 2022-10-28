<?PHP 
$title = 'Site Map';
require_once 'includes/header.php';
$d = dir(substr($_SERVER['SCRIPT_FILENAME'], 0, -11));
$stdOut .= '<ul>';
while (false !== ($entry = $d->read())) {
	if (substr($entry, -4) == '.php') {
		$stdOut .= '<li><a href="' . $entry . '">' . substr($entry, 0, -4) . '</a></li>';
	};
}
$stdOut .= '</ul>';
$d->close();
require_once 'includes/footer.php'; ?>