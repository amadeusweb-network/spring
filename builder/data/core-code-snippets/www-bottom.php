<?php
doToBuffering(1);

echo getSnippet('links', CORESNIPPET);
echo cbCloseAndOpen(cssUX::container);

printH1InDivider('This Domain');
echo getLink('All Domains', replaceNetworkUrls('%urlOf-spring%') . 'health/domains/', 'btn btn-lg btn-danger');
contentBox('end');

runFeature(features::explore);
if (variable(VARSafeName) == 'amadeusweb' && nodeIs(SITEHOME))
	variables(['boxes' => true]);
if (domain::$currentName != 'amadeusweb')
	variables(['domains-to-loop' => [domain::$current]]);
network_menu(function($item) { showSite($item); });

$result = doToBuffering(2);
doToBuffering(3);
return $result;
