<?php
namespace app\models;
use yii\base\Model;

class LoginForm extends Model
{
	public $verifyCode;
     
    public function rules()
    {
        return [
            ['verifyCode', 'required'],
            ['verifyCode', 'captcha'],
        ];
    }
}
