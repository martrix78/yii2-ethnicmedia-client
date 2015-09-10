<?php

/**
 * Description of Emadsdisplaywidget
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
namespace martrix\ethnicmediaClient;
class Emadsdisplaywidget extends \yii\base\Widget{
	
		/** @var string $apiKey */
	public $apiKey;

	/** @var bool $debug */
	public $debug =  false;

	/** @var string $lang */
	public $lang = '';

	/** @var int $adsLimit */
	public $adsLimit = 20;
	
	/** @var int $categoryId */
	public $categoryId;
	
	/** @var int $page */
	public $page=1;
	/** @var string $type */
	public $type='html';
	
	/** @var int $limit */
	public $limit=20;

	public function run() {
		if(!$this->lang) {
			$this->lang = substr(\Yii::$app->language, 0, 2);
		}
		$this->apiKey = \Yii::$app->params['EM_apiKey'];
		$request= $this->getClient();
		$data=$request->getAds($this->categoryId, $this->type, $this->limit,  $this->page);
		if($this->type=='html') {
			
			return $this->render('ads',array(
					'data'       => $data,
					'pagination' => $request->getPaginationArray(),
					'categories' => $request->getCats(),
					'categoryId' => $this->categoryId,
					'page'       => $this->page,
					));
			
		}else{
			return $data;
		}
	}
	
	private function getClient(){
		include_once __DIR__.'/lib/EMclient.php';
		$request= new EMclient($this->apiKey, $this->lang, $this->debug, $this->adsLimit);
		return $request;
	}
}

