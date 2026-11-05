<?php
DEFINE('SKIPBOOTSTRAP', true);
include_once __DIR__ . '/../entry.php';
domain::includeAll();

$name = isset($_GET['name']) ? $_GET['name'] : explode('.', $_SERVER['HTTP_HOST'])[0];
if ($name == 'localhost') $name = ALLSITENAME == 'aztras' ? 'realtors' : 'imran';

$rootPath = realpath(__DIR__ . '/../../') . '/';
$siteInfo = domain::$all[ALLSITENAME];
$siteInfo::$currentName = $name;

if (count($siteInfo->subFolders)) {
	$fol = $rootPath . $siteInfo->folder;
	$found = false;
	foreach ($siteInfo->subFolders as $item) {
		if (is_dir($fol . $item . '/' . $name)) {
			$siteInfo->siteFolder = $item . '/';
			$found = true;
			break;
		}
	}
	if (!$found) die($name . ' not found in: ' . $fol);
}

DEFINE('ALLSITEPATH', $rootPath . $siteInfo->folder . $siteInfo->siteFolder . domain::$currentName);

define('SITEINFO', $siteInfo);
define('SITEPATH', ALLSITEPATH);

if (DEFINED('SKIPBOOTSTRAP'))
	before_bootstrap();

$loader = ALLSITESROOT . domain::$current->mainSites[0] . '/loader.php';
if (disk_file_exists($loader)) disk_include_once($loader);

runFrameworkFile('site/begin');
