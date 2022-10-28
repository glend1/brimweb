<?php
$title = 'Database Address Group';
require_once 'includes/header.php';
$stdOut .= '<table class="records"><thead><tr><th>Address Group</th><th>User</th></thead></tbody>';
$queryEvent = 'select addressgroup, name 
		from J_AddressesGroup
		join users on userfk = users.id
		order by addressgroup asc';
$dataEvent = odbc_exec($conn, $queryEvent);
while(odbc_fetch_row($dataEvent)) {
	$table[odbc_result($dataEvent, 1)][] = odbc_result($dataEvent, 2); 
}
$queryEvent = 'select addressgroup, users.name
		from J_AddressesGroup
		join groupjunction on groupid = groupfk
		join users on userid = users.id
		order by addressgroup asc';
$dataEvent = odbc_exec($conn, $queryEvent);
while(odbc_fetch_row($dataEvent)) {
	$table[odbc_result($dataEvent, 1)][] = odbc_result($dataEvent, 2);
}
$i = 0;
foreach ($table as $key => $array) {
	foreach ($array as $user) {
		if ($i % 2 == 0) {
			$stdOut .= '<tr class="oddRow">';
		} else {
			$stdOut .= '<tr class="evenRow">';
		};
		$i++;
		$stdOut .= '<td>' . $key . '</td><td>' . $user . '</td></tr>';
	};
};
$stdOut .= '</tbody></table>';
require_once 'includes/footer.php'; ?>