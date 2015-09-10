<?php

/**
 * Description of Emwidget
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
namespace martrix\ethnicmediaClient;

class Emformwidget extends  \yii\base\Widget{
	
		/** @var string $apiKey */
	public $apiKey;

	/** @var bool $debug */
	public $debug =  false;

	/** @var string $lang */
	public $lang = '';

	/** @var int $adsLimit */
	public $adsLimit = 20;
	
	/** @var int $adId */
	public $adId;
	public function run() {
		if(!$this->lang) {
			$this->lang = substr(\Yii::$app->language, 0, 2);
		}
		$this->apiKey = \Yii::$app->params['EM_apiKey'];
		$request= $this->getClient();
		\Yii::$app->session['EM_advForm'] = $this->adId;
		return $this->render('adForm',array('form'=> $request->getAdverticeForm($this->adId, 'html')));
	}
	
	private function getClient(){
		include_once __DIR__.'/lib/EMclient.php';
		$request= new EMclient($this->apiKey, $this->lang, $this->debug, $this->adsLimit);
		return $request;
	}
}
