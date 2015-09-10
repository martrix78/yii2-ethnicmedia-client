<?php

/*
Plugin Name:  Ethnic Media ads exchange system client
Plugin URI: http://ethnicmedia.us
Description: Клиент для подключения к системе EthnicMedia ad exchange system
Version: 1.0
Author: Ethnic Media
Author URI: http://ethnicmedia.us
*/
/*  Copyright 2015  Ethnic Media  (email: andrew.russkin@ethnicmedia.us)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Добавляем пункт в меню
if ( is_admin() ){
	add_action('admin_menu', 'em_add_pages');
	add_action( 'admin_init', 'register_mysettings' );
}
session_start();

/**
 * AJAX Callback 
 */
function EM_action_callback() {
	$request1= getClient();
   /// AJAX
   /// Обновить прайслист
   if(isset($_POST['updatePricelist']) and isset($_POST['advertise_id'])) {
	   echo $request1->getPricelist($_POST['advertise_id']);
	   wp_die(); 
   }
   /// Обновить цену
   if(isset($_POST['updatePrice']) and isset($_POST['advertise_id'])) {
	   echo $request1->updatePrice($_POST);
	   wp_die(); 
   }
   /// обновить даты
   if(isset($_POST['getDate']) and isset($_POST['Advertise_id'])) {
	   echo $request1->updateDate($_POST);
	   wp_die(); 
   }
   /// послать обьявление
   if(isset($_POST['sendForm']) ) {
	   $data = $request1->createAdvertice($_POST);
	   echo (isset($data['preview']))?$data['preview']:'';
	   wp_die();
   }
   /// смоздать инвойс
   if(isset($_POST['createInvoice']) ) {
	   $data = $request1->createInvoice($_POST['advId']);
	   if(isset($data['status']) and $data['status'] =='paid') {
		   ?>
				<div class="EM_success">Paid!</div>
			<?PHP
	   }elseif(isset($data['status']) and $data['status'] =='success' and isset($data['invoiceId'])){
		   echo $request1->getPaidForm($data['invoiceId']);
		   echo ''
				   . '<script type="text/javascript">'
				   . 'jQuery("#AutorizeForm_AMOUNT").val('.$data['price'].');'
				   . 'jQuery("#invoiceId").val('.$data['invoiceId'].');'
				   . '</script>';
	   }else{
		   var_dump($data);
		   echo "ERROR creating invoice...";
	   }
	   wp_die();
   }
   /// Оплатить
   if(isset($_POST['payOrder'])) {
	   $data=$request1->payInvoice($_POST);
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
		   echo $request1->getPaidForm($_POST['invoiceId']);
		   echo ''
				   . '<script type="text/javascript">'
				   . 'jQuery("#AutorizeForm_AMOUNT").val('.$amt.');'
				   . 'jQuery("#invoiceId").val('.$invoiceID.');'
				   . '</script>';
	   }
	    wp_die();
   }
   /// авторизация
   if(isset($_POST['auth']) and $_POST['auth']== 'login' ) {
	   $data= $request1->login($_POST['login'], $_POST['password']);
	   if(is_array($data)) {
		   foreach ($data as $value) {
			   echo "<div class='red'>$value</div>";
		   };
	   }else{
		   $request1->setUserId($data);
	   }
	   echo $request1->getAdverticeForm($_SESSION['EM_advForm'], 'html');
	   wp_die(); 
   }
   // регитстрация
   if(isset($_POST['auth']) and $_POST['auth']== 'register' ) {
	   $data= $request1->register($_POST['login'], $_POST['password'],$_POST['Confirmpassword']);
	   if(is_array($data)) {
		   foreach ($data as $value) {
			   echo "<div class='red'>$value</div>";
		   };
	   }else{
		   $request1->setUserId($data);
	   }
	   echo $request1->getAdverticeForm($_SESSION['EM_advForm'], 'html');
	   wp_die(); 
   }
}
	
if( defined('DOING_AJAX') && DOING_AJAX ){
	add_action('wp_ajax_EM_action', 'EM_action_callback');
	add_action('wp_ajax_nopriv_EM_action', 'EM_action_callback');
}
/// Добавление асетов
//add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
//function ajax_test_enqueue_scripts() {
//	wp_enqueue_script( 'test', plugins_url( '/test.js', __FILE__ ), array('jquery'), '1.0', true );
//}


function register_mysettings() { // whitelist options
  register_setting( 'myoption-group', 'EM_APIKEY' );
  register_setting( 'myoption-group', 'EM_adsLimit' );
  register_setting( 'myoption-group', 'EM_showMenu' );
  register_setting( 'myoption-group', 'EM_debug' );
}
// action function for above hook
function em_add_pages() {
    // Add a new submenu under Options:
    add_options_page('Ethnic Media ads exchange system client', 'Ads exchange', 8, 'em_setting', 'mt_options_page');
}

// mt_options_page() displays the page content for the Test Options submenu
function mt_options_page() {
	?>
	<div class="wrap">
		<h2>Ethnic Media ads exchange system client setting</h2>
			<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">APIKEY</th>
							<td>
								<input placeholder="put your API key here" style="width: 50%;" type="text" name="EM_APIKEY" value="<?= get_option('EM_APIKEY'); ?>" />
							</td>
					</tr>
					<tr valign="top">	
						<th scope="row">Default Ad list limit</th>
							<td>
								<input style="width: 50px;" min="0" step="1" type="number" name="EM_adsLimit" value="<?= get_option('EM_adsLimit'); ?>" />
							</td>
					</tr>
					<tr valign="top">	
						<th scope="row">Show EthnicMedia menu</th>
							<td>
								<input  type="hidden" name="EM_showMenu" value="0" />
								<input type="checkbox" name="EM_showMenu" <?php if (get_option('EM_showMenu')): ?> checked="" <?php endif ?> value="1" />
							</td>
					</tr>
					<tr valign="top">	
						<th scope="row">Debug mode</th>
							<td>
								<input  type="hidden" name="EM_debug" value="0" />
								<input type="checkbox" name="EM_debug" <?php if (get_option('EM_debug')): ?> checked="" <?php endif ?> value="1" />
							</td>
					</tr>

				</table>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="EM_APIKEY,EM_adsLimit,EM_debug" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
	</div>
<?php
}


function getClient(){
	include_once __DIR__.'/lib/EMclient.php';
	$request= new EMclient(get_option('EM_APIKEY'), 'en', get_option('EM_debug'), get_option('EM_adsLimit'));
	return $request;
}

/**
 * вывести обьявления
 * @param int $categoryId
 * @param string $type
 * @param int $limit
 */
function EM_getAds($categoryId=null,$type='html',$limit=20){
	$request= getClient();
	$data=$request->getAds($categoryId, $type, $limit);
	if($type=='html') {
		if($data and is_array($data)) {
			foreach ($data as $ad){
				echo $ad;
			}
		}
	}else{
		return $data;
	}
}

function EM_getAdsCats() {
	$request= getClient();
	$data=$request->getCats();
	return $data;
}

/**
 * Вывести форму подачи обьявления
 * @param int $id
 */
function EM_showAdForm($id){
$_SESSION['EM_advForm']=$id;
	$request= getClient();
	?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<script type="text/javascript">
	function getEmUrl(){
		return "/wp-admin/admin-ajax.php";
	}
</script>
	
		<div style="width: 400px;">
			<div id="EM_adv">
				<?= $request->getAdverticeForm($id, 'html')?>
			</div>
		</div>
<?php
}
include('lib/shortcodes.php');