<?php
namespace app\models;
use yii\base\Model;

class Search extends Model
{
	public function get_where_job($condition = NULL,$page = 1,$s_type = 1,$otherCondition = NULL){
		
		if( $condition === NULL ) $condition = " where 1=1";
		
		$where = '';//其它条件

		if(isset($otherCondition['work_city'])){
	
			$where .= " AND a.work_city like '%".$otherCondition['work_city']."%'";

		}

		if(isset($otherCondition['work_year'])){
	
			$where .= " AND a.work_year like '%".$otherCondition['work_year']."%'";

		}

		if(isset($otherCondition['education'])){
	
			$where .= " AND a.education like '%".$otherCondition['education']."%'";

		}

		if(isset($otherCondition['work_type'])){
	
			$where .= " AND a.work_type like '%".$otherCondition['work_type']."%'";

		}

		if(isset($otherCondition['addtime'])){
	
			$where .= " AND a.addtime>".$otherCondition['addtime'];

		}

		if(isset($otherCondition['salary'])){
			if(isset($otherCondition['salary']['min_salary'])){
				$where .= " AND a.min_salary>=".$otherCondition['salary']['min_salary'];
			}
			if(isset($otherCondition['salary']['max_salary'])){
				$where .= " AND a.max_salary<=".$otherCondition['salary']['max_salary'];
			}

		}
		//echo $where;die;

		if($s_type == 2){
			$condition = " where b.c_name like '%".$condition."%'".$where;
		}
		
		$condition = $condition.$where;
		
		$sql = "select count(*) as num from rel_job as a left join company as b on a.company_id=b.c_id".$condition;
		$row["sql"] = $sql;
		$row["condition"] = $condition;
		return $row;
	}

	public function  get_job($count,$page,$condition){
		$page_size = 10;//每页显示条数
		
		$total_page = ceil($count/$page_size);//总页数

		$page_limit = ($page-1)*$page_size;

		$sql = "select a.rj_id,a.rj_name,a.work_city,a.work_year,a.education,a.work_tempt,a.min_salary,a.max_salary,a.addtime,b.c_name,b.c_job,b.founder,b.c_peoples,b.c_status,b.c_label from rel_job as a left join company as b on a.company_id=b.c_id".$condition." order by addtime desc limit ".$page_limit.",10";
		$row["sql"] = $sql;
		$row["total_page"] = $total_page;
		return $row;
	}

	public function get_jb($job_list,$total_page){
		foreach($job_list as $k=>$val){

			$job_list[$k]['label_list'] = explode(',',$val['c_label']);//把公司福利分割为数组
			$job_list[$k]['salary'] = $val['min_salary']."k-".$val['max_salary']."k";

		}

		$data['total_page'] = $total_page;
		$data['job_list']	= $job_list;

		return $data;
	}
}