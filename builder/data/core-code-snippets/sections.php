<?php
$thisSection = sectionValue();
$result = makeLink('Sections', pageUrl()) . ':';

foreach (variable('sections') as $ix => $section)//TODO: use cssUX
	$result .= ' ' . makeLink(humanize($section), pageUrl($section), false, false, 
		'btn m-1 ' . ($section == $thisSection ? 'btn-primary ' . cssUX::underline : 'btn-secondary'));

return $result;
