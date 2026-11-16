<?php
variables([
	'skip-content-render' => true,
	VARNoFooterWidgets => true,
]);

runFrameworkFile('site/begin');

function did_site_render_page() {
	$header = getSnippet('listing');
	//if (!$header) $header = getSnippet('root-header', CORESNIPPET);
	if ($header)
	echo _excludeFromGoogleSearch(
		contentBox('root-header', 'container text-center mt-5', true)
		. $header
		. contentBox('end', '', true)
	);

	if (DEFINED('DOMAINFOLDER')) domain::$current->subFolders = [DOMAINFOLDER];

	runFeature(features::explore);
	variables([
		'domains-to-loop' => [domain::$current],
	]);
	network_menu(function($item) { showSite($item); });
	return true;
}
