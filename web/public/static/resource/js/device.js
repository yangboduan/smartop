define(['table','form'], function (Table,Form) {
    let Controller = {
        index: function () {
            Table.init = {
                table_elem: 'list',
                tableId: 'list',
                requests:{
                    index_url:'resource/Device/index',
                    add_url:'resource/Device/add',
                    edit_url:'resource/Device/edit',
                    destroy_url:'resource/Device/destroy',
                    delete_url:'resource/Device/delete',
                    import_url:'resource/Device/import',
                    export_url:'resource/Device/export',
                    modify_url:'resource/Device/modify',
				

                }
            }
            Table.render({
                elem: '#' + Table.init.table_elem,
                id: Table.init.tableId,
                url: Fun.url(Table.init.requests.index_url),
				
                init: Table.init,
                primaryKey:'id',
                toolbar: ['refresh','add','destroy','import','export','mytest'],
                cols: [[
                    {checkbox: true,},
                    {field:'ip', title: __('Ip'),align: 'center'},
					{field:'device_type', title: __('Type'),align: 'center',width:100},
                    {field:'name', title: __('Name'),align: 'center'},
                    
                    {field:'mac', title: __('Mac'),align: 'center'},
					{field:'os_type', title: __('操作系统类型'),align: 'center'},
                    {field:'user', title: __('User'),align: 'center'},
                    {field:'department', title: __('Department'),align: 'center'},
					{field:'use_desc', title: __('用途'),align: 'center'}, 
                    {field:'online_status', title: __('OnlineStatus'),align: 'center'},
					{field:'last_online_time', title: __('最后在线时间'),align: 'center'},  
                    {
                        minWidth: 150,
                        align: "center",
                        title: __("Operat"),
                        init: Table.init,
                        templet: Table.templet.operat,
                        operat:["edit","mytest"]
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
