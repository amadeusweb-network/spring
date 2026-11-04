<?php
variables([
	VARSectionsHaveFiles => true,
]);

function site_before_render() {
	$logo = in_array(nodeValue(), ['health']);
	autosetPageMenu([VARLinkToNodeHome => true, DontOverwriteLogo => !$logo]);
}
