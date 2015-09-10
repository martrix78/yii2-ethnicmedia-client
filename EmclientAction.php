<?php

/**
 * Description of Emclient
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
namespace martrix\ethnicmediaClient;

use Yii;
use yii\base;

class EmclientAction extends base\Action {

	/** @var string $apiKey */
	public $apiKey;

	/** @var bool $debug */
	public $debug =  false;

	/** @var string $lang */
	public $lang = '';

	/** @var int $adsLimit */
	public $adsLimit = 20;

	public function run(){
		Yii::$app->request->attachBehaviors($behaviors);
		if(!$this->lang) {
			$this->lang = substr(Yii::$app->language, 0, 2);
		}
		$this->apiKey = Yii::$app->params['EM_apiKey'];
		$request= $this->getClient();
		/// AJAX
		/// Обновить прайслист
		if(isset($_POST['updatePricelist']) and isset($_POST['advertise_id'])) {
			echo $request->getPricelist($_POST['advertise_id']);
			Yii::$app->end(); 
		}
		/// Обновить цену
		if(isset($_POST['updatePrice']) and isset($_POST['advertise_id'])) {
			echo $request->updatePrice($_POST);
			Yii::$app->end(); 
		}

		/// обновить даты
		if(isset($_POST['getDate']) and isset($_POST['Advertise_id'])) {
			echo $request->updateDate($_POST);
			Yii::$app->end(); 
		}
		/// послать обьявление
		if(isset($_POST['sendForm']) ) {
			$data = $request->createAdvertice($_POST);
			echo (isset($data['preview']))?$data['preview']:'';
			Yii::$app->end();
		}
		/// послать обьявление
		if(isset($_GET['page']) ) {
			echo  $request->getAds($_GET['categoryId'], 'html', 20, $_GET['page']);
			Yii::$app->end();
		}
		/// создать инвойс
		if(isset($_POST['createInvoice']) ) {
			$data = $request->createInvoice($_POST['advId']);
			if(isset($data['status']) and $data['status'] =='paid') {
			?>
				<div class="EM_success">Paid!</div>
				<?php
			}elseif(isset($data['status']) and $data['status'] =='success' and isset($data['invoiceId'])){
				echo $request->getPaidForm($data['invoiceId']);
				echo ''
					. '<script type="text/javascript">'
					. 'jQuery("#AutorizeForm_AMOUNT").val('.$data['price'].');'
					. 'jQuery("#invoiceId").val('.$data['invoiceId'].');'
					. '</script>';
			}else{
				var_dump($data);
				echo "ERROR creating invoice...";
			}
			Yii::$app->end();
		}
		/// Оплатить
		if(isset($_POST['payOrder'])) {
			$data=$request->payInvoice($_POST);
			$amt=$_POST['AutorizeForm']['AMOUNT'];
			$invoiceID=$_POST['invoiceId'];
			if(isset($data['status'])) {
				if($data['status'] =='paid') {
					echo '<div class="EM_success_message"> Invoice #'.$data['invoiceId'].' successfully paid</div>';
				}
				if($data['status'] =='recharge') {
					echo '<div class="EM_success_message"> Not enough funds to pay the invoice, please check your balance in your <a href="http://core.ethnicmedia.us/money/payments" target="_BLANK">personal account</a></div>';
				}
			}else{
				if(is_array($data)) {
					foreach ($data as $key => $value) {
						echo "<p><strong>$key</strong> $value</p>";
					}
				}else{
					echo $data;
				}
				echo $request->getPaidForm($_POST['invoiceId']);
				echo ''
					. '<script type="text/javascript">'
					. 'jQuery("#AutorizeForm_AMOUNT").val('.$amt.');'
					. 'jQuery("#invoiceId").val('.$invoiceID.');'
					. '</script>';
			}
			Yii::$app->end();
		}
		/// авторизация
		if(isset($_POST['auth']) and $_POST['auth']== 'login' ) {
		$data= $request->login($_POST['login'], $_POST['password']);
			if(is_array($data)) {
				foreach ($data as $value) {
					echo "<div class='red'>$value</div>";
				};
			}else{
				$request->setUserId($data);
			}
			echo $request->getAdverticeForm(Yii::app()->session['EM_advForm'], 'html');
			Yii::$app->end(); 
		}
		// регитстрация
		if(isset($_POST['auth']) and $_POST['auth']== 'register' ) {
		   $data= $request->register($_POST['login'], $_POST['password'],$_POST['Confirmpassword']);
		   if(is_array($data)) {
			   foreach ($data as $value) {
				   echo "<div class='red'>$value</div>";
			   };
		   }else{
			   $request->setUserId($data);
		   }
		   echo $request->getAdverticeForm(Yii::app()->session['EM_advForm'], 'html');
		   Yii::$app->end(); 
	   }
	}

	private function getClient(){
		include_once __DIR__.'/lib/EMclient.php';
		$request= new EMclient($this->apiKey, $this->lang, $this->debug, $this->adsLimit);
		return $request;
	}
}