<?php
if (!variable('countdown')) return '';
$tpl = _excludeFromGoogleSearch(getThemeSnippet('countdown') . NEWLINE . '<hr>');
return replaceItems($tpl, [
	'heading'          => returnLine(subVariableOr('countdown', 'heading', 'A proprietary system, The <span class="ls-2">[**Dynamic AmadeusWeb Network**](%urlOf-world%BTNINFO)</span> is not for the faint of heart!')),
	'left-hand-side'   =>   markdown(subVariableOr('countdown', 'left',    'Next Release In')),
	'right-hand-side'  =>   markdown(subVariableOr('countdown', 'right',   'built with AW Spring [v9.9 of Sep 28th](%urlOf-spring%BTNSUCCESS)')),
	'countdown-params' =>            subVariableOr('countdown', 'params',  'data-year="2026" data-month="2" data-day="21" data-hour="12" data-minute="37" data-format="dHMS"'),
	'message'          => returnLine(subVariableOr('countdown', 'message', 'by [Imran Ali Namazi, Architect](%urlOf-imran%BTNPRIMARY)')),
], '%');
