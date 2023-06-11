define(['jquery','table','form'], function ($,Table,Form) {
    Table.init = {
        table_elem: 'list',
        tableId: 'list',
        requests: {
            index_url: 'curd/index/index',
            delete_url: 'curd/index/delete',
            add_url: 'curd/index/add',
            create: {
                type: 'open',
                class: 'layui-btn-sm layui-btn-normal',
                url: 'curd/table/add',
                icon: 'layui-icon layui-icon-add-1',
                text: __('建表'),
                title: __('建表'),
                full: 1,
                height:600,
            },
            list: {
                type: 'iframe',
                event: 'iframe',
                class: 'layui-btn-sm layui-btn-normal',
                url: 'curd/table/index',
                icon: 'layui-icon layui-icon-list',
                text: __('数据表列表'),
                title: __('数据表列表'),
                full: 1,
                height:600,
            },
            add_full: {
                type: 'open',
                class: 'layui-btn-sm layui-btn-normal',
                url: 'curd/index/add',
                icon: 'layui-icon layui-icon-add-1',
                text: __('Add'),
                title: __('Add'),
                full: 1,
                height:600,
                extend:"data-btn=''",
            },
        },
    };
    let Controller = {
        index: function () {
            Table.render({
                elem: '#' + Table.init.table_elem,
                id: Table.init.tableId,
                url: Fun.url(Table.init.requests.index_url),
                init: Table.init,
                toolbar: ['refresh','add_full','create','list','delete'],
                cols: [[
                    {checkbox: true,},
                    {field: 'id', title: 'ID', width: 80, sort: true},
                    {field: 'admin.username', title: __('Admin'), width: 120,templet: Table.templet.tags},
                    {field: 'post_json', title: __('json'), width: 300},
                    {field: 'curd', title: __('curd'), width: 400,},
                    {field: 'create_time', title: __('createTime'), width: 180,search:'range'},
                    {
                        minwidth: 150,
                        align: 'center',
                        title: __('Operat'),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat: ['edit_full', 'delete']
                    }
                ]],
                limits: [10, 15, 20, 25, 50, 100],
                limit: 15,
                page: true
            });
            Table.api.bindEvent(Table.init.tableId);

        },
        add:function () {
            Controller.api.bindevent()
        },
        api: {
            bindevent: function () {
                Form.api.bindEvent($('form'))
                var relTable = [];
                function buildOptions(data,select){
                    var html = [];
                    for (var i = 0; i < data.length; i++) {
                        html.push("<option value='" + data[i] + "'>" + data[i] + "</option>");
                    }
                    select.html(html);
                    layui.form.render();
                    layui.multiSelect.render();
                };
                //选驱动
                layui.form.on('select(driver)', function(data){
                    var driver = data.value;
                    Fun.ajax({url:Table.init.requests.add_url,method:'GET',data:{type:1,driver:driver}},function (res){
                        buildOptions(res.data.table,$('.table'));
                        var length = $('.jointable').length;
                        if(length > 0){
                            for (var index=0;index<length;index++){
                                var data = { //数据
                                    "index":index,
                                    'table':res.data.table,
                                }
                                buildOptions(res.data.table,$('.jointable'));

                            }
                        }
                    })
                })
                //选表
                layui.form.on('select(table)', function(data){
                    var tableName = data.value;
                    var driver = $('select[name="driver"]').val();
                    if(!tableName){
                        Fun.toastr.error(__('please choose main table'));
                        return false;
                    }
                    Fun.ajax({url:Table.init.requests.add_url,method:'GET',data:{type:2,'tablename':tableName,driver:driver}},function (res){
                        buildOptions(res.data.fields_table,$('.fields'));
                        buildOptions(res.data.fields_table,$('.joinforeignkey'));
                    })
                })
                layui.form.on('select(jointable)', function(data){
                    _that  = $(data.elem);
                    var jointablename = data.value;
                    var driver = $('select[name="driver"]').val();
                    var tableName = $('select[name="table"]').val();
                    if(!tableName){
                        Fun.toastr.error(__('please choose rel table'));
                        return false;
                    }
                    Fun.ajax({url:Table.init.requests.add_url,method:'GET',data:{type:3,tablename:tableName,jointablename:jointablename,driver:driver}},function (res){
                        buildOptions(res.data.fields_join,_that.parents('tr').find('.joinprimarykey'));
                        buildOptions(res.data.fields_join,_that.parents('tr').find('.selectfields'));
                        buildOptions(res.data.fields_table,_that.parents('tr').find('.joinforeignkey'));
                    })
                })
                $('.addRelation').click(function (){
                    let index = relTable.length
                    relTable.push({
                        title: '',
                        content: []
                    })
                    //第三步：渲染模版
                    var data = { //数据
                        "index":index
                    }
                    var tableName = $('select[name="table"]').val();
                    var driver = $('select[name="driver"]').val();
                    if(!tableName){
                        Fun.toastr.error(__('please choose main table'));
                        return false;
                    }
                    Fun.ajax({async:false,url:Table.init.requests.add_url,method:'GET',data:{type:4,'tablename':tableName,driver:driver}},function (res){
                        var index = $('#relTab').find('tr').length;
                        buildOptions(res.data.fields,$('.joinforeignkey'));
                        buildOptions(res.data.table,$('.jointable'));
                        data.table = res.data.table;
                        tableInit(data)
                    })
                    // 规格删除按钮事件
                    $(`#relTab-delete-${index}`).click(function (){
                        // 移除元素
                        $(`#relTab-${index}`).remove();
                        // 数组移除
                        relTable.splice(index,1);
                    })
                })
                function tableInit(data){
                    var getTpl = tpl.innerHTML
                        ,view = $('#relTab');
                    layui.laytpl(getTpl).render(data, function(html){
                        view.append(html) ;
                    });
                    layui.form.render()
                    layui.multiSelect.render()
                }
            }
        }

    };
    return Controller;
});