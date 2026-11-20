<?php
$at = getQueryParameter('at', '');
$where = $at ? ' / ' . implode(' / ', array_map('humanize', explode('/', $at))) : '';
printH1InDivider(title(FORHEADING). $where);

$selected = explode('/', $at)[0];

$items = [];
$links = [itemLink('', 'All Sections', $selected)];

$skip = variableOr('SkipSitemap', []);

$ix = 1;
foreach (variable('sections') as $item) {
	if (in_array($item, $skip)) continue;
	$links[] = itemLink($item, humanize($item), $selected);
	if (!$at) recurseList(SITEPATH . '/' . $item . '/', $item . '/', $ix++ . '.', $items);
}
echo bootstrapAndUX::colouredDiv(implode(' ', $links), cssUX::concat(bootstrapAndUX::info, 'p-3'));

function itemLink($item, $text, $selected) {
	$color = $selected == $item ? bootstrapAndUX::success : bootstrapAndUX::primary;
	$qs = $item ? '?at=' . $item : '';
	return getLink($text, pageUrl('sitemap/' . $qs), cssUX::concat('bg-' . $color, cssUX::m1, cssUX::p2, cssUX::dark));
}

if ($at) {
	$base = $at ? substr($at, strlen(sectionValue())) . '/' : '';
	recurseList(SITEPATH . '/' . $at . '/', $base, '1.', $items);
}

ksort($items, SORT_NATURAL);

$id = $at ? '_' . str_replace('/', '_', $at) : '';
features::ensureTables();
tableBuilder::factory('sitemap' . $id, $items, FILTERTABLE);

contentBox('end');

function recurseList($fol, $base, $baseSno, &$items) {
	$folders = [];
	$files = [];

	foreach (_skipExcludedFiles(disk_scandir($fol), ExcludedFolders, EXCLUDEEXTENSIONS, true) as $item) {
		if (disk_is_dir($fol . $item))
			$folders[] = $item;
		else
			$files[] = $item;
	}

	$ix = 1;
	foreach ($files as $item) {
		$sno = $baseSno . $ix++;
		$name = substr($base, 0, -1);
		$items[$sno] = [
			'SNo' => $sno,
			'Page' => getLink(humanize($item), pageUrl($base . ($item != 'home' ? $item : ''))),
			'In' => substr($baseSno, 0, -1),
			'Locate' => getLink($name, pageUrl('sitemap/?at=' . $name) . NOFOLLOWSUFFIX),
		];
	}

	foreach ($folders as $item)
		recurseList($fol . $item . '/', $base . $item . '/', $baseSno . $ix++ . '.', $items);
}
