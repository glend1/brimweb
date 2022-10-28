<?php
$title = 'Database Trigger/Attachement';
require_once 'includes/header.php';
if (!isset($_GET['attachment']) || !isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'An Attachment and ID must be set!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
} else {
	switch ($_GET['attachment']) {
		case 'Scheduler':
			$queryAttachment = 'select top 1 id, name, scheduledrun, interval from J_SchedEvent where id = ' . $_GET['id'];
			$dataAttachment = odbc_exec($conn, $queryAttachment);
			while(odbc_fetch_row($dataAttachment)) {
			$stdOut .= '<ul><li>ID: ' . odbc_result($dataAttachment, 1) . '</li>
					<li>Run Date/Time: ' . odbc_result($dataAttachment, 2) . '</li>
					<li>Interval: ' . odbc_result($dataAttachment, 3) . '</li></ul>';				
			};
			break;
		case 'IO':
			$queryAttachment = 'select top 1 J_StructureEvent.id, j_device.name, address, operator, condition
					from J_StructureEvent
					join j_data on datafk = J_Data.id
					join j_device on devicefk = j_device.name
					where J_StructureEvent.id = ' . $_GET['id'];
			$dataAttachment = odbc_exec($conn, $queryAttachment);
			while(odbc_fetch_row($dataAttachment)) {
			$stdOut .= '<ul><li>ID: ' . odbc_result($dataAttachment, 1) . '</li>
					<li>Device: ' . odbc_result($dataAttachment, 2) . '</li>
					<li>Address: ' . odbc_result($dataAttachment, 3) . '</li>
					<li>Operator: ' . odbc_result($dataAttachment, 4) . '</li>
					<li>Condition: ' . odbc_result($dataAttachment, 5) . '</li></ul>';				
			};
			break;
		default:
			$_SESSION['sqlMessage'] = 'Not a valid Attachment!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
	};
};
require_once 'includes/footer.php'; ?>