<?php

namespace Clevis\Version\DI;

use Nette\DI;
use Nette\Neon\Neon;
use Tracy\Debugger;


class VersionExtension extends DI\CompilerExtension
{

	static $samplePath = '%appDir%/config/config.local.sample.neon';
	static $localPath = '%appDir%/config/config.local.neon';

	public function beforeCompile()
	{
		$config = $this->getConfig();
		if (count($config) !== 1 || !isset($config[0]))
		{
			throw new ConfigVersionExtension("Version number not set in config.local.neon. Add this root key: 'version: [1]' to your local config.");
		}
		$old = $config[0];

		$raw = file_get_contents($this->getContainerBuilder()->expand(static::$samplePath));
		$sample = Neon::decode($raw);
		if (!isset($sample['version'][0]) || count($sample['version']) !== 1)
		{
			throw new ConfigVersionExtension("Version number not set in sample config. Add this root key: 'version: [1]' to your sample config.");
		}
		$new = $sample['version'][0];

		if ($old !== $new)
		{
			Debugger::getBlueScreen()->addPanel($this->getBlueScreenPanelCallback());
			throw new ConfigVersionExtension('Sample config neon is not compatible with your current local config. Update your local config according to the diff below.');
		}
	}

	public function getBlueScreenPanelCallback()
	{
		list($output, $command) = $this->getDiff();
		return function() use ($command, $output) {
			echo '<div class="panel">';
			echo '<h2><a href="#tracyConfigFileDiff" class="tracy-toggle">Config file diff</a></h2>';
			echo '<div id="tracyConfigFileDiff" class="inner">';
			echo '<p><code>' . $command . '</code></p>';
			echo '<pre class="neon">';
			echo $output;
			echo '</pre>';
			echo '</div>';
			echo '</div>';
		};
	}

	protected function getDiff()
	{
		$a = $this->getContainerBuilder()->expand(static::$samplePath);
		$b = $this->getContainerBuilder()->expand(static::$localPath);

		$lines = [];
		$command = 'git diff --no-index -U1 ' . escapeshellarg($a) . ' ' . escapeshellarg($b);
		exec($command, $lines);

		array_shift($lines);
		array_shift($lines);

		foreach ($lines as &$line)
		{
			if (strpos($line, '+') === 0)
			{
				$line = '<span style="color: #D24; font-weight: bold">' . $line . '</span>';
			}
			else if (strpos($line, '-') === 0)
			{
				$line = '<span style="color: #080; font-weight: bold">' . $line . '</span>';
			}
			else
			{
				$line = '<span style="color: gray;">' . $line . '</span>';
			}
		}

		return [implode("\n", $lines), $command];
	}

}

class ConfigVersionExtension extends \LogicException {}
