<?php
declare (strict_types = 1);

namespace app\resource\controller;

use think\Request;
use think\App;
use think\facade\View;
use app\resource\model\Device as DeviceModel;
use app\common\annotation\NodeAnnotation;
use app\common\annotation\ControllerAnnotation;
use think\facade\Db;

/**
 * @ControllerAnnotation (title="Device")
 */
class Device extends \app\common\controller\Backend
{
    protected $pageSize = 15;
    protected $layout = '../../backend/view/layout/main';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->modelClass = new DeviceModel();


    }

    
     public function hello()
    {
    
    $list = Db::table('device')->select();
    
    //$user = Db::connect('mysql')->table('tp_user')->select();
    $result = ['code' => 0, 'msg' => lang('Get Data Success'), 'data' => $list->items(), 'count' =>$list->total()];
            return json($result);
   
    }
    
}

