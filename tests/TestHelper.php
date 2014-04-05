<?php
require_once 'PHP/CodeSniffer/CLI.php';

class TestHelper {

	protected $_rootDir;

	protected $_dirName;

	protected $_phpcs;

	public function __construct() {
		$this->_rootDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Loadsys';

		$includePath = explode(PATH_SEPARATOR, get_include_path());
		array_unshift($includePath,
			$this->_rootDir,
			$this->_rootDir . '/Sniffs'
		);
		set_include_path(implode(PATH_SEPARATOR, array_unique($includePath)));

		$this->_phpcs = new PHP_CodeSniffer_CLI();
	}

/**
 * Run PHPCS on a file.
 *
 * @param string $file to run.
 * @return string The output from phpcs.
 */
	public function runPhpCs($file) {
		$options = $this->_phpcs->getDefaults();
		$standard = $this->_rootDir . '/ruleset.xml';
		if (
			defined('PHP_CodeSniffer::VERSION') &&
			version_compare(PHP_CodeSniffer::VERSION, '1.5.0') != -1
		) {
			$standard = array($standard);
		}
		$options = array_merge($options, array(
			'encoding' => 'utf-8',
			'files' => array($file),
			'standard' => $standard,
		));

		// New PHPCS has a strange issue where the method arguments
		// are not stored on the instance causing weird errors.
		$reflection = new ReflectionProperty($this->_phpcs, 'values');
		$reflection->setAccessible(true);
		$reflection->setValue($this->_phpcs, $options);

		ob_start();
		$this->_phpcs->process($options);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

}
