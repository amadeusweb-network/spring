<?php
addStyle(features::engage, assetManager::core);
addScript(features::engage, assetManager::core);

function getEngageData($meta = [], $onlyAttributes = false) {
	$salutation = variableOr('salutation', 'Dear ' . variable('name')) . ',';
	$addressee = '';
	$additionalCC = '';
	$whatsapp = variable('whatsapp-txt-start');

	if ($meta) {
		$mailSpacer = ',';
		if (isset($meta['Salutation'])) $salutation = $meta['Salutation'];
		if (isset($meta['Email To'])) $addressee = $meta['Email To'] . $mailSpacer;
		if (isset($meta['Email Cc'])) $additionalCC = $meta['Email Cc'] . $mailSpacer;
		if (isset($meta['WhatsApp To'])) $whatsapp = whatsapp_me($meta['WhatsApp To'], WAQS, false);
	}

	$systemIncluded = contains($addressee, VARSystemEmail) || contains($additionalCC, VARSystemEmail);
	$defaultCC = $systemIncluded ? '' : VARSystemEmail;

	$attributes = ' data-to="' . ($email = $addressee . variable(VAREmail)) .
		'" data-cc="' . $additionalCC . $defaultCC .
		'" data-whatsapp="' . $whatsapp .
		'" data-site-name="' . variable('name') .
		'" data-salutation="' . $salutation . '"';
	if ($onlyAttributes) return $attributes;
	return ['attributes' => $attributes, 'email' => $email, 'whatsapp' => $whatsapp];
}

//TODO: Make a toggle-more when the md contains <!--more-->
function renderEngage($name, $raw, $echo = true, $meta = [], $settings = []) {
	//if (!$open) echo engageButton($name, $class);

	$class = valueIfSet($settings, VARNoContentBoxes) ? features::engage : _getCBClassIfWanted(features::engage);
	$class = '" class="' . $class . '"';

	$data = getEngageData($meta);

	$result = '	<div id="engage-' . urlize($name) . $class .
		//($open ? '' : 'style="display: none" ') .
		$data['attributes'] . '>' . NEWLINE;

	$result .= renderMarkdown($raw, [replacer::replaces => getEngageReplaces($data['email'], $data['whatsapp']), 'echo' => false]);

	$result .= getEngageToolbox();
	
	$result .= '</div>' . NEWLINE;
	if (!$echo) return $result;
	echo $result;
}

function getEngageReplaces($email = false, $whatsapp = false) {
	if (!disk_file_exists($note = (AMADEUSDATA . 'engage-note.md'))) return [];

	if (!$email) $email = variable(VAREmail);
	if (!$whatsapp) $whatsapp = variable(VARWhatsapp);
	return [
		VAREngageNote => tryEnrichEngageNote('<div class="engage-note-hint d-none">' . returnLine($note) . '</div>', VAREngageNote),
		VAREngageNoteAbove => disk_file_exists($note2 = (AMADEUSDATA . 'engage-note-above.md')) ? tryEnrichEngageNote(returnLine($note2), VAREngageNoteAbove) : '',
		VAREmail => $email,
		VARWhatsapp => $whatsapp . getHtmlVariable('enquiry'),
	];
}

function getEngageToolbox() {
	return getSnippet('engage-toolbox', CORESNIPPET);
}

function tryEnrichEngageNote($content, $where) {
	return function_exists('enrichEngageNote') ? enrichEngageNote($content, $where) : $content;
}

function renderEngageFields($raw) {
	return replaceItems($raw, [
		'[field]' => '<ul><li>amount</li><li>note</li></ul>',
		'[field-TicketId]' => '<span class="bg-info dark p-2 fs-5 rounded-2 listened-field" data-field="TicketId">%TicketId%</span>',
	]);
}

function runEngageFromSheet($pageName, $sheetName) {
	$sheet = getSheet($sheetName);
	$contentIndex = $sheet->columns['content'];
	$introIndex = $sheet->columns['section-intro'];
	$varsIndex = valueIfSet($sheet->columns, 'item_vars');
	$introduction = valueIfSet($sheet->values, 'introduction', 'Welcome to <b>' . humanize($pageName) . '</b> page of <b>' . variable('name') . '</b>.');

	//TODO: use faq by category like canvas' FAQ?
	//$items = []; //trying to make as pills in a later version
	$raw = ['<!--engage: SITE //engage--><!--render-processing-->', $introduction, ''];

	$firstSection = true;

	$customEngageNotes = variable('custom-engage-notes');
	if (!$customEngageNotes)
		$raw[] = '%engage-note-above%';

	foreach ($sheet->group as $name => $rows) {
		$raw[] = '## ' . $name;
		$raw[] = '';

		$firstRow = true;
		foreach ($rows as $row) {
			if ($firstRow) {
				$raw[] = $row[$introIndex];
				$raw[] = '';
				$firstRow = false;
			}
	
			$line = $row[$contentIndex];

			if ($varsIndex) {
				$vars = $row[$varsIndex];
				if ($vars) {
					$vbits = explode(', ', $vars);
					if (in_array('open', $vbits))
						$line .= '<!--open-->';
					if (in_array('large', $vbits))
						$line .= '<!--large-->';
				}
			}

			$raw[] = '* '  . $line;
			//$content[] = ;
		}
	
		$raw[] = '';
	}

	$raw[] = '';
	if (!$customEngageNotes)
		$raw[] = '%engage-note%';

	//$raw = print_r($items, 1); //$raw = renderPills($items); //todo: LATER!
	sectionId('engage-' . urlize($pageName));
	renderEngage($pageName, implode(NEWLINE, $raw));
	sectionEnd();
}
