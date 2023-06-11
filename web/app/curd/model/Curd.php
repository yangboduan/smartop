<?php
/**
 * FunAdmin
 * ============================================================================
 * 版权所有 2017-2028 FunAdmin，并保留所有权利。
 * 网站地址: http://www.FunAdmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2021/5/11
 * Time: 15:55
 */
namespace app\curd\model;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class Curd extends BaseModel{
    use SoftDelete;
    protected $name = 'addons_curd';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
    public function admin(){
        return $this->belongsTo('app\backend\model\Admin','admin_id','id');
    }
}