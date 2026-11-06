<?php
contentBox(nodeValue(), 'container mt-3');
h2('Search Engines');

$engine;
$id = getPageParameterAt();

features::ensureTables();
$search = getHtmlVariable('url') . nodeValue() .'/%key%/';
(new tableBuilder(nodeValue(), search::getItems($id, $engine), 'auto', '<tr><td>%is_this%</td><td><a href="' . $search . '">%name%</a></td>' .
	'<td><a href="' . search::GoogleAdminUrl . '%code%">Modify</a></td><td>%description%</td></tr>',
	['enrichItem' => function($row) { $row['is_this'] = $row['key'] == variable(search::engineId) ? tagUX::appearChecked : ''; return $row; }]))->render();
?>

<script async src="https://cse.google.com/cse.js?cx=<?php echo $engine->code; ?>"></script>
<div class="gcse-search"></div>

<?php
contentBox('end');
