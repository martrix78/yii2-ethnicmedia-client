<?php

/**
 * Клиент для REST servis 
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
class EMclient {
	
	/*  API KEY для сайта */
	public $APIKEY='4a75db6207983269d7957dafbc560e7896278681';
	
	/** @var string  */
	protected $serverUrl='http://api.ethnicmedia.us';
	
	/** @var string  */
	public $checkLoginUrl = "http://core.ethnicmedia.us/logo.png";
	
	/** @var bool  */
	public $debug=true;
	
	/** @var integer  */
	public $adsLimit=20;


	/** @var FirePHP  */
	protected $FB;

	/** @var Request  */
	protected $request;
	
	/**
	 * разлогиневание в системе
	 * @var string 
	 */
	protected $logoutUri='?mode=logout';

	/** @var string  */
	protected $lang;
	/** @var string  */
	protected $userID;

	function __construct($apiKey,$lang='ru',$debug=true,$adsLimit=20) {
		$this->APIKEY=$apiKey;
		$this->debug=$debug;
		$this->adsLimit=$adsLimit;
		if ($this->debug) {
			include_once 'FirePHP/FirePHP.class.php';
			$this->FB= FirePHP::getInstance(true);
		}
		$haystack=array('en','ru');
		if (isset($_COOKIE['EM_lang']) and in_array($_COOKIE['EM_lang'], $haystack)) {
			$lang=$_COOKIE['EM_lang'];
		}
		$this->lang=$lang;
		$this->userID=(isset(Yii::app()->session['userEmId']) and Yii::app()->session['userEmId'])?Yii::app()->session['userEmId']:null;
		include_once 'Request.php';
		$this->request=new Request($this->debug, $this->serverUrl);
		if (!$this->userID) {
//			$this->userID='ee2df61f21eb96e4878d3320dada70cf9a1e3f98';
//			$this->tryAutentificate();
		}
	}
	
	/**
	 *  Аутентификация пользователя
	 * @return string
	 */
	public function autentificate($userData){
		if ($userData['userEmId']) {
			$this->userID=$userData['userEmId'];
			return true;
		}
		$data= $this->request->getRequest('GET', '/login', 'API_KEY='.$this->APIKEY.'&USERNAME='.$userData['login'].'&PASSWORD='.$userData['pass'], array(), 'login');
	}
	
	/**
	 * Получить полностью всю болванку
	 */
	public function getAllLayout(){
		return $this->request->getRequest(
				'GET',
				'/getalllayout',
				$this->getUri().'&LOGOUTURI='.$this->logoutUri,
				$data=array(),
				'main_layout_'.$this->userID.'_'.$this->lang);
	}
	
	/**
	 * Получить основную разметку плашки
	 * @return string
	 */
	public function getLayout(){

		return $this->request->getRequest('GET', '/getlayout', $this->getUri(), $data=array(), 'main_layout');
	}

	/**
	 * Получить форму для подачи объявления
	 * @param int $formId
	 * @param string $type 'html' || 'json'
	 * @param array $options (showDate,showServises,showPricelist,includeJs)
	 */
	public function getAdverticeForm($formId, $type,$options=array('showDate'=>true,'showServises'=>true,'showPricelist'=>true,'includeJs'=>true)){
		if(!$this->userID) {
			return $this->request->getRequest('GET', '/getauthform', $this->getUri().'&SHOWADVFORM=1', $data=array(), 'auth_form_'.$this->lang);
		}
		if(!in_array($type, array('html','json'))) {
			throw new Exception('Wrong return type format', 500);
		}
		return $this->request->getRequest('GET', '/adform', $this->getUri().'&ADFORM='.(int)$formId.'&TYPE='.$type.'&DATA='.json_encode($options), $data=array(), 'adv_form'.(int)$formId.$type);
	}
	
	/**
	 * Авторизация
	 * @param string $login
	 * @param type $password
	 * @return string
	 */
	public function login($login,$password){
		return $this->request->getRequest('POST', '/login', $this->getUri(), $data=array('LOGIN'=>$login,'PASSWORD'=>$password ) );
	}

	/**
	 * Регистрация
	 * @param string $login
	 * @param type $password
	 * @param type $confirmpassword
	 * @return string
	 */
	public function register($login,$password,$confirmpassword){
		return $this->request->getRequest('POST', '/register', $this->getUri(), $data=array('LOGIN'=>$login,'PASSWORD'=>$password,'CONFIRMPASSWORD' => $confirmpassword ) );
	}
	
	
	/**
	 * Строка аутентификации
	 */
	private function getUri(){
		return 'API_KEY='.$this->APIKEY.'&LANG='.$this->lang.'&USERID='.$this->userID;
	}

	/**
	 * Обновить цену обьявления
	 * @param array $POST
	 * @return type
	 */
	public function updatePrice($POST){
		return $this->request->getRequest('POST', '/updateprice', $this->getUri(), $data=array('DATA'=>  json_encode($POST)) );
	}
		
	/**
	 * Обновить даты обьявления
	 * @param array $POST
	 * @return type
	 */
	public function updateDate($POST){
		return $this->request->getRequest('POST', '/updatedates', $this->getUri(), $data=array('DATA'=>  json_encode($POST)) );
	}
	
	/**
	 * Получить прайслист для обьявления 
	 * @param int $advertiseId
	 */
	public function getPricelist($advertiseId){
		return $this->request->getRequest('GET', '/pricelist', $this->getUri().'&ADVERTICEID='.(int)$advertiseId, $data=array(), 'adv_price'.(int)$advertiseId);
	}
		
	/**
	 * Создать обьявление 
	 * @param array $POST
	 */
	public function createAdvertice($POST){

		$data=array('DATA'=>  json_encode($POST));
		if(isset($_FILES)) {
			foreach ($_FILES as $key=>$file) {
				if(file_exists($file['tmp_name'])) {
					$data[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
				}
			}
		}
		return $this->request->getRequest('POST', '/createad', $this->getUri(), $data );
	}
	
	/**
	 * Создать инвойс 
	 * @param int $advId
	 */
	public function createInvoice($advId){

		return $this->request->getRequest('POST', '/createinvoice', $this->getUri(), $data=array('ADVERTICEID'=> $advId) );
	}
	
	/**
	 * Оплатить инвойс 
	 * @param array $data
	 */
	public function payInvoice($data){
		$data['INVOICEID']=$data['invoiceId'];
		$dat=  array('DATA'=>  json_encode($data));
		return $this->request->getRequest('POST', '/payinvoice', $this->getUri()."&INVOICEID=".$data['invoiceId'], $dat );
	}
	/**
	 * Форма для оплаты 
	 */
	public function getPaidForm(){

		return $this->request->getRequest('GET', '/getpayform', $this->getUri().'&TYPE=html', $data=array(),'payForm'.$this->lang );
	}
	/**
	 * Попробуем дождаться ответа с нашим userID
	 */
	private function tryAutentificate(){
		$now = time();
		$sessionId=  session_id();
		while (!$this->userID) {
			$this->userID = $this->request->getUserID($sessionId);
			if ( (time()-$now) > 3 or $this->userID) { // лимит ответа 2 сек
				$_SESSION['userEmId'] = $this->userID;
				return;
			}
		}
	}

	/**
	 * Возвращает API Key
	 * @return string
	 */
	public function getApiKey(){
		return $this->APIKEY;
	}

	/**
	 * авторизует пользователя
	 * @param string $id
	 * @return string
	 */
	public function setUserId($id){
		$this->userID = $id;
		Yii::app()->session['userEmId'] = $this->userID;
		
	}
	
	/**
	 * Возвращает UserID
	 * @return string
	 */
	public function getUserId(){
		return $this->userID;
	}
	/**
	 * Возвращает Request
	 * @return Request
	 */
	public function getRequest(){
		return $this->request;
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
	 * получить данные пользователя
	 * @return array
	 */
	public function getUserData(){
		$data=array();
		if ($this->userID) {
			$data=$this->request->getRequest('GET', '/getuserdata', $this->getUri(), $data=array(), 'userdata_'.$this->userID);
		}
		return $data;
	}
	
	/**
	 * Баланс
	 * @return array 
	 */
	public function getBalanse(){
		$data=array();
		if ($this->userID) {
			$data=$this->request->getRequest('GET', '/getbalance', $this->getUri(), $data=array(), 'balance_'.$this->userID);
		}
		return $data;
	}
	
	/**
	 * Счета и платежи
	 * @param int $limit 
	 * @param string $scope invoices|payments
	 * @return array 
	 */
	public function getInvoices($limit=5,$scope='invoices'){
		$data=array();
		if ($this->userID) {
			$data=$this->request->getRequest(
				'GET', '/gettrans', $this->getUri().'&LIMIT='.$limit.'&SCOPE='.$scope,
				$data=array(),
				$scope.'_'.$this->userID);
		}
		return $data;
	}

	/**
	 * Данные блокнота
	 * @return array
	 */
	public function getNotepadData(){
		$data=array();
		if ($this->userID) {
			$data=$this->request->getRequest(
				'GET', '/getnotepaddata', $this->getUri(),
				$data=array(),
				'notepad_'.$this->userID);
		}
		return $data;

	}

	/**
	 * Получить личные сообщения
	 * @return array
	 */
	public function getPrivateMessage($limit=5){
		$data=array();
		if ($this->userID) {
			$data=$this->request->getRequest(
				'GET', '/getprivatemessage', $this->getUri().'&LIMIT='.$limit,
				$data=array(),
				'pm_'.$this->userID);
		}
		return $data;
	}
	

	/**
	 * Получить обьявления
	 * @param int $categoryId
	 * @param string $format html/array/idml
	 * @param int $limit
	 * @param int $page
	 * @return html/array/idml
	 */
	public function getAds($categoryId, $format,$limit=20,$page=1){
		$data=array();
			$data=$this->request->getRequest(
				'GET', '/getads', 'API_KEY='.$this->APIKEY.'&LANG='.$this->lang.'&CATEGORYID='.$categoryId.'&FORMAT='.$format.'&LIMIT='.$limit.'&PAGE='.$page,
				$data=array(),
				'ads_'.$categoryId.'_'.$this->lang.'_'.$format.'_'.$limit
			);
		return $data;
	}

	
	/**
	 * Получить массив для построения пагинации
	 * @param int $categoryId
	 * @param int $limit
	 * @return array
	 */
	public function getPaginationArray($categoryId=0,$limit=20){
		$return=array();
			$data=$this->request->getRequest(
				'GET', '/getadsmaxpage', 'API_KEY='.$this->APIKEY.'&CATEGORYID='.$categoryId.'&LIMIT='.$limit,
				$data=array());
		if($data and $data > 1) {
			for ($i = 1; $i <= $data; $i++) {
				$return[]=$i;
			}
		}
		return $return;
	}
	
	public function getCats() {
		$data=array();
		$data=$this->request->getRequest(
			'GET', '/getadcategorylist', 'API_KEY='.$this->APIKEY.'&LANG='.$this->lang,
			$data=array(),
			'ads_cats_'.$this->lang
		);
		return $data;
	}
	/**
	 * Добавить в избранное
	 * @param string $url
	 */
	public function addToFavorites($url=''){
		$data=array();
		if ($url){
			$data=$this->request->getRequest(
				'POST', '/addtofavorites', '',
				$data=array(
					'API_KEY' => $this->APIKEY,
					'LANG'    => $this->lang,
					'URL'     => $url,
					'USERID'  => $this->userID,
				),
				null);

		}
		return $data;
	}
	
	/**
	 * Добавить запись в блокнот
	 * @param type $category_id
	 * @param type $text
	 * @return type
	 */
	public function addNote($category_id, $text){
		$data=array();
		if ($text){
			$data=$this->request->getRequest(
				'POST', '/addnote', '',
				$data=array(
					'API_KEY'    => $this->APIKEY,
					'LANG'       => $this->lang,
					'CATEGORYID' => $category_id,
					'TEXT'       => $text,
					'USERID'     => $this->userID,
				),
				null);

		}
		return $data;
	}

	/**
	 * Добавить запись в блокнот
	 * @param type $category_id
	 * @param type $text
	 * @return type
	 */
	public function sendMail($post){
		$data=array();
		if ($post){
			$requred=array(
					'API_KEY'    => $this->APIKEY,
					'LANG'       => $this->lang,
					'USERID'     => $this->userID,
			);
			$senddata=  array_merge($requred,$post);
			$data=$this->request->getRequest(
				'POST', '/sendmail', '',
				$data=$senddata,
				null);
		}
		return $data;
	}
	
	/**
	 * поменять язык 
	 * @param type $lang
	 */
	public function changeLang($lang){
		$haystack=array('en','ru');
		if ( in_array($lang, $haystack)) {
			SetCookie("EM_lang",$lang,time()+36000000);
			if ($this->userID){
				$this->request->flush_cache($this->userID,$lang);
			}
		}
		
	}
}