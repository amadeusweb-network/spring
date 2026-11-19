<?php
//From: https://bitbucket.org/ianamazi/sixtyplus/src/master/protected/models/Family.php
//Enhanced to support families of siblings
//Updated in v5.3 to support multiple families in the same md file, removing $name and using Around:
addStyle('family-tree', assetManager::core);

class Family
{
	private $text;

	public function __construct(string $text = null) {
		$this->text = $text;
	}

	public function renderInfo($people = false)
	{
		$sub = $people !== false;
		if (!$people) $people = self::parse($this->text);

		$father = ''; $mother = '';
		$grandFather = ''; $grandMother = '';
		$name = '';

		$siblings = array();
		$rows = array();

		$thirdcol = $sub ? '' : '<td></td>';
		foreach ($people as $p)
		{
			$r = $p['relation'];
			if ($r == 'Around' || $r == 'FamilyId') continue;

			if ($r == 'Father')
				$father = self::personTag($p);
			else if ($r == 'Mother')
				$mother = self::personTag($p);
			else if ($r == 'GrandFather')
				$grandFather = self::personTag($p);
			else if ($r == 'Mother')
				$grandMother = self::personTag($p);
			else if ($r == 'Brother' || $r == 'Sister')
				$siblings[] = [ 'tag' => self::personTag($p), 'sub' => isset($p['sub']) ? $this->renderInfo($p['sub'], $p['name']) : ''];
			else
				$rows[] = '<tr><td>' . $r . '</td><td>'
					. self::personTag($p)
					. '</td>' . (isset($p['sub']) ? '<td>' . $this->renderInfo($p['sub'], $p['name']) . '</td>' : '<td></td>')
					. '</tr>' . PHP_EOL;
		}

		$tbl = '<table class="family-tree" border="1">' . PHP_EOL;

		if ($grandFather || $grandMother) $tbl .= "<tr><td>$grandFather</td><td>$grandMother</td>$thirdcol</tr>" . PHP_EOL;

		if ($father || $mother) $tbl .= "<tr><td>$father</td><td>$mother</td>$thirdcol</tr>" . PHP_EOL;

		if ($name) $tbl .= "<tr><td colspan='2'><em>$name</em></td>$thirdcol</tr>" . PHP_EOL;

		if (count($siblings)) {
			$tbl .= '<tr><td rowspan="' . count($siblings). '">Siblings</td><td>' . $siblings[0]['tag'] . '</td><td>' . $siblings[0]['sub'] . '</td></tr>' . PHP_EOL;
			$siblings = array_splice($siblings, 1);
			foreach ($siblings as $sibling)
				$tbl .= '<tr><td>' . $sibling['tag'] . '</td><td>' . $sibling['sub'] . '</td></tr>' . PHP_EOL;
		}

		foreach ($rows as $row) $tbl .= $row;
		$tbl .= '</table>';
		return $tbl;
	}

	private static function personTag($p)
	{
		$male = array('Brother', 'Son', 'Husband', 'Father', 'GrandFather');
		$gender = $p['relation'] == 'Name' ? 'self' : (in_array($p['relation'], $male) ? 'male' : 'female');
		return '<span class="' . $gender . '" title="' . $p['relation'] . '">' . $p['name'] . '</span>';
	}

	private static function parse($text, $sub = false)
	{
		$lines = explode($sub ? ', ' : PHP_EOL, $text);
		$op = array();
		foreach ($lines as $line)
		{
			if ($line == '') continue;
			$bits = explode(': ', $line, 2);

			if (isset($bits[1]) && $pos = stripos($bits[1], ', ') !== false) {
				$comma = stripos($bits[1], ',');
				$sub = self::parse(substr($bits[1], $pos + $comma + 1), true);
				$bits[1] = substr($bits[1], 0, $pos + $comma - 1);
			} else {
				$sub = false;
			}

			$row = array('relation' => $bits[0], 'name' => isset($bits[1]) ? renderSingleLineMarkdown($bits[1], ['echo' => false]) : false);
			if ($sub) $row['sub'] = $sub;

			$op[] = $row;
		}
		return $op;
	}
}
