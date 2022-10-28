<?PHP
require_once 'secrets.php';
if (!isset($_GET['filter'])) {
	$output['status'] = 'no Filter found.';
	$error = true;
};
if (!isset($error)) {
	$rConn = odbc_connect('DRIVER={SQL Server};Server=INSQL2;Database=runtime;', $dbUsername, $dbPassword);
	$queryTags = 'select TagName, tag.Description, ComputerName, TagTypeName, Topic.Name 
	from tag
	join IOServer on IOServer.IOServerKey = Tag.IOServerKey
	join Topic on Topic.TopicKey = Tag.TopicKey
	join TagType on TagType.TagTypeKey = Tag.TagType
	where TagTypeKey < 3 and (tag.description like \'%' . $_GET['filter'] . '%\' or tag.tagname like \'%' . $_GET['filter'] . '%\')';
	if (isset($_GET['exclude'])) {
		$sExcludeTags = '(';
		$excludeSep = '';
		foreach ($_GET['exclude'] as $key => $value) {
			$sExcludeTags .= $excludeSep . 'tagname <> \'' . $value . '\'';
			$excludeSep = ' and ';
		};
		$queryTags .= ' and ' . $sExcludeTags . ')';
	};
	$dataTags = odbc_exec($rConn, $queryTags);
	$return = '';
	$noTag = true;
	while(odbc_fetch_row($dataTags)) {
		$noTag = false;
		$return .= utf8_encode('<li data-desc="' . odbc_result($dataTags, 2) . '" data-type="' . odbc_result($dataTags, 4) . '"><a href="#"><span class="icon-plus"></span></a><div class="tagname">' . odbc_result($dataTags, 1) . '</div>');
		if (odbc_result($dataTags, 2)) {
			$return .= utf8_encode('<div class="hinttext">' . odbc_result($dataTags, 2) . '</div>');
		};
		$return .= utf8_encode('</li>');
	};
	if ($noTag) {
		$output['status'] = 'no Tag by that name found.';
	} else {
		$output['oreturn'] = $return;
		$output['status'] = 'complete';
	};
	odbc_close($rConn);
};
print(json_encode($output));
?>