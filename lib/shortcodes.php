<?php 

/**
 * Shortcodes for Ethnic Media Ads Exchange Plugin
 */

function EM_show_cats() {
	$categories = EM_getAdsCats();
	$page_url = site_url( $_SERVER['REQUEST_URI'] );
	ob_start();
	?>
	<nav class="nav_bar">
		<div class="container-fluid cats-bussines">
			<div class="navmenu offcanvas-sm">
				<ul class="nav navmenu-nav nav-pills nav-stacked">
					<li><a href="<?php echo home_url(); ?>" class="back"><span class="icon-arrow-left2"></span> <span>Вернуться на новости</span></a></li>
					<li><a href="#" class="adclassy"><span class="glyphicon glyphicon-bullhorn"></span><span> Подать объявление</span></a></li>
					<?php 
						foreach ( $categories as $id => $title ) {
							echo '<li><a href="' . $page_url . '?adcat=' . $id . '"><img src="' . get_template_directory_uri() . '/media/img/ads_cat_icon_' . $id . '.png" class="icon"> <span>' . $title . '</span></a></li>';
						}
					?>
				</ul>
			</div>
		</div>
	</nav>
<?php 
	$cats = ob_get_contents();
	return $cats;
	ob_end_clean();
}
add_shortcode( 'em-cats', 'EM_show_cats' );