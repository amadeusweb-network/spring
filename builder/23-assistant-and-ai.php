<?php
class aihelper {
	public readonly string $type;
	private readonly bool $adjust;

	function hasLlm() {
		return $this->type != 'none'
			|| $this->adjust;
	}

	function getCss() {
		switch ($this->type) {
			case 'gemini': return self::GeminiClasses;
			case 'chatgpt': return self::ChatgptClasses;
		}
		throw new Exception('cannot call getCss on type: ' . $this->type);
	}


	function __construct($raw) {
		$hasGemini = contains($raw, self::HasGemini);
		$hasChatgpt = contains($raw, self::HasChatgpt);

		if ($hasGemini || contains($raw, self::FromGemini))
			$this->type = 'gemini';
		else if ($hasChatgpt || contains($raw, self::FromChatgpt))
			$this->type = 'chatgpt';
		else
			$this->type = 'none';

		if ($hasGemini || $hasChatgpt)
			$this->adjust = true;
		else
			$this->adjust = false;
	}

	static function peekAtMainFile($file, $return = false) {
		$raw = disk_file_get_contents($file);
		$ai = new aihelper($raw);
		if (!$ai->hasLlm()) return '';

		$css = $ai->getCss();
		if ($return) return $css;

		add_body_class($css);
	}

	private const FromGemini = '<!--exported-from-gemini-ai-->';
	private const HasGemini = '<!--has-gemini-ai-elements-->';
	private const GeminiMsg = 'This is a Chat with "Gemini AI"';
	private const GeminiClasses = 'with-ai has-ai has-gemini-ai has-prompts';

	private const FromChatgpt = '<!--exported-from-chatgpt-ai-->';
	private const HasChatgpt = '<!--has-chatgpt-ai-elements-->';
	private const ChatgptMsg = 'This is a Chat with "Chat GPT"';
	private const ChatgptClasses = 'with-ai has-ai has-chatgpt-ai has-prompts';

	function preProcess($raw) {
		if ($this->type == 'chatgpt')
			$raw = replaceItems($raw, [
				'## Prompt:' . NEWLINE => '## Prompt:' . NEWLINES2 . '> ',
				'## Response:' . NEWLINE => '## Response:' . NEWLINES2 . '> ',
			]);

		$autoV2 = $this->type == 'chatgpt' ? 'v2' : '';
		$replaces = [
			self::FromGemini => self::FromGemini . SPACERSTART . self::GeminiMsg . SPACEREND,
			self::FromChatgpt => self::FromChatgpt . SPACERSTART . self::ChatgptMsg . SPACEREND,
			'## Prompt:' => '[prompt' . $autoV2 . ']',
			'## Response:' => '[/prompt' . $autoV2 . ']' . NEWLINES2,
			'## User:' => '[promptv2]',
			'## Gemini:' => '[/promptv2]' . NEWLINES2,
			'***' => '<p>***</p>',
		];

		foreach (['https://www.geminiexporter.com/'
					, 'https://www.chatgptexporter.com'
				] as $item)
			$replaces[$item] = $item . NOFOLLOWSUFFIX;


		if ($sr = variable('siteAIReplaces'))
			$raw = replaceItems($raw, $sr);

		return replaceItems($raw, $replaces);
	}

	private const GoogleImages = 'https://lh3.googleusercontent.com';

	function adjustOutput($raw) {
		$noFollow = nofollowReplace(self::GoogleImages);
		$raw = replaceItems($raw, $noFollow);

		if (!contains($raw, '<p>| ')) return $raw;

		features::ensureTables();
		_includeDatatables(false);
		_includeTableAssets();

		return replaceItems($raw, [
			'<p>| ' => '<table class="datatables table-sans-th table table-striped table-bordered"><thead></thead><tbody><tr><td>',
			'|</p> ' => '</tr></tbody></table>',
			' | ' => '</td><td>',
			'| ' => '<tr><td>',
			' |' => '</td></tr>',
			'</td></tr></p>' => '</tr></tbody></table>',
			'--- |' . NEWLINE => '-->' . NEWLINE,
			'| --- |' => '<!--',
			'<tr><td>---' => '<tr class="d-none"><td>',
		]);
	}
}

class search {
	public $key;
	public $name;
	public $code;
	public $description;

	function __construct($key, $name, $code, $description) {
		$this->key = $key;
		$this->name = $name;
		$this->code = $code;
		$this->description = $description;
	}

	public const GoogleAdminUrl = 'https://programmablesearchengine.google.com/controlpanel/overview?cx=';

	const engineId = 'searchEngineId';

	public const dawn = 'dawn';
	public const imran = 'imran';
	public const defaults = [self::dawn, self::imran, 'msa'];
	public const names = 'searchNames';
	public const items = 'searchItems';

	static function getItems(&$id, search | null &$engine) {
		$names = variableOr(self::names, self::defaults);
		$searches = variableOr(self::items, []);

		$searches = array_merge([
			'dawn' => new search('dawn', 'All DAWN', '6759b2f9b58c247af', 'All AmadeusWeb.in sites'),
			'imran' => new search('imran', 'All Imran', 'f26c9d08384bc4ce7', 'All Imran\'s Writing'),
			'msa' => new search('msa', 'Mother Sri Aurobindo', '84d24b3918cbd5f1a', 'Resource Sites of the Aurobindonian World'),
		], $searches);

		$result = [];
		if (!$id) $id = $names[0];
		variable(self::engineId, $id);

		foreach ($names as $key) {
			$item = $searches[$key];
			if ($item->key == $id) $engine = $item;
			$result[] = get_object_vars($item);
		}

		return $result;
	}
}