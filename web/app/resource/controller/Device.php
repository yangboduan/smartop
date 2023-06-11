<?php
declare (strict_types = 1);

namespace app\resource\controller;

use think\Request;
use think\App;
use think\facade\View;
use app\resource\model\Device as DeviceModel;
use app\common\annotation\NodeAnnotation;
use app\common\annotation\ControllerAnnotation;

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
        return 'hello，world！';
    }
    

}

