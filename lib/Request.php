<?php

/**
 * Description of Request
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
class Request {
	
	/** @var string  */
	protected $serverUrl='http://api.ethnicmedia.us';
	
	/** @var string  */
	private $cacheDir;
	
	/** @var bool  */
	protected $debug=true;
	/** @var FirePHP  */
	protected $FB;
	/** @var string  */
	private $notModifiedString='not modified';


	function __construct($debug=true,$serverUrl='http://api.ethnicmedia.us') {
		$this->serverUrl = $serverUrl;
		$this->debug     = $debug;
		if ($this->debug) {
			include_once 'FirePHP/FirePHP.class.php';
			$this->FB= FirePHP::getInstance(true);
		}
		$this->cacheDir = dirname(__FILE__).'/../cache';
		if (!is_dir($this->cacheDir)) {
			if (!is_writable(dirname(__FILE__).'/..')){
				throw new Exception('Cache directory not found and parent directory not writable ! "'.$this->cacheDir.'"', '500');
			}else{
				mkdir($this->cacheDir);
			}
		}
		if (!is_writable($this->cacheDir)) {
			throw new Exception('Cache directory not writable!', '500');
		}
		
	}

	/**
	 * Очистить кэш
	 * @param int $userId
	 */
	public function flush_cache($userId,$lang){
		if ($userId) {
			array_map("unlink", glob($this->cacheDir."/*_".$userId));
			array_map("unlink", glob($this->cacheDir."/main_layout*_".$userId.'_'.$lang));
		}
	}

	/**
	 * Сделать запрос и получить ответ от сервера
	 * @param string $method
	 * @param string $uri
	 * @param string  $querry
	 * @param string  $json
	 * @return array
	 */
	private function _makeRequest($method,$uri,$querry=NULL,$json=NULL){
	// Connect 
		$adb_handle = curl_init();
		// Compose querry
		$options = array(
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_HEADER         => false,
		  CURLOPT_URL            => $this->serverUrl.$uri."?".  $querry,
		  CURLOPT_CUSTOMREQUEST  => $method, // GET POST PUT PATCH DELETE HEAD OPTIONS 
		  CURLOPT_POSTFIELDS     => $json,
		  CURLOPT_REFERER        => $_SERVER['SERVER_NAME'],
		  CURLOPT_TIMEOUT        => 10,
		); 
		curl_setopt_array($adb_handle,$options); 
		
		if ($this->debug) {
			$this->FB->info('Start request to '.$this->serverUrl.$uri."?".$querry);
			$this->_logGroupe('request data', $options);
		}
		// send request and wait for responce
		$result = curl_exec($adb_handle);
		
//			print_r($result);
			
		$responce =  json_decode($result,true);
		if(!$responce) { // 304
			$responce = array('result'=> $this->notModifiedString);
		}		
		if ($this->debug) {
			if (curl_error($adb_handle)) {
				$this->FB->error('CURL ERROR : '.curl_error($adb_handle));
			}
		$this->_logGroupe('response data', $responce);
		}
		return $responce ;
	}
	
	/**
	 * Сделать запрос и сравнить с кэшем
	 * @param string $method   HTTP метод
	 * @param string $url      Урл сервера
	 * @param string $querry   URI запроса
	 * @param string $data     данные
	 * @param string $filename Файл кэша
	 */
	public function getRequest($method,$url,$querry,$data,$filename=''){

		$cacheData = ($filename)? $this->_loadCacheFile($filename):null; 
		
		$timestamp = (isset($cacheData['timestamp']) and $cacheData['timestamp'])?$cacheData['timestamp']:null;
		$data['timestamp']=$timestamp;
		
		$result=  $this->_makeRequest($method, $url,$querry.'&TIMESTAMP='.$timestamp, $data);
		// без изменений
		//304 Not Modified
		if (isset($result['result']) and $result['result'] == $this->notModifiedString){
			if ($this->debug) {
				$this->FB->info('Not modified  ');
			}
			return $cacheData['data'];
		}
		// Есть новые данные
		if(isset($result['data'])) {
			$newData = array(
					'timestamp' => $result['timestamp'],
					'data'      => $result['data']
					);
			$this->_writeCacheFile($filename, $newData);
			return $result['data'];
		}

	}
	
	/**
	 * Загрузить файл
	 * @param string $filename
	 * @param $delete
	 * @return bool || array
	 */
	private function _loadCacheFile($filename,$delete=false){
		if(file_exists($this->cacheDir.'/'.$filename) and is_readable($this->cacheDir.'/'.$filename)){
			if ($this->debug) {
				$this->_logGroupe('Serving cashe dafa from '.$filename, file_get_contents($this->cacheDir.'/'.$filename));
			}
			$data = unserialize(file_get_contents($this->cacheDir.'/'.$filename));
			if ($delete) {
				$this->_logGroupe('Delete cache file '.$this->cacheDir.'/'.$filename,'' );
				unlink($this->cacheDir.'/'.$filename);
			}
			return $data;
		}else{
			if ($this->debug) {
				$this->FB->warn('Cache file '.$filename.' not found');
			}
		}
		return false;
	}
	
	/**
	 * Записать Данные в кэш
	 * @param type $filename
	 * @param type $data
	 * @return bool
	 */
	private function _writeCacheFile($filename,$data){
		if ($this->debug) {
			$this->FB->info('Saving new data to file '.$filename);
			$this->_logGroupe('data', $data);
		}
		if (!$filename) {
			return;
		}
		return file_put_contents($this->cacheDir.'/'.$filename, serialize($data));
	}
	
	/**
	 * Вывести группу FB
	 * @param string $message
	 * @param mixed $data
	 */
	private function  _logGroupe($message, $data,$colapsed=true){
		$this->FB->group($message,array('Collapsed' => $colapsed));
		$this->FB->fb($data);
		$this->FB->groupEnd();
	}
	
	/**
	 * Получить имя пользователя
	 * @param string $sessionId 
	 */
	public function getUserID($sessionId){
		$filename=md5($sessionId);
		return $this->_loadCacheFile($filename, true);
	}
	
	/**
	 * Записать в файл полученный UserId
	 * @param int $userId
	 * @param string $sessionId
	 */
	public function setUserId($userId,$sessionId){
		$filename=md5($sessionId);
		$this->_writeCacheFile($filename, $userId);
	}
	
}
