<?php
class CanvasTheme {
	const beauty = 'beauty';
	const realEstate = 'real-estate';
	const spa = 'spa';
	const supplements = 'supplements';
	const dontUseTag = false;

	static function addAssets($page) {
		foreach (self::HeadCssFor($page, self::dontUseTag) as $css)
			add3pStyle($css);

		if (!in_array($page, [self::beauty])) return;

		$cp = 'js/components/';
		$scripts = [
			self::beauty => [$cp . 'event.move', $cp . 'image-changer'],
		][$page];

		$base = getThemeBaseUrl();
		foreach ($scripts as $js)
			add3pScript($base . $js . '.js');
	}

	static function HeadCssFor($page, $useTag = true) {
		$format = $useTag ? CSSTAG : '%s';
		$css = [];
		$base = getThemeBaseUrl();
		$demo = $base . 'demos/' . $page . '/';
		if ($page == self::spa) {
			//$demo = '//canvastemplate.com/demo/spa/';
			$css[] = sprintf($format, $demo . $page . '.css');
			$css[] = sprintf($format, $demo . 'css/fonts/spa-icons.css');
		} else if ($page == self::realEstate) {
			$css[] = sprintf($format, $demo . $page . '.css');
			$css[] = sprintf($format, $demo . 'css/font-icons.css');
		} else if ($page == self::beauty) {
			$css[] = sprintf($format, $demo . $page . '.css');
		} else if ($page == self::supplements) {
			$css[] = sprintf($format, $demo . $page . '.css');
		}
		return $css;
	}

	static function IconsFor($page) {
		$demo = 'demos/' . $page . '/';
		if ($page == 'spa')
			return $demo . 'css/fonts/spa-icons';
	}

	//needed since using stylesheets loaded in page...
	static function ReincludeIcons() {
		$base = 'css/icons/';
		return [
			$base . 'font-awesome',
			$base . 'bootstrap-icons',
			$base . 'unicons',
		];
	}
}

function themeScripts() {
	addScript('js/plugins.min', assetManager::theme);
	addScript('js/functions.bundle', assetManager::theme);
}
