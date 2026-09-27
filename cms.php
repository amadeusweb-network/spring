<?php
variables([
	VARSectionsHaveFiles => true,
]);

function site_before_render() {
	autosetPageMenu([VARLinkToNodeHome => true, DontOverwriteLogo => true]);
}
