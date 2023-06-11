define(['table','form'], function (Table,Form) {
    let Controller = {
        index: function () {
            Table.init = {
                table_elem: 'list',
                tableId: 'list',
                requests:{
                    index_url:'resource/Database/index',
                    add_url:'resource/Database/add',
                    edit_url:'resource/Database/edit',
                    destroy_url:'resource/Database/destroy',
                    delete_url:'resource/Database/delete',
                    import_url:'resource/Database/import',
                    export_url:'resource/Database/export',
                    modify_url:'resource/Database/modify',

                }
            }
            Table.render({
                elem: '#' + Table.init.table_elem,
                id: Table.init.tableId,
                url: Fun.url(Table.init.requests.index_url),
                init: Table.init,
                primaryKey:'id',
                toolbar: ['refresh','add','destroy','import','export'],
                cols: [[
                    {checkbox: true,},
                   {field:'ip', title: __('Ip'),align: 'center'},
                    {field:'name', title: __('Name'),align: 'center'},
					
                    {field:'user', title: __('User'),align: 'center'},
                    {field:'department', title: __('Department'),align: 'center'},
                    {field:'online_status', title: __('OnlineStatus'),align: 'center'},
                    {field:'database_type', title: __('数据库类型'),align: 'center'},
                    {field:'os_type', title: __('OsType'),align: 'center'},
                    {field:'last_online_time',title: __('LastOnlineTime'),align: 'center',timeType:'datetime',dateformat:'yyyy-MM-dd HH:mm:ss',searchdateformat:'yyyy-MM-dd HH:mm:ss',search:'time',templet: Table.templet.time,sort:true},
                    {field:'use_desc', title: __('UseDesc'),align: 'center'},
                    {
                        minWidth: 150,
                        align: "center",
                        title: __("Operat"),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat:["edit"]
                    },
                ]],
                limits: [10, 15, 20, 25, 50, 100,500],
                limit: 15,
                page: true,
                done: function (res, curr, count) {
                }
            });
            Table.api.bindEvent(Table.init.tableId);
        },
        add: function () {
            Controller.api.bindevent()
        },
        edit: function () {
            Controller.api.bindevent()
        },
        
        api: {
            bindevent: function () {
                Form.api.bindEvent($('form'))
            }
        }
    };
    return Controller;
});