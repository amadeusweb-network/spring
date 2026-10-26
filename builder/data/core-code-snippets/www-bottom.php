<?php
runFeature(features::explore);
if (variable(VARSafeName) == 'amadeusweb' && nodeIs(SITEHOME))
	variables(['boxes' => true]);
else
	variables(['domains-to-loop' => [domain::$current]]);
doToBuffering(1);
network_menu(function($item) { showSite($item); });
$result = doToBuffering(2);
doToBuffering(3);
return $result;
