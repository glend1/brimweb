<?PHP 
$title = 'Credits';
require_once 'includes/header.php';
$aLicences = ['SIL OFL 1.1' => 'http://scripts.sil.org/OFL', 'MIT' => 'http://opensource.org/licenses/mit-license.html', 'LGPL' => 'http://www.gnu.org/copyleft/lesser.html','BSD' => 'http://opensource.org/licenses/bsd-license.html', 'Apache Licence 2.0' => 'http://www.apache.org/licences/LICENSE-2.0', 'GPLv3' => 'http://www.gnu.org/licenses/gpl-3.0.html', 'EPL' => 'http://opensource.org/licenses/EPL-1.0', 'Microsoft' => 'mslicence.php', 'CC 3.0' => 'http://creativecommons.org/licenses/by/3.0/'];
$aCredits[] = ['Language' => 'ICON', 'Package' => 'FamFamFam', 'Version' => '1.3', 'Licence' => ['CC 3.0'], 'Authors' => ['Mark James'], 'Website' => 'http://famfamfam.com'];
$aCredits[] = ['Language' => 'CSS', 'Package' => 'Font Awesome', 'Version' => '3.2.1', 'Licence' => ['SIL OFL 1.1','MIT'], 'Authors' => ['Dave Gandy'], 'Website' => 'http://fontawesome.io']; 
$aCredits[] = ['Language' => 'CSS', 'Package' => 'normalize.css', 'Version' => '3.0.1', 'Licence' => ['MIT'], 'Authors' => ['Nicolas Gallagher'], 'Website' => 'http://necolas.github.io/normalize.css/']; 
$aCredits[] = ['Language' => 'PHP', 'Package' => 'PHPMailer', 'Version' => '5.2.9', 'Licence' => ['LGPL'], 'Authors' => ['Brent R. Matzelle', 'Andy Prevost', 'Jim Jagielski', 'Marcus Bointon', 'Chris Ryan'], 'Website' => 'https://github.com/PHPMailer/PHPMailer/'];
$aCredits[] = ['Language' => 'PHP', 'Package' => 'DBF reader Class', 'Version' => '0.05', 'Licence' => ['LGPL'], 'Authors' => ['Faro K Rasyid', 'Nicholas Vrtis']];
$aCredits[] = ['Language' => 'PHP', 'Package' => 'EvalMath', 'Version' => '1.0.0', 'Licence' => ['BSD'], 'Authors' => ['Miles Kaufmann'], 'Website' => 'http://www.twmagic.com/'];
$aCredits[] = ['Language' => 'PHP', 'Package' => 'Portable PHP password hashing framework', 'Version' => '0.3', 'Licence' => ['Public Domain'], 'Authors' => ['Solar Designer'], 'Website' => 'http://www.openwall.com/phpass/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'ExplorerCanvas', 'Version' => 'Release 3', 'Licence' => ['Apache Licence 2.0'], 'Authors' => ['Emil A Eklund', 'Erik Arvidsson', 'Glen Murphy'], 'Website' => 'http://www.excanvas.sourceforge.net/']; 
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'Flot', 'Version' => '0.8.1', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'colorhelpers', 'Version' => '1.1', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.dashes', 'Version' => '0.1b', 'Licence' => ['MIT'], 'Authors' => ['Michael Hixson'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.categories', 'Version' => '1.0', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.crosshair', 'Version' => '1.0', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.selection', 'Version' => '1.1', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.navigate', 'Version' => '1.3', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.stack', 'Version' => '1.2', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.time', 'Version' => '1.0', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.resize', 'Version' => '1.0', 'Licence' => ['MIT'], 'Authors' => ['Ole Laursen'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.pie', 'Version' => '1.1', 'Licence' => ['MIT'], 'Authors' => ['Brian Medendorp', 'Anthony Aragues', 'Xavi Ivars'], 'Website' => 'http://www.flotcharts.org/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.gantt', 'Version' => '0.3', 'Licence' => ['BSD'], 'Authors' => ['Juergen Marsch'], 'Website' => 'http://jumflot.jumware.com/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.mouse', 'Version' => '0.2', 'Licence' => ['BSD'], 'Authors' => ['Juergen Marsch'], 'Website' => 'http://jumflot.jumware.com/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'Axis Labels Plugin for flot', 'Version' => '2.0', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Xuan Luo'], 'Website' => 'http://github.com/markrcote/flot-axislabels'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.orderbars', 'Version' => '0.2', 'Licence' => ['MIT'], 'Authors' => ['Przemyslaw Koltermann', 'Benjamin Buffet'], 'Website' => 'http://en.benjaminbuffet.com/labs/flot/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'JQuery', 'Version' => '1.9.1', 'Licence' => ['MIT'], 'Authors' => ['https://www.jquery.org/members/'], 'Website' => 'https://jquery.org'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'JQuery UI', 'Version' => '1.10.3', 'Licence' => ['MIT'], 'Authors' => ['https://www.jquery.org/members/'], 'Website' => 'https://jqueryui.com'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'JQuery-dateFormat', 'Version' => 'May 19, 2015', 'Licence' => ['MIT'], 'Authors' => ['Pablo Cantero'], 'Website' => 'https://github.com/phstc/jquery-dateFormat'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'SCEditor', 'Version' => '1.4.5', 'Licence' => ['MIT'], 'Authors' => ['Sam Clarke'], 'Website' => 'http://www.sceditor.com/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'jQuery Timepicker Addon', 'Version' => '1.4.4', 'Licence' => ['MIT'], 'Authors' => ['Trent Richardson'], 'Website' => 'http://trentrichardson.com/examples/timepicker'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'jQuery UI Slider Access', 'Version' => '0.3', 'Licence' => ['MIT', 'GPLv3'], 'Authors' => ['Trent Richardson'], 'Website' => 'http://trentrichardson.com/examples/jQuery-SliderAccess/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'jquery.event.drag.js', 'Version' => 'v1.5', 'Licence' => ['MIT'], 'Authors' => ['Three Dub Media'], 'Website' => 'http://threedubmedia.com'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'jquery.mousewheel.min.js', 'Version' => '3.0.6', 'Licence' => ['MIT'], 'Authors' => ['Brandon Aaron'], 'Website' => 'http://brandonaaron.net'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'jQuery resize event', 'Version' => '1.1', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Ben Alman'], 'Website' => 'http://benalman.com/projects/jquery-resize-plugin/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'TableSorter', 'Version' => '2.18.2', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'TableSorter math widget', 'Version' => '2.18.2', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'TableSorter output widget', 'Version' => '2.18.2', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'tablesorter pager widget', 'Version' => '2.18.1', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'Column Selector/Responsive table widget (beta)', 'Version' => '2.18.1', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'tableSorter widgets', 'Version' => '2.18.2', 'Licence' => ['GPLv3', 'MIT'], 'Authors' => ['Christian Bach', 'Rob Garrison'], 'Website' => 'http://mottie.github.io/tablesorter/'];
//$aCredits[] = ['Language' => 'Javascript', 'Package' => 'Select2', 'Version' => '3.5.2', 'Licence' => ['GPLv3', 'Apache Licence 2.0'], 'Authors' => ['Igor Vaynberg'], 'Website' => 'http://ivaynberg.github.io/select2/'];
$aCredits[] = ['Language' => 'Javascript', 'Package' => 'flot.stackpercent', 'Version' => '0.1', 'Licence' => ['MIT'], 'Authors' => ['Skeleton9'], 'Website' => 'http://github.com/skeleton9/flot.stackpercent'];
$aCredits[] = ['Language' => 'Java', 'Package' => 'Open SCADA', 'Version' => '1.1', 'Licence' => ['EPL'], 'Authors' => ['IBH Systems GmbH'], 'Website' => 'http://openscada.org/'];
$aCredits[] = ['Language' => 'Java', 'Package' => 'Microsoft JDBC Drivers', 'Version' => '4.1', 'Licence' => ['Microsoft'], 'Authors' => ['http://www.microsoft.com/'], 'Website' => 'http://www.microsoft.com/'];
$aCredits[] = ['Language' => 'Java', 'Package' => 'JavaMail', 'Version' => '1.5', 'Licence' => ['GPLv3'], 'Authors' => ['http://www.oracle.com/'], 'Website' => 'http://www.oracle.com/'];
$aCredits[] = ['Language' => 'Java', 'Package' => 'SLF4J', 'Version' => '1.6.4', 'Licence' => ['None'], 'Authors' => ['http://www.qos.ch'], 'Website' => 'http://www.qos.ch'];
//$aCredits[] = ['Language' => 'test', 'Package' => 'test', 'Version' => 'number', 'Licence' => ['test'], 'Authors' => ['name'], 'Website' => 'url'];
$stdOut .= '<table class="records"><thead><th>Language</th><th>Package</th><th>Version</th><th>Licence</th><th>Authors</th><th>Website</th></tr></thead><tbody>';
foreach ($aCredits as $i => $row) {
	if (($i + 1) % 2 == 0) {
		$stdOut .= '<tr class="oddRow">';
	} else {
		$stdOut .= '<tr class="evenRow">';
	};
	$stdOut .= '<td>' . $row['Language'] . '</td>
	<td>' . $row['Package'] . '</td>
	<td>' . $row['Version'] . '</td><td>';
	$licSep = '';
	foreach ($row['Licence'] as $licence) {
		$stdOut .= $licSep;
		if (isset($aLicences[$licence])) {
			$stdOut .= '<a href="' . $aLicences[$licence] . '">' . $licence . '</a>';
		} else {
			$stdOut .= $licence;
		};
		$licSep = '/';
	};
	$stdOut .= '</td><td>';
	$authSep = '';
	foreach ($row['Authors'] as $author) {
		$stdOut .= $authSep;
		if (strpos($author, 'www.')) {
			$stdOut .= '<a href="' . $author . '">' . $author . '</a>';
		} else {
			$stdOut .= str_replace(' ', '&nbsp;', $author);
		};
		$authSep = ', ';
	};
	$stdOut .= '</td><td>';
	if (isset($row['Website'])) {
		$stdOut .= '<a href="' . $row['Website'] . '">' . $row['Website'] . '</a>';
	};
	$stdOut .= '</td>
	</tr>';
};
$stdOut .= '</tbody></table>';
require_once 'includes/footer.php'; ?>