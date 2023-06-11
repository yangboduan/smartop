define(['table','form'], function (Table,Form) {
    let Controller = {
        index: function () {
            Table.init = {
                table_elem: 'list',
                tableId: 'list',
                requests:{
                    index_url:'resource/Resource/index',
                    add_url:'resource/Resource/add',
                    edit_url:'resource/Resource/edit',
                    destroy_url:'resource/Resource/destroy',
                    delete_url:'resource/Resource/delete',
                    import_url:'resource/Resource/import',
                    export_url:'resource/Resource/export',
                    modify_url:'resource/Resource/modify',

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
                    {field:'name', title: __('Name'),align: 'center'},
                    {field:'ip', title: __('Ip'),align: 'center',sort:true},
                    {field:'mac', title: __('Mac'),align: 'center'},
                    {field:'user', title: __('User'),align: 'center'},
                    {field:'department', title: __('Department'),align: 'center'},
                    {field:'create_time',title: __('CreateTime'),align: 'center',timeType:'datetime',dateformat:'yyyy-MM-dd HH:mm:ss',searchdateformat:'yyyy-MM-dd HH:mm:ss',search:'time',templet: Table.templet.time,sort:true},
                    {field:'type', title: __('Type'),align: 'center'},
					{field:'online_status', title: __('在线状态'),align: 'center'},
                    {
                        minWidth: 250,
                        align: "center",
                        title: __("Operat"),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat:["edit","destroy"]
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
