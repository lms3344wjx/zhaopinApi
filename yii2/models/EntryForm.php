<?php
namespace app\models;
use yii\base\Model;

class EntryForm extends Model
{
	public $article_name;
	public $article_content;
	public $article_author;

	public function rules(){
		return[
			
				[['article_name','article_content','article_author'],'required'],
			
		];
	}

}
?>
