<?php
DEFINE('NETWORKDEFINEDAT', DEFINED('NETWORKPATH') ? NETWORKPATH . '/' : AMADEUSSITEDATA);
DEFINE('NETWORKABBR', variableOr('NetworkMenuName', 'DAWN'));

function network_menu($renderFn = false) {
	$wantsRender = $renderFn != false;
	if (variable(VARDAWNMenu) === BOOLNoString) return;

	$all = [];
	foreach (domain::$all as $d) {
		$item = domain::cast($d);
		if ($wantsRender && !$item->isCurrent && $item->only) continue;
		$all[] = site::getInfo($item->mainSites[0], $item);
	}

	if (!domain::$skipRest) $all[] = site::getInfo('spring', null); //only here null is allowed
	$items = [NETWORKABBR . ' DOMAINS' => $all];

	$domains = variableOr('domains-to-loop', domain::$all);
	foreach ($domains as $d) {
		$item = domain::cast($d);

		if (!is_dir(ALLSITESROOT . $item->folder)) continue;

		if ($wantsRender && !$item->isCurrent && $item->only) continue;

		$these = [];
		foreach ($wantsRender ? [] : $item->mainSites as $slug) {
			if (!is_dir(ALLSITESROOT . $slug)) continue;
			$item->prepareMainSite($slug);
			$these[] = site::getInfo($slug, $item);
		}

		if (!$wantsRender)
			$items[] = MENUSEPARATOR;
		$items[$item->heading] = $these;

		if (!$wantsRender && !$item->isCurrent && $item->skip) continue;

		if ($item->subFolders) foreach ($item->subFolders as $slug) {
			if (startsWith($slug, MENUGROUP)) { $items[$slug] = []; continue; }
			if (!is_dir(ALLSITESROOT . $item->folder . $slug)) continue;
			$items[humanize($slug)] = $item->getSites($slug);
		} else {
			$slug = substr($item->folder, -1);
			$items[humanize($slug)] = $item->getSites($slug);
		}
	}

	if ($wantsRender) {
		$renderFn($items);
		return;
	}

	if (domain::isDebugging(2)) showDebugging(57, $items, PleaseDie);
	twoLevelMenu($items, NETWORKABBR);
	if (getQueryParameter(VARQueryGenerate) == 'sites')
		domain::saveSites();
}

function getWebring() {
	$urlKey = _getUrlKeySansPreview();

	$txt = ALLSITESROOT . 'spring/data/Webring.txt';
	if (disk_file_exists($txt2 = ALLSITESROOT . domain::$current->mainSites[0] . '/Webring.txt'))
		$txt = $txt2;

	$items = textToList(disk_file_get_contents($txt));

	$sites = [];
	foreach ($items as $item) {
		if (startsWith($item, MENUGROUP)) {
			$sites[] = $item;
			continue;
		}

		$item = site::getInfo($item, null); //assume already in dawn menu

		$sites[] = $item;
	}

	return $sites;
}
