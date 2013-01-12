<?php

	namespace Scabbia;

	/**
	 * Datasource Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class datasource {
		/**
		 * @ignore
		 */
		public $id;
		/**
		 * @ignore
		 */
		public $provider;
		/**
		 * @ignore
		 */
		public $cache = array();
		/**
		 * @ignore
		 */
		public $stats = array('cache' => 0, 'query' => 0);

		/**
		 * @ignore
		 */
		public function __construct($uConfig) {
			$this->id = $uConfig['id'];

			$tProvider = $uConfig['provider'];
			$this->provider = new $tProvider ($uConfig);
		}

		/**
		 * @ignore
		 */
		public function __destruct() {
			$this->close();
		}

		/**
		 * @ignore
		 */
		public function open() {
			$this->provider->open();
		}

		/**
		 * @ignore
		 */
		public function close() {
			$this->provider->close();
			$this->provider = null;
		}

		/**
		 * @ignore
		 */
		public function serverInfo() {
			$this->open();

			return $this->provider->serverInfo();
		}
	}

	?>