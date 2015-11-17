<?php
namespace app\models;
use yii\base\Model;

class Member extends \yii\db\ActiveRecord
{
	public function checkemail($email){
		//echo $email;die;
		$result=Member::find()->where(['email'=>$email])->asArray()->one();
		//print_r($result);die;
		return $result;
	}

	public function checkphone($member_phone){
		$result=Member::find()->where(['member_phone'=>$member_phone])->asArray()->one();
		return $result;
	}

	public function forgetpwd($member_phone,$password){
		
		$customer = Member::find()->where(['member_phone' => $member_phone])->one();
		$customer->password=$password;
		//print_r($customer);die;
		$customer->save();
		
		return $customer;
	}

	public function checkinsert($data){
		$result=Member::save($data);
		return $result;
	}
	
}
