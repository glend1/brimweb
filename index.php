<?PHP 
$title = 'Brimweb';
require_once 'includes/header.php';
$stdOut .= '<div id="features">
<h2>Feature List</h2>
<h3>Private</h3>
<ul>
<li>Control over Configuration of Brimweb.</li>
<li>Viewing specific select datasets. e.g. Viewing only Iron Bullion proccess data</li>
<li>Basic messaging/notification system.</li>
<li>Full Brimweb WMO system access</li>
<ul>
<li>Creation</li>
<li>Voting</li>
<li>Commenting</li>
</ul>
</ul>
<h3>Public</h3>
<ul>
<li>Batch detail and data visualization
<ul><li>Cap9/Cap14 Report</li></ul>
</li>
<li>Alarm detail and data visualization</li>
<li>Process Trends</li>
<li>Static Reports</li>
<ul>
<li>Daily enviromental</li>
<li>Daily effluent</li>
<li>Gas & Oxygen consumption</li>
<li>Arc usage</li>
</ul>
<li>Basic Brimweb WMO creation.</li>
</ul>
</div>';
$newsItems = 3;
$queryNewsTotal = 'select COUNT(id) as pages from news';
$dataNewsTotal = odbc_exec($conn, $queryNewsTotal);
odbc_fetch_row($dataNewsTotal);
$newsTotalPages = ceil((odbc_result($dataNewsTotal, 1) + 1) / $newsItems);
if (isset($_GET['p'])) {
	$currentPage = $_GET['p'];
} else {
	$currentPage = 1;
};
if ($currentPage == 1) {
	$common = '<div id="tasks"><h3>Common Tasks</h3><ul>';
	$queryTask = 'select distinct html from tasks left join taskjunction on tasks.id = taskfk ';
	if (isset($_SESSION['id'])) {
		if (isset($_SESSION['permissions']['group'])) {
			$queryTask .= 'where ' . fOrThemReturn($_SESSION['permissions']['group'], 100, 'groupfk');
		} else {
			unset($queryTask);
		};
	} else {
		$queryTask .= 'where tasks.id = 3';
	};
	$displayCommon = false;
	if (isset($queryTask)) {
		$dataTask = odbc_exec($conn, $queryTask);
		while (odbc_fetch_row($dataTask)) {
			$displayCommon = true;
			$common .= '<li>' . odbc_result($dataTask, 1) . '</li>';
		};
		$common .= '</ul></div>';
		if ($displayCommon) {
			$stdOut .= $common;
		};
	};
	$stdOut .= '<h3>What is Brimweb?</h3>
	<div class="news">
	<p>Brimweb is a web based platform for, but not limited to, process data visualization and automation(BrimJava). Designed, maintained & written by Glen Dovey of Cogent Automation for Johnson Matthey Brimsdown. Brimweb runs on an Apache HTTP Server, utilizing MSSQL 2008, HTML5, CSS3, JavaScript(JQuery+Flot) and PHP. BrimJava runs on a GlassFish Oracle Server exclusively written in Java that uses a Metro Web Service Processor.</p></div>';
};
$queryNews = 'select timestamp, title, news 
from (
	select timestamp, title, news, ROW_NUMBER() over (order by timestamp desc) as sortorder
	from news
) as newsorder
where sortorder between ' . (($currentPage * $newsItems) - $newsItems) . ' and ' . (($currentPage * $newsItems) - 1) . '
order by Timestamp desc';
$dataNews = odbc_exec($conn, $queryNews);
while (odbc_fetch_row($dataNews)) {
	$localTime = date_create(date(odbc_result($dataNews, 1)));
	//print(date_format($localTime, 'Y-m-d H:i:s')); UTC // actually GMT/BST
	//date_timezone_set($localTime, timezone_open('Europe/London'));
	//print(date_format($localTime, 'Y-m-d H:i:s')); GMT/BST // actually UTC
	$stdOut .= '<h3>' . odbc_result($dataNews, 2) . '</h3><div class="timestamp">' . date_format($localTime, 'Y-m-d h:i:s A') . '</div><div class="news">' . odbc_result($dataNews, 3) . '</div>';
};
if ($currentPage < $newsTotalPages) {
	$stdOut .= '<a class="next-news" href="index.php?p=' . ($currentPage + 1) . '">Next Page</a>';
};
if ($currentPage > 1) {
	$stdOut .= '<a class="previous-news" href="index.php?p=' . ($currentPage - 1) . '">Previous Page</a>';
};
require_once 'includes/footer.php'; ?>