<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
	<?= $form->field($model, 'article_name') ?>
	<?= $form->field($model, 'article_content') ?>
	<?= $form->field($model, 'article_author') ?>
<div class="form-group">
	<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>