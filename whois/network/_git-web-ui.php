<?php
/*****
 * This "AmadeusWeb Core's git integrations" feature is proprietary, Source-available software!
 * It is licensed for distribution at the sole discretion of it's owner Imran Ali Namazi.
 * v 3.0
 * https://github.com/amadeusweb-network/spring/blob/main/whois/network/_git-web-ui.php
 * 
 * These are command line scripts relating to a site for doing
 * 		"git clone"
 * 		"git pull"
 * 		"git log"
 * 
 * Setup: simply run the batch file "_setup.bat" in this directory after every update to this file, then open:
 * 		http://localhost/spring/network/repos/
 * 
 * Never make changes to the copy in the "public_html" root folder
 * 
 * **** To keep your server safe, never deploy this file and the one that calls it.
 */

DEFINE('NEWLINE', "\r\n");
DEFINE('BRNL', '<br />' . NEWLINE);

function contains($haystack, $needle) {
	return stripos($haystack, $needle) !== false;
}

function chunkCommits($output) {
	$chunks = [];
	$newChunk = false;
	foreach ($output as $ix => $line) {
		if (contains($line, 'commit ')) {
			if ($newChunk) $chunks[] = $newChunk;
			$newChunk = [];
		}
		$newChunk[$ix] = $line;
	}
	$chunks[] = $newChunk;

	return print_r($chunks, true);
}

function _executePopen($msg, $command) {
	echo 'This script must be run in a context where the PHP environment has permission to interact with the Windows desktop GUI' . BRNL
		. 'If nothing happens, go to > services.msc > apache 2.2 > properties > logon >  Local System account > ' . BRNL
		. 'Check the box that says "Allow service to interact with desktop" > apply > restart' . BRNL . BRNL;
	echo $msg . BRNL . 'About to popen: ' . $command . BRNL . BRNL;

	$handle = popen('start /B "' . $command . '"', 'r');

	if ($handle === false) {
		die("Failed to execute command.");
	}

	// Read the output line by line until End-Of-File (EOF)
	while (!feof($handle)) {
		$buffer = fgets($handle);
		echo $buffer;
	}

	// Close the pipe and get the termination status
	$exitCode = pclose($handle);
	echo BRNL . BRNL . "Process exited with code: " . $exitCode;
}

function _executeCommand($name, $command) {
	$output = null;
	$result = null;
	exec($command, $output, $result);
	if ($result == 0) {
		_blocker($name . ' exited with nothing', false);
	} else {
		_blocker($name . ' exited with value: ' . $result, false, '#afa');
	}

	$output_r = contains($command, 'log') ? chunkCommits($output) : print_r($output, true);
	echo '<h3>' . $name . '</h3><blockquote style="border-left: 5px solid gray; padding: 15px; background-color: #c8c8e10; margin-left: 0";>'
		. $command . '</blockquote><pre>' . $output_r . '</pre><hr />';
	return $output;
}

function _blocker($message, $exit = true, $color = '#FFE4E1') {
	echo '<div style="margin: 60px auto; max-width: 400px; background-color: ' . $color . '; padding: 40px; border-radius: 30px; text-align: center;">' . $message . '</div>';
	if ($exit) exit;
}

$git = isset($_GET['git-action']) ? $_GET['git-action'] : false;

if (!$git) _blocker('Appears to be a broken link! Contact dawn.amadeusweb.com.');

if ($git) {
	if ($git == 'pull') $command = 'git pull';
	else if ($git == 'clone') $command = 'git clone "' . $_GET['git-url'] . '" "./"';
	else if ($git == 'log') $command = 'git log -n 25';
	else if ($git == 'diff') $command = 'repostatus';
	else _blocker('Not supported git-action: ' . $git);

	$site = $_GET['site'];

	if ($git == 'clone' && contains( strtolower($site), 'undefined'))
		_blocker('Clone path invalid! Contact amadeusweb.in.');

	$abs = __DIR__ . '/' . $site;
	if ($git == 'clone' && !is_dir($abs))
		mkdir($abs);
	
	if ($git == 'diff') {
		$abs = str_replace('/', '\\', realpath($abs));
		$cmd = sprintf('TortoiseGitProc.exe /command:%s /path:%s', $command, $abs);

		_executePopen('Working on site: ' . $site . ' - action = ' . $git, $cmd);
		die();
	}

	chdir($site);

	echo '<section>';
	$output = _executeCommand('Working on site: ' . $site . ' - action = ' . $git, $command);

	//handle the "detected dubious ownership in repository"
	if (contains(end($output), '--add safe.directory')) {
		$real = str_replace('\\', '/', realpath('../../' . $site));
		_executeCommand('Making it a safe directory', 'git config --global --add safe.directory "' . $real . '" 2>&1');
		_executeCommand('Retry of Update', $command);
	}

	echo '</section>';
}