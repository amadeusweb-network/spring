<?php
class main {
	static function defaultSocial($prepend = []) {
		return socialBuilder::create($prepend)
			->addLinkedIn('in/imran-ali-namazi/', 'Imran Ali Namazi')
			->addDawn(true, true)
			->addGithubGroup()
			->addUtilityGroup()
			->getItems();
	}

	static function defaultSearches() {
		return [
			'amadeusweb' => ['code' => 'c0a96edc60a44407a', 'name' => 'AmadeusWeb Network&nbsp;&nbsp;', 'description' => 'All AmadeusWeb sites from 2025'],
			'imranali' =>   ['code' => '63a208ccffd5b4492', 'name' => 'Imran\'s Writing / Poems&nbsp;&nbsp;', 'description' => 'All of Imran\'s writing since 2017'],
			'yieldmore' =>  ['code' => '29e47bd630f4c73c0', 'name' => 'YieldMore Network&nbsp;', 'description' => 'All YieldMore sites from 2013 to 2024'],
			'sriaurobindo'=>['code' => '84d24b3918cbd5f1a', 'name' => 'Mother Sri Aurobindo Sites', 'description' => 'From the Aurobindonian World'],
		];
	}

	static function runAndReturn() {
		doToBuffering(1);
		main::analytics();
		if (!getQueryParameter(VARQueryContent))
			main::chat();
		$result = doToBuffering(2);
		doToBuffering(3);
		return $result;
	}

	static function chat() {
		$val = variable(VARChatraID);
		$val = $val && $val != 'none' ? ($val != VARUseAmadeusWeb ? $val : 'wqzHJQrofB47q5oFj') : false;
		if (!$val) return;
		variable(VARChatraID, $val);
		runModule('chatra');
	}

	static function analytics() {
		$val = variable(VARGoogleAnalytics);
		$val = $val && $val != 'none' ? ($val != VARUseAmadeusWeb ? $val : 'G-LN2JB9GLDC') : false;
		if (!$val) return;
		variable(VARGoogleAnalytics, $val);
		runModule(VARGoogleAnalytics);
	}
}
