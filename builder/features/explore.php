<?php
function showSite($allItems) {
	$subdomainHack = false;
	echo tagUX::tagStart(tagUX::Div, cssUX::CenterContainer);
	$urlKey = _getUrlKeySansPreview();
	$slim = nodeIs(SITEHOME) || variableOr('slim', getQueryParameter('slim'));
	$boxes = variableOr('boxes', getQueryParameter('boxes'));
	if ($boxes) { $slim = false; }
	$result = [htmlUX::valueOf(htmlUX::artAll)];

	$sites = [];
	$lite = variable('only-description');

	foreach ($allItems as $menu => $items) {
		if ($items == MENUSEPARATOR) continue;
		if (!$lite && !$boxes && startsWith($menu, MENUGROUP)) { printH1InDivider(substr($menu, 1), false); continue; }
		if (is_string($menu)) {
			if (!$lite && $slim) tagUX::contentBox(urlize($menu));
			if (!$lite && !$boxes) h2($menu);

			if ($boxes) {
				if (count($result) > 1)
					$result[] = htmlUX::valueOf(htmlUX::artClose);
				$result[] = htmlUX::valueOf(htmlUX::art3);
				$result[] = '<h3>' . $menu . '</h3>' . NEWLINES2;
			}
		}
		if (!is_array($items)) $items = [$items];
		foreach ($items as $ix => $item) {
			$site = site::cast($item);
			if ($lite) {
				$sites[] = $site->asItem($urlKey, $menu);
				continue;
			}

			$link = $site->getUrl($urlKey);
			$img = ($imgBase = $site->getUrl($urlKey, $subdomainHack) . $site->key) . '-logo.png';

			if ($boxes) {
				$result[] = '<a href="' . $link . '" target="_blank"><img width="40" src="' . $imgBase . '-icon.png" /> ' . $site->siteName . '</a> &mdash; ' . $site->description . '<hr />';
				continue;
			}

			$append = '';
			if (is_local())
				$append = getLink($item->getUrl(site::local), $item->getUrl(site::local), 'btn btn-secondary')
					. ' &mdash; ' . getLink($item->getUrl(site::live), $item->getUrl(site::live), 'btn btn-success');

			if ($slim) {
				echo
					'<hr /><a class="text-center" href="' . $link . '" target="_blank">' . 
					'	<img class="img-fluid img-max-200" src="' . $img . '" />' . BRNL .
					'	<b>' . $site->siteName . '</b>' . BRNL .
					'</a>' . $append . NEWLINE;
				continue;
			}

			tagUX::contentBox($site->key);
			echo
				//'<input class="img-fluid text-center" value="' . $img . '" />' . BRNL .
				'<a class="text-center" href="' . $link . '" target="_blank">' . 
				'	<img class="img-fluid img-max-600" src="' . $img . '" />' . BRNL .
				'	<b>' . $site->siteName . '</b>' . BRNL .
				'	<i>' . $site->byline . '</i>' . BRNL .
				//'	<input class="img-fluid text-center" value="' . $item[$urlKey] . '" />' . BRNL .
				$append .
				'</a>' . NEWLINE;
			
			$file = ALLSITESROOT . $site->path . '/whois/introduction/home.md';
			$id = $site->key . '-intro';
			$introduction = disk_file_exists($file)
				? renderSET::create(renderSET::default, false, $id)->excerpt()
					->render($file, [VARWrapInSection => true, replacer::replaces => ['url' => $link]])
				: bootstrapAndUX::colouredDiv('Not found: ' . substr($file, strlen(ALLSITESROOT)), bootstrapAndUX::warning, $id);

			echo '<hr><h5 class="mb-1">Introduction</h5>' . NEWLINE
				. tagUX::tag(tagUX::Div, 'p-3 rounded-3 introduction', $id . '-div', $introduction);

			tagUX::contentBoxEnd();
		}

		if (!$lite && $slim) tagUX::contentBoxEnd();
	}

	if ($boxes) {
		$result[] = htmlUX::valueOf(htmlUX::artClose);
		$result[] = htmlUX::valueOf(htmlUX::artAllClose);
		echo implode(NEWLINE, $result);
	}

	if ($lite) {
		features::ensureTables();
		(new tableBuilder('explore', $sites, explode(VARTab, site::$cols)))->render();
	}

	echo tagUX::tagEnd(tagUX::Div);
}
?>
<style type="text/css">
.introduction { background-color: azure; text-align: left; }
.introduction p:last-of-type { margin-bottom: 0; }
</style>
