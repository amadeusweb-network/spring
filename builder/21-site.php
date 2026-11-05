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

	static function getFolder($relativePath) {
		//todo - slashes to text
		$bits = explode(DIRECTORY_SEPARATOR, _makeSlashesConsistent($relativePath));
		return array_pop($bits);
	}

	static function getPrefix($relativePath, &$file) {
		if (!defined('ALLSITESDATA') || !in_array($f = site::getFolder($relativePath), ALLSITESNAMES))
			return '';

		$file = ALLSITESDATA;
		return $f . '_';
	}

	const local = 'local-url';
	const live = 'live-url';

	public string $key;
	public string $name;
	public string $siteName;
	public string $byline;

	public string $localUrl;
	public string $liveUrl;
	public string $assetUrl;

	public string $path;
	public string $folder;

	public string $description;
	public string $notes;
	public string $year;
	public string $sections;

	public function __construct(sheet $site, $relativePath, $prefix = '') {
		$this->key = $site->getValue($site->firstOfGroup($prefix . VARSafeName), 'value');
		$this->name = $site->getValue($site->firstOfGroup($prefix . VARIconName), 'value');

		$this->siteName = $site->getValue($site->firstOfGroup($prefix . 'name'), 'value');
		$this->byline = $site->getValue($site->firstOfGroup($prefix . VARByline), 'value');

		$this->localUrl = $site->getValue($site->firstOfGroup($prefix . 'local-url'), 'value');
		$this->liveUrl = $site->getValue($site->firstOfGroup($prefix . 'live-url'), 'value');
		$this->folder = self::getFolder($this->path = $relativePath);

		$this->description = $site->getValue($site->firstOfGroup($prefix . 'description', '', false), 'value', VAREMPTY, false, BOOLNo);
		$this->notes = $site->getValue($site->firstOfGroup($prefix . 'notes', '', false), 'value', VAREMPTY, false, BOOLNo);
		$this->year = $site->getValue($site->firstOfGroup($prefix . 'year'), 'value');
		$this->sections = $site->getValue($site->firstOfGroup($prefix . 'sections'), 'value');
	}
	
	function linkDomain(domain $domain = null) {
		$this->assetUrl = $domain
			? $domain->getBaseInfo(removeSlash($domain->siteFolder, 'end')) . $this->folder . '/'
			: $this->getUrl(_getUrlKeySansPreview());
		return $this;
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

	function getUrl(string $key) {
		return $key == self::local ? $this->localUrl : $this->liveUrl;
	}
}

function getSiteInfo($relativePath, $urlKey = false, domain $domain = null) : site | bool {
	$key = 'siteInfo_' . $relativePath;
	$result = variable($key);
	if ($result) return $result;

	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	$prefix = site::getPrefix($relativePath, $file);
	//showDebugging('getSiteInfo()', [site::getFolder($relativePath), $relativePath, $file, $prefix]);

	//need the check again as it may be called from subsites/
	if (!sheetExists($file)) {
		if (is_local()) showDebugging('137 getSiteInfo', ['missing for' => $relativePath, 'TSV missing' => $file], PleaseDie, IncludeTrace);
		return false;
	}

	$site = (new site(getSheet($file, 'key'), $relativePath, $prefix))->linkDomain($domain);

	if ($urlKey) addNetworkUrl($relativePath, $site->getUrl($urlKey));
	if (count(domain::$all)) variable($key, $site);
	return $site;
}

function getSiteLink($relativePath, domain $domain) {
	$site = getSiteInfo($relativePath, false, $domain);
	if (!$site) return 'missing: ' . $relativePath;
	return getLink($site->siteName, $site->getUrl(_getUrlKeySansPreview()));
}

function getUrlFrom($relativePath, $urlKey = false) {
	if (!$urlKey) $urlKey = _getUrlKeySansPreview();
	$result = getSiteInfo($relativePath);
	if (!$result) return '#missing-' . $relativePath;
	return $result->getUrl($urlKey);
}
