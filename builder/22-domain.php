<?php
class domain {
	const OnlyThis = 'only-this';
	const SkipList = 'skip-list';

	/**
	 * @return domain Cast the domain
	 */
	static function cast($item) {
		return $item;
	}

	public static $skipRest = false;

	static function includeAll() {
		$sheet = getSheet(AMADEUSSITEDATA . 'domains.tsv', false);
		foreach ($sheet->rows as $item) {
			$site = $sheet->getValue($item, 'site');
			if (!disk_is_dir(ALLSITESROOT . $site)) continue;
			disk_include_once(ALLSITESROOT . $site . '/domain.php');
		}

		if (!self::$current && count(self::$all))
			self::$current = self::$all[array_keys(self::$all)[0]];

		if (self::$current)
			self::$current->isCurrent = true;
	}

	//all hosted sites cache
	static function getSites(&$items) {
		$generate = getQueryParameter(VARQueryGenerate) == 'sites';
		if ($generate || !disk_file_exists(self::$sitesFile)) return null;

		$sheet = getSheet(self::$sitesFile, 'domain');
		foreach ($sheet->group as $domain => $sites) {
			$fol = $item->folder;
			if (!is_dir(ALLSITESROOT . $fol)) continue;
			$main = arrayGroupBy();
		}
	}

	private static $sitesFile = ALLSITESROOT . '/sites.tsv';
	private static $sites = ['#domain	folder	site	main	name	byline	' .
		'iconName	local-url	live-url	sections	description'];
	
	static function appendSites($fol, array $items, domain $domain) {
	}

	static function saveSites() {
		if (!is_local()) showDebugging('nice try', 'illegal operation', PleaseDie);
		$file = str_replace('.tsv', '.gen.tsv', self::$sitesFile);
		file_put_contents($file, implode(NEWLINE, self::$sites));
	}

	static function mainSiteRelpath() {
		if (is_local() || !self::$current) return '';
		return self::$current->mainSites[0] . '/';
	}

	/**
	 * @var domain[] All domains
	 */
	static $all = [];

	/**
	 * @var domain Current domain
	 */
	static $current;

	static function add(string $key, string $folder, bool | string $skipRule, domain $item) {
		$path = DEFINED('ALLSITENAME') ? ALLSITENAME : (DEFINED('SITEPATH') ? SITEPATH : false);
		$match = $path && contains($path, $key); //doesnt handle substring collision

		if ($skipRule && $skipRule != self::SkipList && !$match && !DEFINED('ALLSITENAME')) return;

		if ($match) {
			self::$current = $item;
			if (!DEFINED('NETWORKPATH'))
				DEFINE('NETWORKPATH', $folder);
		}

		if (self::$skipRest) return;

		$item->hasRule = is_string($skipRule);
		if ($skipRule == self::SkipList) $item->skip = true;
		if ($skipRule == self::OnlyThis) $item->only = true;

		self::$all[$key] = $item;
		if ($match && $item->skip) self::$skipRest = true;
	}

	static function remove(string $key) {
		if (!isset(self::$all[$key])) return;
		unset(self::$all[$key]);
	}

	public static string $currentName = '';

	public string $currentSubfolder = '';

	public string $key;
	public bool $skip = false;
	public bool $only = false;
	public bool $hasRule = false;
	public bool $isCurrent = false;

	public string $folder;
	public string $heading;
	public string $local;
	public string $live;
	public string $localBase;
	public string $liveBase;
	public array $mainSites;
	public array | bool $subFolders;

	public function prepareMainSite($site) {
		$site = substr($site, strlen($this->folder));
		if (!contains($site, '/')) return;
		$bits = explode('/', $site);
		$this->currentSubfolder = $bits[0];
	}

	public function cleanSubfolder() {
		$this->currentSubfolder = '';
	}

	public function __construct(array $vars, array $mainSites, $subFolders = []) {
		$this->folder = $vars['folder'];
		$this->key = removeSlash($this->folder, 'end');
		$this->heading = $vars['heading'];
		$this->local = $vars['local'];
		$this->live = $vars['live'];
		$this->localBase = $vars['local-base'];
		$this->liveBase = $vars['live-base'];
		foreach ($mainSites as $ix => $val) {
			if (contains($val, '%folder%'))
				$mainSites[$ix] = replaceItems($val, ['folder' => $this->folder], WRAPREPLACE);
		}
		$this->mainSites = $mainSites;
		$this->subFolders = $subFolders;
	}
}
