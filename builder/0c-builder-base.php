<?php
//added in 8..5
abstract class builderBase {
	public $settings = [];

	//called in constructor, check so doesnt override
	protected function setDefault($key, $value) {
		if (!isset($this->settings[$key]))
			$this->set([$key => $value]);
		return $this;
	}

	protected function settingIs($key, $value = true) {
		return isset($this->settings[$key]) ? $this->settings[$key] == $value : BOOLNo;
	}

	protected function getSetting($key) {
		return isset($this->settings[$key]) ? $this->settings[$key] : BOOLNo;
	}

	function setValue($key, $value) {
		return $this->set([$key => $value]);
	}

	function set($override = []) {
		foreach ($override as $key => $value)
			$this->settings[$key] = $value;
		return $this;
	}

	function unset($keys = []) {
		if (is_string($keys))
			$keys = [$keys];

		foreach ($keys as $key)
			unset($this->settings[$key]);

		return $this;
	}
}
