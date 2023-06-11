<?php
declare (strict_types = 1);

namespace app\resource\model;
use app\common\model\BaseModel;
use think\Model;
use think\model\concern\SoftDelete;
/**
 * @mixin \think\Model
 */
class Database extends BaseModel
{
    

    

    protected $pk = 'id';

    protected $name = 'database';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
    

    
}
