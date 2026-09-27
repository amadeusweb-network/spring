<?php
function showSite($allItems) {
	$subdomainHack = false;
	echo tagUX::tagStart(tagUX::Div, cssUX::CenterContainer);
	$urlKey = _getUrlKeySansPreview();
	$slim = nodeIs(SITEHOME) || variableOr('slim', getQueryParameter('slim'));
	$sites = [];
	$lite = variable('only-description');
	foreach ($allItems as $menu => $items) {
		if ($items == MENUSEPARATOR) continue;
		if ($slim) tagUX::contentBox(urlize($menu));
		if (!$lite) h2($menu);
		foreach ($items as $item) {
			$site = site::cast($item);
			if ($lite) {
				$sites[] = $site->asItem($urlKey, $menu);
				continue;
			}

			$link = $site->getUrl($urlKey);
			$img = $site->getUrl($urlKey, $subdomainHack) . $site->key . '-logo.png';

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

		if ($slim) tagUX::contentBoxEnd();
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
