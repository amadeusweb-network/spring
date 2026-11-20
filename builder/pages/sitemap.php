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
	if (!$at) recurseList(SITEPATH . '/' . $item . '/', $item . '/', '', $ix++ . '.', $items);
}
echo bootstrapAndUX::colouredDiv(implode(' ', $links), cssUX::concat(bootstrapAndUX::info, 'p-3'));

function itemLink($item, $text, $selected) {
	$qs = $item ? '?at=' . $item : '';
	$result = getLink($text, pageUrl('sitemap/' . $qs), bootstrapAndUX::buttonBg($selected == $item ? bootstrapAndUX::success : bootstrapAndUX::primary));
	if ($selected == $item) {
		$text = '<i class="fa fa-arrow-down-short-wide"></i>';
		$qs .= ($qs ? '&' : '?') . 'reverse=1';
		$result .= ' ' . getLink($text, pageUrl('sitemap/' . $qs) . NOFOLLOWSUFFIX, bootstrapAndUX::buttonBg(getQueryParameter('reverse') ? bootstrapAndUX::danger : bootstrapAndUX::warning));
	}
	return $result;
}

if ($at) {
	$base = $at ? substr($at, strlen(sectionValue())) . '/' : '';
	$node = explode('/', $base);
	unset($node[0]);
	$node = implode('/', $node);
	recurseList(SITEPATH . '/' . $at . '/', $base, $node, '1.', $items);
}

if (getQueryParameter('reverse'))
	krsort($items, SORT_NATURAL);
else
	ksort($items, SORT_NATURAL);

$id = $at ? '_' . str_replace('/', '_', $at) : '';
features::ensureTables();
tableBuilder::factory('sitemap' . $id, $items, FILTERTABLE);

contentBox('end');

function recurseList($fol, $base, $node, $baseSno, &$items) {
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
		$title = humanize($item == 'home' ? textUtil::last($base) : $item);
		$items[$sno] = [
			'SNo' => $sno,
			'Page' => getLink($title, pageUrl($node . ($item != 'home' ? $item : ''))),
			'In' => substr($baseSno, 0, -1),
			'Locate' => getLink($name, pageUrl('sitemap/?at=' . $name) . NOFOLLOWSUFFIX),
		];
	}

	foreach ($folders as $item)
		recurseList($fol . $item . '/', $base . $item . '/', $node . $item . '/', $baseSno . $ix++ . '.', $items);
}
