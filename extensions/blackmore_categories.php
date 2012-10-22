<?php

if(extensions::isSelected('blackmore_categories')) {
	/**
	* Blackmore: Categories Extension
	*
	* @package Scabbia
	* @subpackage blackmore_categories
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, blackmore
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore_categories extends controller {
		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'blackmore_categories',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'resources', 'blackmore')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			events::register('blackmore_buildMenu', 'blackmore_categories::blackmore_buildMenu');
		}

		/**
		* @ignore
		*/
		public static function blackmore_buildMenu($uParms) {
			$uParms['menuItems'][] = array(
				'title' => 'Categories',
				'link' => mvc::url('blackmore/categories'),
				'subitems' => array(
					array(
						'title' => 'New Category',
						'link' => mvc::url('blackmore/categories/new')
					),
					array(
						'title' => 'All Categories',
						'link' => mvc::url('blackmore/categories/all')
					),
				)
			);
		}
	}
}

?>