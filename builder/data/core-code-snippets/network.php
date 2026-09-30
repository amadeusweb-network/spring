<?php
return ''; //deprecated - use new explore via www-bottom
if (!contains(variable(VARSafeName), 'amadeusweb')) return ''; //TODO: in_array

doToBuffering(1);

echo cbCloseAndOpen('after-content');
printSpacer('NETWORK');

runFrameworkFile('site/listing');

$result = doToBuffering(2);
doToBuffering(3);
return $result;
