<?php

if(extensions::isSelected('http')) {
	/**
	* Http Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	class http {
		/**
		* @ignore
		*/
		public static $platform = null;
		/**
		* @ignore
		*/
		public static $crawler = null;
		/**
		* @ignore
		*/
		public static $crawlerType = null;
		/**
		* @ignore
		*/
		public static $isSecure = false;
		/**
		* @ignore
		*/
		public static $isAjax = false;
		/**
		* @ignore
		*/
		public static $isGet = false;
		/**
		* @ignore
		*/
		public static $isPost = false;
		/**
		* @ignore
		*/
		public static $isBrowser = false;
		/**
		* @ignore
		*/
		public static $isRobot = false;
		/**
		* @ignore
		*/
		public static $isMobile = false;
		/**
		* @ignore
		*/
		public static $languages = array();
		/**
		* @ignore
		*/
		public static $contentTypes = array();

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'http',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'io')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			// session trans sid
			ini_set('session.use_trans_sid', '0');

			// required for IE in iframe facebook environments if sessions are to work.
			header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

			// replace missing environment variables
			static $aEnvNames = array(
				'HTTP_ACCEPT',
				'HTTP_ACCEPT_LANGUAGE',
				'HTTP_HOST',
				'HTTP_USER_AGENT',
				'HTTP_REFERER',
				'SCRIPT_FILENAME',
				'PHP_SELF',
				'QUERY_STRING',
				'REQUEST_URI',
				'SERVER_ADDR',
				'SERVER_NAME',
				'SERVER_PORT',
				'HTTPS'
			);

			foreach($aEnvNames as &$tEnv) {
				if(isset($_SERVER[$tEnv]) && strlen($_SERVER[$tEnv]) > 0) {
					continue;
				}

				$_SERVER[$tEnv] = getenv($tEnv) or $_SERVER[$tEnv] = '';
			}

			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if(!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else {
				$_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR') or $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
			}

			// phpself and query string
			$_SERVER['PHP_SELF'] = str_replace(array('<', '>'), array('%3C', '%3E'), $_SERVER['PHP_SELF']);
			$tPhpSelfInfo = pathinfo($_SERVER['PHP_SELF']);
			if($tPhpSelfInfo['basename'] == 'index.php') {
				$_SERVER['PHP_SELF'] = $tPhpSelfInfo['dirname'] . '/';
			}

			$_SERVER['REQUEST_STRING'] = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['PHP_SELF']));

			$tPos = strpos($_SERVER['REQUEST_STRING'], '?');
			if($tPos !== false) {
				$_SERVER['QUERY_STRING'] = substr($_SERVER['REQUEST_STRING'], $tPos + 1);
			}

			foreach(config::get('/http/rewriteList', array()) as $tRewriteList) {
				$tReturn = preg_replace('|^' . $tRewriteList['@match'] . '$|', $tRewriteList['@forward'], $_SERVER['REQUEST_URI'], -1, $tCount);
				if($tCount > 0) {
					$_SERVER['REQUEST_URI'] = $tReturn;
					break;
				}
			}

			if($_SERVER['HTTPS'] == '1' || $_SERVER['HTTPS'] == 'on') {
				self::$isSecure = true;
			}

			if(strlen($_SERVER['HTTP_HOST']) == 0) {
				$_SERVER['HTTP_HOST'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

				if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
					$_SERVER['HTTP_HOST'] .= $_SERVER['SERVER_PORT'];
				}
			}

			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
				self::$isAjax = true;
			}

			if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
				self::$isPost = true;
			}
			else {
				self::$isGet = true;
			}

			$tAutoCheckUserAgents = intval(config::get('/http/userAgents/@autoCheck', '1'));

			if($tAutoCheckUserAgents) {
				self::checkUserAgent();
			}

			// self::$browser = get_browser(null, true);
			self::$languages = self::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE'], true);
			self::$contentTypes = self::parseHeaderString($_SERVER['HTTP_ACCEPT'], true);

			$_GET = self::parseGet($_SERVER['REQUEST_STRING']);

			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE); // GPC Order w/o session vars.

			events::register('output', events::callback('http::output'));
		}

		/**
		* @ignore
		*/
		public static function output($uParms) {
			if(self::$isAjax) {
				$tLastContentType = http::sentHeaderValue('Content-Type');
				$tArray = array(
					'"isSuccess": ' . (($uParms['error'][0] > 0) ? 'false' : 'true'),
					'"errorMessage": ' . (is_null($uParms['error']) ? 'null' : string::dquote($uParms['error'][1], true))
				);

				if($tLastContentType == false) {
					self::sendHeader('Content-Type', 'application/json', true);

					$tArray[] = '"object": ' . string::dquote($uParms['content'], true);
				}
				else {
					$tArray[] = '"object": ' . $uParms['content'];
				}

				$uParms['content'] = '{' . implode(', ', $tArray) . '}';
			}
		}

		/**
		* @ignore
		*/
		public static function checkUserAgent() {
			foreach(config::get('/http/userAgents/platformList', array()) as $tPlatformList) {
				if(preg_match('/' . $tPlatformList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$platform = $tPlatformList['@name'];
					break;
				}
			}

			foreach(config::get('/http/userAgents/crawlerList', array()) as $tCrawlerList) {
				if(preg_match('/' . $tCrawlerList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$crawler = $tCrawlerList['@name'];
					self::$crawlerType = $tCrawlerList['@type'];

					switch($tCrawlerList['@type']) {
					case 'bot':
						self::$isRobot = true;
						break;
					case 'mobile':
						self::$isMobile = true;
						break;
					case 'browser':
					default:
						self::$isBrowser = true;
						break;
					}

					break;
				}
			}
		}

		/**
		* @ignore
		*/
		public static function checkLanguage($uLanguage = null) {
			if(is_null($uLanguage)) {
				return self::$languages;
			}

			return in_array(strtolower($uLanguage), self::$languages);
		}

		/**
		* @ignore
		*/
		public static function checkContentType($uContentType = null) {
			if(is_null($uContentType)) {
				return self::$contentTypes;
			}

			return in_array(strtolower($uContentType), self::$contentTypes);
		}

//		public static function is($uType) {
//			$tType = 'is' . ucfirst($uType);
//			return self::${$tType};
//		}
//
//		public static function __callStatic($uMethod, $uArgs) {
//			return self::${$uMethod};
//		}

		/**
		* @ignore
		*/
		public static function &xss(&$uString) {
			if(is_string($uString)) {
				$tString = str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); // '&' => '&#38;'
				return $tString;
			}

			return $uString;
		}

		/**
		* @ignore
		*/
		public static function encode($uString) {
			return urlencode($uString);
		}

		/**
		* @ignore
		*/
		public static function decode($uString) {
			return urldecode($uString);
		}

		/**
		* @ignore
		*/
		public static function encodeArray($uArray) {
			$tReturn = array();

			foreach($uArray as $tKey => &$tValue) {
				$tReturn[] = urlencode($tKey) . '=' . urlencode($tValue);
			}

			return implode('&', $tReturn);
		}

		/**
		* @ignore
		*/
		public static function copyStream($tFilename) {
			$tInput = fopen('php://input', 'rb');
			$tOutput = fopen($tFilename, 'wb');
			stream_copy_to_stream($tInput, $tOutput);
			fclose($tOutput);
			fclose($tInput);
		}

		/**
		* @ignore
		*/
		public static function baseUrl() {
			if(http::$isSecure) {
				return 'https://' . $_SERVER['HTTP_HOST'];
			}

			return 'http://' . $_SERVER['HTTP_HOST'] . framework::$siteroot;
		}

		/**
		* @ignore
		*/
		public static function secureUrl($uUrl) {
//			if(http::$isSecure && substr($uUrl, 0, 7) == 'http://') {
//				return 'https://' . substr($uUrl, 7);
//			}

			return $uUrl;
		}

		/**
		* @ignore
		*/
		public static function sendStatus($uStatusCode) {
			switch((int)$uStatusCode) {
			case 100: $tStatus = 'HTTP/1.1 100 Continue'; break;
			case 101: $tStatus = 'HTTP/1.1 101 Switching Protocols'; break;
			case 200: $tStatus = 'HTTP/1.1 200 OK'; break;
			case 201: $tStatus = 'HTTP/1.1 201 Created'; break;
			case 202: $tStatus = 'HTTP/1.1 202 Accepted'; break;
			case 203: $tStatus = 'HTTP/1.1 203 Non-Authoritative Information'; break;
			case 204: $tStatus = 'HTTP/1.1 204 No Content'; break;
			case 205: $tStatus = 'HTTP/1.1 205 Reset Content'; break;
			case 206: $tStatus = 'HTTP/1.1 206 Partial Content'; break;
			case 300: $tStatus = 'HTTP/1.1 300 Multiple Choices'; break;
			case 301: $tStatus = 'HTTP/1.1 301 Moved Permanently'; break;
			case 302: $tStatus = 'HTTP/1.1 302 Found'; break;
			case 303: $tStatus = 'HTTP/1.1 303 See Other'; break;
			case 304: $tStatus = 'HTTP/1.1 304 Not Modified'; break;
			case 305: $tStatus = 'HTTP/1.1 305 Use Proxy'; break;
			case 307: $tStatus = 'HTTP/1.1 307 Temporary Redirect'; break;
			case 400: $tStatus = 'HTTP/1.1 400 Bad Request'; break;
			case 401: $tStatus = 'HTTP/1.1 401 Unauthorized'; break;
			case 402: $tStatus = 'HTTP/1.1 402 Payment Required'; break;
			case 403: $tStatus = 'HTTP/1.1 403 Forbidden'; break;
			case 404: $tStatus = 'HTTP/1.1 404 Not Found'; break;
			case 405: $tStatus = 'HTTP/1.1 405 Method Not Allowed'; break;
			case 406: $tStatus = 'HTTP/1.1 406 Not Acceptable'; break;
			case 407: $tStatus = 'HTTP/1.1 407 Proxy Authentication Required'; break;
			case 408: $tStatus = 'HTTP/1.1 408 Request Timeout'; break;
			case 409: $tStatus = 'HTTP/1.1 409 Conflict'; break;
			case 410: $tStatus = 'HTTP/1.1 410 Gone'; break;
			case 411: $tStatus = 'HTTP/1.1 411 Length Required'; break;
			case 412: $tStatus = 'HTTP/1.1 412 Precondition Failed'; break;
			case 413: $tStatus = 'HTTP/1.1 413 Request Entity Too Large'; break;
			case 414: $tStatus = 'HTTP/1.1 414 Request-URI Too Long'; break;
			case 415: $tStatus = 'HTTP/1.1 415 Unsupported Media Type'; break;
			case 416: $tStatus = 'HTTP/1.1 416 Requested Range Not Satisfiable'; break;
			case 417: $tStatus = 'HTTP/1.1 417 Expectation Failed'; break;
			case 500: $tStatus = 'HTTP/1.1 500 Internal Server Error'; break;
			case 501: $tStatus = 'HTTP/1.1 501 Not Implemented'; break;
			case 502: $tStatus = 'HTTP/1.1 502 Bad Gateway'; break;
			case 503: $tStatus = 'HTTP/1.1 503 Service Unavailable'; break;
			case 504: $tStatus = 'HTTP/1.1 504 Gateway Timeout'; break;
			case 505: $tStatus = 'HTTP/1.1 505 HTTP Version Not Supported'; break;
			default:
				return;
			}

			self::sendHeader($tStatus);
		}

		/**
		* @ignore
		*/
		public static function sendStatus404() {
			self::sendStatus(404);
		}

		/**
		* @ignore
		*/
		public static function sendHeader($uHeader, $uValue = null, $uReplace = false) {
			if(isset($uValue)) {
				header($uHeader . ': ' . $uValue, $uReplace);
			}
			else {
				header($uHeader, $uReplace);
			}
		}

		/**
		* @ignore
		*/
		public static function sentHeaderValue($uKey) {
			foreach(headers_list() as $tHeaderRow) {
				$tHeader = explode(': ', $tHeaderRow, 2);

				if(count($tHeader) < 2) {
					continue;
				}

				if(strcasecmp($tHeader[0], $uKey) == 0) {
					return $tHeader[1];
				}
			}

			return false;
		}

		/**
		* @ignore
		*/
		public static function sendFile($uFilePath, $uAttachment = false, $uFindMimeType = true) {
			$tExtension = pathinfo($uFilePath, PATHINFO_EXTENSION);

			if($uFindMimeType) {
				$tType = io::getMimeType($tExtension);
			}
			else {
				$tType = 'application/octet-stream';
			}

			self::sendHeaderExpires(0); // 1970
			self::sendHeaderNoCache();
			self::sendHeader('Accept-Ranges', 'bytes', true);
			self::sendHeader('Content-Type', $tType, true);
			if($uAttachment) {
				self::sendHeader('Content-Disposition', 'attachment; filename=' . pathinfo($uFilePath, PATHINFO_BASENAME) . ';', true);
			}
			self::sendHeader('Content-Transfer-Encoding', 'binary', true);
			//! filesize problem
			// self::sendHeader('Content-Length', filesize($uFilePath), true);
			self::sendHeaderETag(md5_file($uFilePath));
			readfile($uFilePath, false);
			framework::end(0);
		}

		/**
		* @ignore
		*/
		public static function sendHeaderLastModified($uTime, $uNotModified = false) {
			self::sendHeader('Last-Modified', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);

			if($uNotModified) {
				self::sendStatus(304);
			}
		}

		/**
		* @ignore
		*/
		public static function sendHeaderExpires($uTime) {
			self::sendHeader('Expires', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);
		}

		/**
		* @ignore
		*/
		public static function sendRedirect($uLocation, $uTerminate = true) {
			self::sendHeader('Location', $uLocation, true);

			if($uTerminate) {
				framework::end(0);
			}
		}

		/**
		* @ignore
		*/
		public static function sendRedirectPermanent($uLocation, $uTerminate = true) {
			self::sendStatus(301);
			self::sendHeader('Location', $uLocation, true);

			if($uTerminate) {
				framework::end(0);
			}
		}

		/**
		* @ignore
		*/
		public static function sendHeaderETag($uHash) {
			self::sendHeader('ETag', '"' . $uHash . '"', true);
		}

		/**
		* @ignore
		*/
		public static function sendHeaderNoCache() {
			self::sendHeader('Pragma', 'public', true);
			self::sendHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
			self::sendHeader('Cache-Control', 'pre-check=0, post-check=0, max-age=0');
		}

		/**
		* @ignore
		*/
		public static function sendCookie($uCookie, $uValue, $uExpire = 0) {
			setrawcookie($uCookie, self::encode($uValue), $uExpire);
		}

		/**
		* @ignore
		*/
		public static function removeCookie() {
			setrawcookie($uCookie, '', time() - 3600);
		}

		/**
		* @ignore
		*/
		public static function parseGet($uQueryString) {
			$tParsingType = config::get('/http/request/@parsingType', '0');
			$tDefaultParameter = config::get('/http/request/@getParameters', '?&');
			$tDefaultKey = config::get('/http/request/@getKeys', '=');
			$tDefaultSeperator = config::get('/http/request/@getSeperator', '/');

			if($tParsingType == '1') {
				return string::parseQueryString($uQueryString, $tDefaultParameter, $tDefaultKey);
			}

			if($tParsingType == '2') {
				return string::parseQueryString($uQueryString, $tDefaultParameter, $tDefaultKey, $tDefaultSeperator);
			}
		}

		/**
		* @ignore
		*/
		public static function parseHeaderString($uString, $uLowerAll = false) {
			$tResult = array();

			foreach(explode(',', $uString) as $tPiece) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$tPiece = trim($tPiece);
				if($uLowerAll) {
					$tResult[] = strtolower(substr($tPiece, 0, strcspn($tPiece, ';')));
				}
				else {
					$tResult[] = substr($tPiece, 0, strcspn($tPiece, ';'));
				}
			}

			return $tResult;
		}

		/**
		* @ignore
		*/
		public static function buildQueryString($uArray) {
			//! $tDefaultKey = config::get('/http/request/@getKeys', '=');
			/*
			if(isset($uArray['_segments'])) {
				$tString = '/' . implode('/', $uArray['_segments']) . '?';
			}
			else {
				$tString = '?';
			}
			*/
			$tString = '?';

			foreach($uArray as $tKey => &$tItem) {
				if($tKey == '_segments' || $tKey == '_hash') {
					continue;
				}

				if(is_null($tItem)) {
					$tString .= $tKey . '&';
					continue;
				}

				$tString .= $tKey . '=' . $tItem . '&';
			}

			return substr($tString, 0, -1);
		}

		/**
		* @ignore
		*/
		public static function get($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_GET)) {
				return $uDefault;
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, self::xss($_GET[$uKey]));

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_GET[$uKey]);
		}

		/**
		* @ignore
		*/
		public static function post($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_POST)) {
				return $uDefault;
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, self::xss($_POST[$uKey]));

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_POST[$uKey]);
		}

		/**
		* @ignore
		*/
		public static function cookie($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_COOKIE)) {
				return $uDefault;
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, self::xss($_COOKIE[$uKey]));

				return call_user_func_array('string::filter', $tArgs);
			}

			return self::xss($_COOKIE[$uKey]);
		}
	}
}

?>