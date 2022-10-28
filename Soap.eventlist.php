<?php
$title = 'Database Event List';
require_once 'includes/header.php';
$queryEvent = 'select j_event.id, attachmentfk, j_eventTypes.name, j_Type.name, eventfk 
		from j_event
		join j_eventTypes on eventtypesfk = j_eventTypes.id
		join j_Type on typefk = j_Type.id
		order by id asc';
$dataEvent = odbc_exec($conn, $queryEvent);
$stdOut .= '<table class="records"><thead><tr><th>Ref ID</th><th>Attachment/Trigger</th><th>Attachment/Trigger Type</th><th>Event Type</th><th>Event ID</th></thead></tbody>';
$i = 0;
while(odbc_fetch_row($dataEvent)) {
	if ($i % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$i++;
	$stdOut .= '<td>' . odbc_result($dataEvent, 1) . '</td>
	<td><a href="soap.attachment.php?attachment=' . odbc_result($dataEvent, 3) . '&id=' . odbc_result($dataEvent, 2) . '">' . odbc_result($dataEvent, 2) . '</a></td>
	<td>' . odbc_result($dataEvent, 3) . '</td>
	<td>' . odbc_result($dataEvent, 4) . '</td>
	<td><a href="soap.event.php?type=' . odbc_result($dataEvent, 4) . '&id=' . odbc_result($dataEvent, 5) . '">' . odbc_result($dataEvent, 5) . '</a></td></tr>';
}
$stdOut .= '</tbody></table>';
require_once 'includes/footer.php'; ?>