<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>
<ul>
	<li><label>文章名称</label>: <?= Html::encode($model->article_name) ?></li>
	<li><label>文章内容</label>: <?= Html::encode($model->article_content) ?></li>
	<li><label>文章作者</label>: <?= Html::encode($model->article_author) ?></li>
</ul>