<?php
/**
 * Author: Vadim Belyaev
 * Date: 19.04.2016 11:19
 * http://magwai.ru
 */

namespace Editor;

class Paker
{

	public static $validCode = true;


	public static function OnPageStart()
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php';
		$code = file_get_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php');

		$codePaker = self::getCode($code, $path, (isset($_REQUEST['level'])) ? $_REQUEST['level'] : self::$levelMax);
		echo $codePaker;
	}

	public static function getCode($code, $path, $levelMax, $levelCurrent = 1) {

		if ($levelMax == 1) {
			return $code;
		}

		return preg_replace_callback(
			'/require(_once)?\((.*")(.*\.php)"\);/',
			function($match) use ($path, $levelMax, $levelCurrent) {

				$relativePath = $match[3];
				$absolutePathCode = $match[2];
				$absolutePath = self::getAbsolutePath($absolutePathCode, $path);

				//\Bitrix\Main\Diag\Debug::dump(array($absolutePath));

				//\Bitrix\Main\Diag\Debug::dump(array($absolutePath.$relativePath));

				$code = file_get_contents($absolutePath . $relativePath);
				$path = $absolutePath . $relativePath;


				if ($levelCurrent >= ($levelMax - 1) && self::$validCode) {
					$code = self::validCode($code, $path);
				}

				$code = self::trimPHPTag($code);

				return ($levelCurrent >= ($levelMax - 1)) ? $code : self::getCode($code, $path, $levelMax, ($levelCurrent+1));
			}, $code);
	}

	public static function getAbsolutePath($absolutePathCode, $path) {
		$arRegexSearch = array(); // TODO

		$arStringSearch = array(
			'$_SERVER["DOCUMENT_ROOT"]',
			'dirname(__FILE__)',
			'BX_ROOT',
		);

		$arStringRelace = array(
			$_SERVER["DOCUMENT_ROOT"],
			dirname($path),
			BX_ROOT,
		);

		foreach($arStringSearch as $key => $search) {
			$absolutePathCode = str_replace($search, $arStringRelace[$key], $absolutePathCode);
		}

		$absolutePath = str_replace(array('\040', '.', '"'), '', $absolutePathCode);

		return $absolutePath;
	}

	public static function trimPHPTag($code) {
		return preg_replace('/^(<\?(php)?+)?+(.*)(\?>)?+$/iUs', "$3", $code);
	}

	public static function validCode($code, $path) {
		return str_replace('dirname(__FILE__)', '"' . dirname($path) . '"', $code);
	}

}
 
 