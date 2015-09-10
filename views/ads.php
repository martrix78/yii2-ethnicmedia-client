<?php

/**
 * Description of ads
 *
 * @author Andrew Russkin <andrew.russkin@gmail.com>
 */
?>
<div class='ad_categories'>
	<?php if ($categories): ?> 
	<ul>
		<?php foreach ($categories as $key=>$name): ?> 
			<li><a class="<?=($categoryId == $key)?' active':''?> " href='/classifield?categoryId=<?=$key?>'><?=$name?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif ?>
</div>

<?php if ($pagination): ?> 
	<ul class="pagination">
		<?php foreach ($pagination as $item): ?> 
		<li>
			<a class="<?=($item == $page)?' active':''?>"   href="/classifield?<?=($categoryId)?'categoryId='.$categoryId.'&':''?><?='page='.$item?>"><?=$item ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
<?php endif ?>

<div class="ads">
	<?php if ($data): ?> 
		<?php
			foreach ($data as $ad){
				echo $ad;
			}
		?>
	<?php endif ?>
</div>

<?php if ($pagination): ?> 
	<ul class="pagination">
		<?php foreach ($pagination as $item): ?> 
		<li>
			<a class="<?=($item == $page)?' active':''?>"   href="/classifield?<?=($categoryId)?'categoryId='.$categoryId.'&':''?><?='page='.$item?>"><?=$item ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
<?php endif ?>


