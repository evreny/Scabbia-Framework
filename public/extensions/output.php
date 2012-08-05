<?php

if(extensions::isSelected('output')) {
	/**
	* Output Extension
	*
	* @package Scabbia
	* @subpackage ExtensibilityExtensions
	*/
	class output {
		/**
		* @ignore
		*/
		public static $effectList = array();

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'output',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
		}

		/**
		* @ignore
		*/
		public static function begin() {
			ob_start('output::flushOutput');
			ob_implicit_flush(false);

			$tArgs = func_get_args();
			array_push(self::$effectList, $tArgs);
		}

		/**
		* @ignore
		*/
		public static function &end($uFlush = true) {
			$tContent = ob_get_contents();
			ob_end_flush();

			foreach(array_pop(self::$effectList) as $tEffect) {
				$tContent = call_user_func($tEffect, $tContent);
			}

			if($uFlush) {
				echo $tContent;
			}

			return $tContent;
		}

		/**
		* @ignore
		*/
		public static function flushOutput($uContent) {
			return '';
		}
	}
}

?>