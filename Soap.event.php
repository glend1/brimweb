<?php
$title = 'Database Trigger/Attachement';
require_once 'includes/header.php';
if (!isset($_GET['type']) || !isset($_GET['id'])) {
	$_SESSION['sqlMessage'] = 'A Type and ID must be set!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
} else {
	switch ($_GET['type']) {
		case 'Email':
			$queryEvent = 'select top 1 id, addressgroup, subject, html from J_EventEmail where id = ' . $_GET['id'];
			$dataEvent = odbc_exec($conn, $queryAttachment);
			while(odbc_fetch_row($dataEvent)) {
			$stdOut .= '<ul><li>ID: ' . odbc_result($dataEvent, 1) . '</li>
					<li>Address Group: ' . odbc_result($dataEvent, 2) . '</li>
					<li>Subject: ' . odbc_result($dataEvent, 3) . '</li>
					<li>HTML: ' . odbc_result($dataEvent, 4) . '</li></ul>';				
			};
			break;
		case 'Sms':
			$queryEvent = 'select top 1 id, addressgroup, message from J_EventSms where id = ' . $_GET['id'];
			$dataEvent = odbc_exec($conn, $queryAttachment);
			while(odbc_fetch_row($dataEvent)) {
				$stdOut .= '<ul><li>ID: ' . odbc_result($dataEvent, 1) . '</li>
				<li>Run Date/Time: ' . odbc_result($dataEvent, 2) . '</li>
				<li>Interval: ' . odbc_result($dataEvent, 3) . '</li></ul>';
			};
			break;
		case 'File':
			$queryEvent = 'select top 1 id, tablecol, query, path from J_EventFileSql where id = ' . $_GET['id'];
			$dataEvent = odbc_exec($conn, $queryAttachment);
			while(odbc_fetch_row($dataEvent)) {
				$stdOut .= '<ul><li>ID: ' . odbc_result($dataEvent, 1) . '</li>
				<li>Database: ' . odbc_result($dataEvent, 2) . '</li>
				<li>Query: ' . odbc_result($dataEvent, 3) . '</li>
				<li>Path: ' . odbc_result($dataEvent, 4) . '</li></ul>';
			};
			break;
		default:
			$_SESSION['sqlMessage'] = 'Not a valid Attachment!';
			$_SESSION['uiState'] = 'error';
			fRedirect();
	};
};
require_once 'includes/footer.php'; ?>