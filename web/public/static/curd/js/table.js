define(['table','form'], function (Table,Form) {
    Table.init = {
        table_elem: 'list',
        tableId: 'list',
        searchName:'TABLE_NAME',
        requests:{
            index_url:'curd/table/index?id='+ (typeof id!=='undefined'?id:''),
            edit_url:'curd/table/edit',
            fieldlist_url:'curd/table/fieldlist?id='+ (typeof id!=='undefined'?id:''),
            add_full:{
                type: 'open',
                event: 'open',
                class: 'layui-btn layui-btn-blue',
                icon: 'layui-icon layui-icon-add-1',
                text: __('建表'),
                title: __('建表'),
                url: 'curd/table/add',
                full:1,
            },
            list:{
                type: 'open',
                event: 'open',
                class: 'layui-btn layui-btn-blue',
                icon: 'layui-icon layui-icon-console',
                text: __('表信息'),
                title: __('表信息'),
                url: 'curd/table/list',
            },
            delfile:{
                type: 'request',
                event: 'request',
                class: 'layui-btn layui-btn-danger',
                icon: 'layui-icon layui-icon-delete',
                text: __('删除备份'),
                title: __('删除备份'),
                url: 'curd/table/delfile',
            },
            field:{
                type: 'open',
                event: 'open',
                class: 'layui-btn layui-btn-blue',
                icon: 'layui-icon layui-icon-list',
                text: __('字段'),
                title: __('字段'),
                url: 'curd/table/fieldlist',
                btn:'close',
                full:1
            },
            addfield:{
                type: 'open',
                event: 'open',
                class: 'layui-btn layui-btn-normal',
                icon: 'layui-icon layui-icon-add-1',
                text: __('新增字段'),
                title: __('新增字段'),
                full:1,
                url: 'curd/table/addfield?id='+(typeof id!=='undefined'?id:''),
            },
            delfield:{
                type: 'request',
                event: 'request',
                class: 'layui-btn layui-btn-danger',
                icon: 'layui-icon layui-icon-delete',
                text: __('删除字段'),
                title: __('删除字段'),
                url: 'curd/table/delfield',
            },
            savefield:{
                type: 'save',
                event: 'save',
                class: 'layui-btn layui-btn-danger',
                icon: 'layui-icon layui-icon-add-1',
                text: __('保存'),
                title: __('保存'),
                url: 'curd/table/savefield',
                callback: function(obj,row,config){

                    Fun.toastr.confirm(__('确定保存吗?'), function () {
                        var COLUMN_KEY  = $('tr[data-index="'+row.index+'"]').find("select[lay-filter='COLUMN_KEY']").parents('.layui-table-select').find('dd.layui-this').attr('lay-value'),
                        DATA_TYPE   = $('tr[data-index="'+row.index+'"]').find("select[lay-filter='DATA_TYPE']").parents('.layui-table-select').find('dd.layui-this').attr('lay-value'),
                        IS_NULLABLE = $('tr[data-index="'+row.index+'"]').find("select[lay-filter='IS_NULLABLE']").parents('.layui-table-select').find('dd.layui-this').attr('lay-value'),
                        AFTER       = $('tr[data-index="'+row.index+'"]').find("select[lay-filter='AFTER']").parents('.layui-table-select').find('dd.layui-this').attr('lay-value');
                        var data = row.data;
                        data.COLUMN_KEY = COLUMN_KEY?COLUMN_KEY:'';
                        data.DATA_TYPE = DATA_TYPE?DATA_TYPE:'';
                        data.IS_NULLABLE = IS_NULLABLE?IS_NULLABLE:'';
                        data.AFTER = AFTER?AFTER:'';
                        console.log(AFTER)
                        Fun.ajax({'url':Fun.url($(obj).data('url')), data: data,}, function (res) {
                            Fun.toastr.success(res.msg, function () {
                                Table.api.reload(tableId);
                            })
                        }, function (res) {
                            Fun.toastr.error(res.msg)
                        })
                        Fun.toastr.close();
                        return false;
                    });
                }
            },
            data_url:{
                type: 'open',
                event: 'open',
                class: 'layui-btn layui-btn-warm',
                text: __('查看'),
                title: __('查看'),
                url: 'curd/table/list',
                icon: 'layui-icon layui-icon-log',
                extend: "data-btn='false'",
                width: '1200',
                height: '600',
            },
            backup_url:{
                type: 'request',
                event: 'request',
                class: 'layui-btn layui-btn-normal',
                text: __('备份'),
                title: __('备份'),
                url: 'curd/table/backup',
                icon: 'layui-icon layui-icon-play',
            },
            clear_url:{
                type: 'request',
                event: 'request',
                class: 'layui-btn layui-btn-blue',
                text: __('更新行数'),
                title: __('更新行数'),
                url: 'curd/table/dataclear',
                icon: 'layui-icon layui-icon-play',
            },
            delete_url:{
                type: 'request',
                event: 'request',
                class: 'layui-btn layui-btn-danger',
                icon: 'layui-icon layui-icon-delete',
                text: __('删除表'),
                title: __('删除表'),
                url: 'curd/table/delete',
            },
           


        }
    }

    let Controller = {
        index: function () {
            Table.render({
                elem: '#' + Table.init.table_elem,
                id: Table.init.tableId,
                url: Fun.url(Table.init.requests.index_url),
                init: Table.init,
                toolbar: ['refresh','add_full','backup_url','clear_url','delfile'],
                cols: [[
                    {checkbox: true,},
                    {field:'TABLE_NAME', title: __('Tablename'),align: 'left',sort:'sort',},
                    {field:'ENGINE', title: __('Engine'),align: 'center',sort:'sort',search: false,width: 100,},
                    {field:'TABLE_ROWS', title: __('Rows'),align: 'center',sort:'sort',search: false,width: 100,},
                    {field:'TABLE_COMMENT', title: __('Explain'),align: 'left',sort:'sort',},
                    {field:'TABLE_COLLATION', title: __('Collation'),align: 'center',sort:'sort',search: false,width: 190,},
                    {field:'CREATE_TIME',title: __('CreateTime'),align: 'center',timeType:'datetime',dateformat:'yyyy-MM-dd HH:mm:ss',searchdateformat:'yyyy-MM-dd HH:mm:ss',search:false,sort:false,width: 170},
                    {field:'UPDATE_TIME',title: __('UpdateTime'),align: 'center',timeType:'datetime',dateformat:'yyyy-MM-dd HH:mm:ss',searchdateformat:'yyyy-MM-dd HH:mm:ss',search:false,sort:true,width: 170},
                    {
                        fixed:"right",
                        width: 350,
                        align: "center",
                        title: __("Operat"),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat: ["edit", 'field',"data_url","backup_url",'delete_url']
                    },
                ]],
                page: false,
                done: function (res, curr, count) {
                }
            });
            Table.api.bindEvent(Table.init.tableId);

        },
        fieldlist: function () {
            Table.render({
                elem: '#' + Table.init.table_elem,
                id: Table.init.tableId,
                url: Fun.url(Table.init.requests.fieldlist_url),
                init: Table.init,
                toolbar: ['refresh','addfield'],
                rowDouble:false,
                cols: [[
                    {checkbox: true,},
                    {title:'序号',type:'numbers',width: 70,},
                    {field:'id', title: __('表列名'),align: 'left',sort:'sort',hide:true},
                    {field:'COLUMN_NAME', title: __('列名'),align: 'left',sort:'sort',edit: true,},
                    {field:'IS_NULLABLE', title: __('是否必填'),align: 'center',sort:'sort',search: false,width:100,
                        selectList: {'YES': __('否'), 'NO': __('是')}, templet: Table.templet.selects},
                    {
                        field:"DATA_TYPE",
                        align: "center",
                        title: "数据类型",
                        selectList:datatypeList,
                        templet: Table.templet.selects,
                    },
                    {field:'CHARACTER_MAXIMUM_LENGTH', title: __('长度'),align: 'left',sort:'sort',edit:true,},
                    {field:'COLUMN_DEFAULT', title: __('默认值'),align: 'left',sort:'sort',edit:true,},
                    {field:'COLUMN_KEY', title: __('索引'),align: 'left',sort:'sort',
                        selectList: indexList,
                        templet: Table.templet.selects,},
                    {field:'COLUMN_COMMENT', title: __('备注'),align: 'left',sort:'sort',search: false,edit:true},
                    {field:'DATETIME_PRECISION', title: __('排序 '),align: 'left',sort:'sort',search: false,
                        filter:'AFTER',
                        selectList: fieldList,
                        templet: Table.templet.selects,

                    },
                    {
                        fixed:"right",
                        width: 200,
                        align: "center",
                        title: __("Operat"),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat: ['savefield',"delfield"]
                    },
                ]],
                page: false,
                done: function (res, curr, count) {
                    // res.data.forEach(function (item, index) {//根据已有的值回填下拉框
                    //     layui.each($("select[name='datatype']", ""), function (index, item) {
                    //         var elem =$(item);
                    //         elem.next().children().children()[0].defaultValue = elem.data('value');
                    //         //elem.val(elem.data('value'));
                    //     });
                    //     layui.table.render('select');
                    // });
                }
            });
            

            Table.api.bindEvent(Table.init.tableId);
            layui.table.on('tool(edit)',function(){
                alert(1);
                return false;
            })

        },
        add: function () {
            $('body').on('click','*[lay-event]',function (e) {
                var getTpl = trtpl.innerHTML, data = {};
                if($(this).attr('lay-event') == 'add'){
                    layui.laytpl(getTpl).render(data, function(html){
                        $('table tbody').find('tr:last-child').after(html)  ;
                        layui.form.render();
                    });
                    return false;
                }
                if($(this).attr('lay-event') == 'del'){
                    $(this).parents('tr').remove();
                }
                return false;
            })
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent()
        },
        list:function(){

        },
        addfield:function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindEvent($('form'))
            }
        }
    };
    return Controller;
});