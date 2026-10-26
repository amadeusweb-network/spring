<?php
DEFINE('NETWORKDEFINEDAT', DEFINED('NETWORKPATH') ? NETWORKPATH . '/' : AMADEUSSITEDATA);
DEFINE('NETWORKABBR', variableOr('NetworkMenuName', 'DAWN'));

setupNetwork();

function network_menu($renderFn = false) {
	$wantsRender = $renderFn != false;
	if (variable(VARDAWNMenu) === BOOLNoString) return;

	/*
	if (variable(VARNetwork) != BOOLNoString && !$wantsRender)
		flatMenu(variable('networkSites'), variable(VARNetwork));
	*/

	$urlKey = _getUrlKeySansPreview();

	$all = [];
	foreach (domain::$all as $d) {
		$item = domain::cast($d);
		if ($wantsRender && !$item->isCurrent && $item->only) continue;
		$all[] = getSiteInfo($item->mainSites[0], $urlKey);
	}

	if (!domain::$skipRest) $all[] = getSiteInfo('spring', $urlKey);
	$items = [NETWORKABBR . ' DOMAINS' => $all];

	$domains = domain::getSites($items) ?? variableOr('domains-to-loop', domain::$all);
	foreach ($domains as $d) {
		$item = domain::cast($d);

		if (!is_dir(ALLSITESROOT . $item->folder)) continue;

		if ($wantsRender && !$item->isCurrent && $item->only) continue;

		$these = [];
		foreach ($wantsRender ? [] : $item->mainSites as $slug) {
			if (!is_dir(ALLSITESROOT . $slug)) continue;
			$item->prepareMainSite($slug);
			$these[] = getSiteInfo($slug, $urlKey, $item);
			$item->cleanSubfolder();
		}

		if (!$wantsRender)
			$items[] = MENUSEPARATOR;
		$items[$item->heading] = $these;

		if (!$wantsRender && !$item->isCurrent && $item->skip) continue;

		if ($item->subFolders) foreach ($item->subFolders as $slug) {
			if (startsWith($slug, MENUGROUP)) { $items[$slug] = []; continue; }
			if (!is_dir(ALLSITESROOT . $item->folder . $slug)) continue;
			$item->currentSubfolder = $slug;
			$items[humanize($slug)] = $sites = setupNetwork($item->folder . $slug, $item);
			domain::appendSites($slug, $sites, $item);
			$item->cleanSubfolder();
		} else {
			$slug = substr($item->folder, -1);
			$sites = setupNetwork($item->folder, $item);
			foreach ($sites as $site) {
				if ($site->folder == 'www') continue;
				$items[$site->folder] = $site;
			}
			domain::appendSites($slug, $sites, $item);
		}
	}

	if ($wantsRender) {
		$renderFn($items);
		return;
	}

	twoLevelMenu($items, NETWORKABBR);
	if (getQueryParameter(VARQueryGenerate) == 'sites')
		domain::saveSites();
}

function setupNetwork(string | bool $where = false, domain $domain = null) {
	$networkSites = [];

	$items = [];
	$urlKey = _getUrlKeySansPreview();
	$returnArray = false;

	if ($where) {
		$fols = _skipNodeFiles(scandir(ALLSITESROOT . $where), ONLYFOLDERS);
		$slash = endsWith($where, '/') ? '' : '/';
		foreach ($fols as $fol) $items[] = $where . $slash . $fol;
		$returnArray = true;
	} else {
		$txt = ALLSITESROOT . 'spring/data/Webring.txt';
		if (disk_file_exists($txt2 = ALLSITESROOT . domain::$current->mainSites[0] . '/Webring.txt'))
			$txt = $txt2;

		$items = textToList(disk_file_get_contents($txt));
	}

	foreach ($items as $item) {
		if (startsWith($item, MENUGROUP)) {
			$networkSites[] = $item;
			continue;
		}

		$item = getSiteInfo($item, $urlKey, $domain);

		if ($item === false) continue;

		$networkSites[] = $item;
	}

	if ($returnArray) return $networkSites;
	variable('networkSites', $networkSites);
}
