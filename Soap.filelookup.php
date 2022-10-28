<?php
$title = 'Database SQL Events';
require_once 'includes/header.php';
$queryEvent = 'select id, tablecol, query, path
		from J_EventFileSql
		order by id asc';
$dataEvent = odbc_exec($conn, $queryEvent);
$stdOut .= '<table class="records"><thead><tr><th>ID</th><th>Database</th><th>Query</th><th>Path</th></thead></tbody>';
$i = 0;
while(odbc_fetch_row($dataEvent)) {
	if ($i % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$i++;
	$stdOut .= '<td>' . odbc_result($dataEvent, 1) . '</td>
	<td>' . odbc_result($dataEvent, 2) . '</td>
	<td>' . odbc_result($dataEvent, 3) . '</td>
	<td>' . odbc_result($dataEvent, 4) . '</td></tr>';
};
$stdOut .= '</tbody></table>';
require_once 'includes/footer.php'; ?>