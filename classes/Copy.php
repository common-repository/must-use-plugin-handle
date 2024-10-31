<?php

namespace muhandle;

class Copy {

	/**
	 * Get all files of an dir (recursive)
	 * @param string $sDir
	 * @return array
	 */
	public static function getAllFiles($sDir) {
		$aFileList = [];
		foreach (scandir($sDir) as $sFile) {
			if (substr($sFile, 0, 1) == '.')
				continue;
			if (is_dir($sDir . DIRECTORY_SEPARATOR . $sFile)) {
				$aFileList[$sFile] = self::getAllFiles($sDir . DIRECTORY_SEPARATOR . $sFile);
			}
			else {
				$aFileList[] = $sFile;
			}
		}
		return $aFileList;
	}
	
	/**
	 * Copy complete dir (recursive)
	 * @param string $sSource
	 * @param string $sTarget
	 * @return type
	 */
	public static function copyDir($sSource, $sTarget) {
		if (!is_dir($sSource)) {
			return;
		}
		if (!is_dir($sTarget)) {
			mkdir($sTarget);
		}
		self::copyRecurse(self::getAllFiles($sSource), $sSource, $sTarget);
	}
	
	/**
	 * Copy all files and directories (recursive)
	 * @param array $aFiles
	 * @param string $sSoruce
	 * @param string $sTarget
	 */
	public static function copyRecurse($aFiles, $sSoruce, $sTarget) {
		foreach ($aFiles as $sDir => $mFile) {
			if (is_array($mFile)) {
				if(!is_dir($sTarget . DIRECTORY_SEPARATOR . $sDir)) {
					mkdir($sTarget . DIRECTORY_SEPARATOR . $sDir);
				}
				self::copyRecurse($mFile, $sSoruce . DIRECTORY_SEPARATOR . $sDir, $sTarget . DIRECTORY_SEPARATOR . $sDir);
			}
			else {
				copy($sSoruce . DIRECTORY_SEPARATOR . $mFile, $sTarget . DIRECTORY_SEPARATOR . $mFile);
			}
		}
	}

}
