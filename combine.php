<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PlaylistCombiner
{
	protected $plsHash = [];
	protected $plsList = [];

	public function __construct()
	{
	}

	public function loadPlaylist($fn, $options = [])
	{
		echo "Обрабатываем плейлист: $fn" . PHP_EOL;
		$allToGrp = $options['all-grp'] ?? '';
		$noDups = $options['nodups'] ?? false;
		$pls_lines = file($fn);
		$l = 0;
		$chn = [];
		$chnName = '';
		foreach ($pls_lines as $line)
		{
			$l++;
			$line = trim($line);
			if ($l == 1)
			{
				if ($line != '#EXTM3U')
					echo "WARNING: неизвестный заголовок плейлиста: ($line)" . PHP_EOL;
				continue;
			}
			if (substr($line, 0, 1) == '#')
			{
				// #EXTINF:0 tvg-rec="1",Пятый канал
				if (substr($line, 0, 8) == '#EXTINF:')
				{
					$chnInfo = explode(',', $line);
					$chnName = $chnInfo[1];
					if (isset($this->plsHash[$chnName]) && !$noDups)
					{
						echo 'WARNING: найден канал с повторяющимся названием: [' . $chnName . ']' . PHP_EOL;
						$i = 0;
						do
						{
							$i++;
							$newName = $chnName . ' (' . $i . ')';
						} while (isset($this->plsHash[$newName]));
						//echo "Новый индекс: $i; ### $newName\n";
						$chnName = $newName;
						$chn[] = $chnInfo[0] . ',' . $newName;
					}
					else
						$chn[] = $line;
					if ($allToGrp)
						$chn[] = '#EXTGRP:' . $allToGrp;
				}
				else
				if (substr($line, 0, 8) == '#EXTGRP:')
				{
					if ($allToGrp)
					{
						echo "WARNING: для канала указана своя группа, игнорируем: $chnName ($line)" . PHP_EOL;
						continue;
					}
					else
						$chn[] = $line;
				}
				else
					throw new Exception('Неизвестная директива в файле конфигурации, строка № ' . $l);
			}
			else
			{
				$chn[] = $line;
				if (!$chnName)
					echo "ERROR:   пустое имя канала ($l), пропускаем канал" . PHP_EOL;
				$this->plsHash[$chnName] = implode("\n", $chn);
				$this->plsList[] = $chnName;
				$chn = [];
				$chnName = '';
			}
		}
	}

	public function writePlaylist()
	{
		$newPLS = "#EXTM3U\n";
		foreach ($this->plsList as $chnName)
			$newPLS .= $this->plsHash[$chnName] . "\n";
		echo "Записываем результирующий плейлист...\n";
		file_put_contents('new.m3u', $newPLS);
	}

	public function run($conf_lines)
	{
		$l = 0;
		foreach ($conf_lines as $line)
		{
			$l++;
			$item = array_map( function ($s) { return trim($s); }, explode('=', $line) );
			if ( !(is_array($item) && (count($item) == 2)) )
				throw new Exception('Ошибка в файле конфигурации, строка № ' . $l);
			$cmd  = array_map( function ($s) { return trim($s); }, explode(':', $item[1]) );
			if (!is_array($cmd)) $cmd = [$cmd];
			$options = [];
			if ($cmd[0] == 'full')
			{
				$options = [];
			}
			else
			if ($cmd[0] == 'all-grp')
			{
				if (!($cmd[1] ?? ''))
					throw new Exception('Ошибка в файле конфигурации: не задано имя группы');
				$options = [$cmd[0] => $cmd[1]];
			}
			else
			if ($cmd[0] == 'nodups')
			{
				$options = [$cmd[0] => 1];
			}
			else
				throw new Exception('Команда не поддерживается: ' . $cmd[0]);
                        $this->loadPlaylist($item[0], $options);
		}
		$this->writePlaylist();
	}
}

$conf_lines = file('combine.ini');
$cmb = new PlaylistCombiner;
$cmb->run($conf_lines);
