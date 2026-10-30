<?php
global $networkUrls;
$networkUrls = [];

function addNetworkUrl($site, $url) {
	global $networkUrls;
	$networkUrls[URLOFPREFIX . $site] = $url;
}

function replaceNetworkUrls($html) {
	global $networkUrls;
	if (empty($networkUrls)) return $html; //assumes will be called again in render
	if ($html === PleaseDie) showDebugging(22, $networkUrls, true);
	if (!contains($html, URLOFPREFIX) || empty($networkUrls)) return $html;
	//if (endsWith($html, '%')) showDebugging(23, [$html, $networkUrls], PleaseDie);
	return replaceItems($html, $networkUrls, WRAPREPLACE);
}

function getSiteKey($site, $suffix = '') { return '%' . URLOFPREFIX . $site . '%' . $suffix; }
function getSiteUrl($site, $suffix = '') { return replaceNetworkUrls(getSiteKey($site)) . $suffix; }

function getSpecialUrl($name) {
	if ($name == 'root')
		return getSiteUrl(SITEROOT);
	else if ($name == 'signup')
		return getSiteUrl(SITEROOT, 'services/signup/');
	else if ($name == 'smithy')
		return getSiteUrl(SITESPRING, NODESTART . '/');
	else throw new Error('Unknown SpecialUrl: ' . $name);
}

function getWhatIsLink($name) {
	return getLink('&nbsp;' . $name . '&nbsp;', getSiteUrl(SITESPRING) . '#todo/what-is/cdn/', 'rounded-2 p-1 text-black fw-bolder bg-info');
}

class site {
	/**
	 * @var site Current site
	 */
	static $current;

	/**
	 * @return site Cast the site
	 */
	static function cast($item) {
		return $item;
	}

	static function getWildcardUrl(domain $domain, string $folder) {
		$base = is_local() ? $domain->localBase : $domain->liveBase;
		return replaceSiteInfo($base, '--unused--', $domain->currentSubfolder) . $folder . '/';
	}

	static function getFolder($relativePath) {
		$bits = explode('/', $relativePath);
		return array_pop($bits);
	}

	const local = 'local-url';
	const live = 'live-url';

	public string $key;
	public string $name;
	public string $siteName;
	public string $byline;

	public string $localUrl;
	public string $liveUrl;
	public string $wildcardUrl = '';

	public string $path;
	public string $folder;

	public string $description;
	public string $notes;
	public string $year;
	public string $sections;

	public function __construct(sheet $site, $relativePath) {
		$this->key = $site->getValue($site->firstOfGroup(VARSafeName), 'value');
		$this->name = $site->getValue($site->firstOfGroup(VARIconName), 'value');

		$this->siteName = $site->getValue($site->firstOfGroup('name'), 'value');
		$this->byline = $site->getValue($site->firstOfGroup(VARByline), 'value');

		$this->localUrl = $site->getValue($site->firstOfGroup('local-url'), 'value');
		$this->liveUrl = $site->getValue($site->firstOfGroup('live-url'), 'value');
		$this->folder = self::getFolder($this->path = $relativePath);

		$this->description = $site->getValue($site->firstOfGroup('description', '', false), 'value', VAREMPTY, false, BOOLNo);
		$this->notes = $site->getValue($site->firstOfGroup('notes', '', false), 'value', VAREMPTY, false, BOOLNo);
		$this->year = $site->getValue($site->firstOfGroup('year'), 'value');
		$this->sections = $site->getValue($site->firstOfGroup('sections'), 'value');
	}

	static $cols = 'name	description	info';

	function asItem($key, $menu) {
		return [
			'name' => '<a href="' . $this->getUrl($key) . '" target="_blank">' . $this->name . '</a>',
			'description' => $this->description . ($this->notes ? '<hr />' : '') . $this->notes,
			'info' => $this->folder . ' (' . $this->year . ') in <b>' . $menu . '</b><br />' .
				'<u>Sections</u>: ' . $this->sections,
		];
	}

	function getUrl(string $key, $wildcard = false) {
		if ($wildcard && $this->wildcardUrl) return $this->wildcardUrl;
		return $key == self::local ? $this->localUrl : $this->liveUrl;
	}

	function setWildcardUrl(domain $domain) {
		if (!$domain->currentSubfolder) return;
		$this->wildcardUrl = self::getWildcardUrl($domain, $this->folder);
	}
}

function getSiteInfo($relativePath, $urlKey = false, domain $domain = null) : site | bool {
	$key = 'siteInfo_' . $relativePath;
	$result = variable($key);
	if ($result) return $result;

	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	//need the check again as it may be called from subsites/
	if (!sheetExists($file)) {
		if (is_local()) debug(__FILE__, 'getSiteInfo', ['missing for' => $relativePath, 'TSV missing' => $file], DEBUGSPECIAL);
		return false;
	}

	$site = new site(getSheet($file, 'key'), $relativePath);

	if ($domain) $site->setWildcardUrl($domain);

	if ($urlKey) addNetworkUrl($relativePath, $site->getUrl($urlKey));
	if (count(domain::$all)) variable($key, $site);
	return $site;
}

function getUrlFrom($relativePath, $urlKey = false) {
	if (!$urlKey) $urlKey = _getUrlKeySansPreview();
	$result = getSiteInfo($relativePath);
	if (!$result) return '#missing-' . $relativePath;
	return $result->getUrl($urlKey);
}

function replaceSiteInfo($input, $name, $subfolder) {
	return str_replace('%subfol%', $subfolder, str_replace('%site%', $name, $input));
}
