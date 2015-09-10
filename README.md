Ethnicmedia client
==================
Ethnicmedia client

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist martrix/yii2-ethnicmedia-client "*"
```

or add

```
"martrix/yii2-ethnicmedia-client": "*"
```

to the require section of your `composer.json` file.


Usage
-----
1. Add to params.php
	'EM_apiKey' => API KEY,

2. Add to SiteController.php
```php
	public function actions(){
		return array(
		'emclient' => array(
			'class'    => '\martrix\ethnicmediaClient\EmclientAction',
			'debug'    => true,
			'lang'     => 'en',
			'adsLimit' => 20,

		  ),
		);
	}

	public function actionClassifield(){
		$categoryId=Yii::$app->request->get('categoryId');
		$page=Yii::$app->request->get('page');
		return $this->render('ad',['categoryId'=>$categoryId,'page'=>$page]);
	}
```
example of use on  template (ad.php):

<div id="EM_ad_form"  class="adform">

	<?= \martrix\ethnicmediaClient\Emformwidget::widget(['adId'=>5]); ?>
</div>


<div class="ad">

	<?= \martrix\ethnicmediaClient\Emadsdisplaywidget::widget(['categoryId'=>$categoryId,'page'=>$page]); ?>
</div>
