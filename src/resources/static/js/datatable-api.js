function eolDatatable(setting) {

    if (typeof (setting.table) == 'undefined') {
        console.log('未指定处理的Table');
        return;
    }

    // 需要处理的表格
    this.table = setting.table;
    this.IdOrClass = '';
    if (setting.table.substr(0, 1) === '.' || setting.table.substr(0, 1) === '#') {
        this.IdOrClass = setting.table.substr(0, 1);
        this.table = setting.table.substr(1);
    }

    this.setConfig('edit', true); // 编辑按钮
    this.setConfig('play', true); // 启用按钮
    this.setConfig('pause', true); // 停用按钮
    this.setConfig('trash', true); // 删除按钮
    this.setConfig('editId', "#large"); //使用的modal容器ID

    this.setConfig('pageLength', 10); // 每页显示记录数
    this.setConfig('ajax.type', 'GET'); // 请求方式

    for (k in setting) {
        if (k === 'table') continue;

        this.setConfig(k, setting[k]);
    }

    this.init();

}

eolDatatable.prototype = {
    objectTable: undefined,

    //基本配置信息
    config: {
        language: '/app-assets/data/locales/datatable/Chinese.json',
        button:[],
        modal:[],
        buttons:[],
    },

    constructor: eolDatatable,

    init: function () {
        // dataTables闭名可访问
        var objectParent = this;

        this.defaultConfig = {
            stateSave: false,
            select: 'multi+shift',
            dom: "lr<'optionButtons dataTables_filter'>tip",
            language: {
                url: this.config.language
            },
            initComplete: objectParent.initComplete(),
            rowCallback: function (row, data, displayNum, displayIndex, dataIndex) {
                json = objectParent.objectTable.ajax.json();

                if (typeof json !== 'undefined') {
                    for (index in data) {
                        // console.log(index, row, data, displayNum, displayIndex, dataIndex)

                        if (typeof json.extra === "undefined" || typeof json.extra[index] === 'undefined') continue;

                        // 判断当前是否有配置重绘属性
                        columns = objectParent.getSetting().columns;

                        if (json.extra[index] === null) {
                            continue;
                        }
                        // 关联位置
                        linkNum = json.extra[index]['datatableIndex'];
                        // 属性已定义重写当前属性，不处理
                        let tempColumn = columns.find(item => item.data === index);
                        if (typeof tempColumn == 'undefined' || typeof tempColumn.render === "function") continue;
                        if (data[index] === "") {
                            $(row).find('td').eq(linkNum).html('暂无');
                        } else if (('' + data[index]).indexOf(',') === -1) {
                            $(row).find('td').eq(linkNum).html('<span class="badge badge-success">' + json.extra[index][data[index]] + '</span>');
                        } else {
                            // 多个元素
                            $.map(data[index].split(','), function (v, k) {
                                if (k === 0) {
                                    $(row).find('td').eq(linkNum).html('<span class="badge badge-success">' + json.extra[index][v] + '</span>');
                                    return true;
                                }

                                $(row).find('td').eq(linkNum).append(' , <span class="badge badge-success">' + json.extra[index][v] + '</span>');

                            })
                        }
                    }
                }

            },
        };

        // 服务器处理
        if (this.getConfig('ajax') !== undefined) {
            let curConfig = this.config;
            this.defaultConfig.ajax = {
                data: function (params) {
                    let tmpData = [];

                    let modifyParam = function (name, value){
                        for(let i in tmpData){
                            if (tmpData[i].name === name && tmpData[i].name.indexOf('[]') === -1){
                                tmpData[i].value = value;
                                return;

                                if (typeof tmpData[i].value == "string") tmpData[i].value = value;
                                // order
                                if (typeof tmpData[i].value == "object") Object.assign(tmpData[i].value, value);

                                return;
                            }
                        }
                        tmpData.push({name:name, value:value});
                    };
                    if (Object.keys(params).length > 0 ){
                        modifyParam('draw', params.draw);
                        modifyParam('length', params.length);
                        modifyParam('start', params.start);

                        // 排序
                        for (let i of params.order) {
                            modifyParam('order['+params.columns[i.column].data+']', i.dir);
                        }
                    }

                    // 拼接搜索条件
                    if (objectParent.getConfig('serverSide') === true && objectParent.getConfig('pageSearch') &&
                        $('section[data-type=search-card-conditions]').find('div.card-content').hasClass('show') // 检索框必须打开
                    ) {
                        $.each($('#datatable-' + objectParent.table).closest('form').serializeArray(), function (index){
                            modifyParam(this['name'], this['value']);
                        });
                    }

                    // 兼容laravel的分页逻辑
                    if (params.length === 0) modifyParam('page', 1);
                    else if (params.length === undefined) modifyParam('page', -1);
                    else modifyParam('page', params.start / params.length + 1);

                    return tmpData;
                },
                dataSrc: function (json) {
                    return json.data;
                },
                dataFilter: function (data) {
                    data = JSON.parse(data)

                    if (typeof data.data == "undefined"){

                        // 检测是否存在报错
                        if (typeof data.errors != "undefined"){
                            let errorMsg = '';
                            for (let index of Object.keys(data.errors)) {
                                errorMsg += data.errors[index].join("<br>");
                            }
                            eolError(errorMsg);
                        }
                        return '[]';
                    }

                    // 生成扩展字段对应的索引key
                    if (typeof data.data.extra !== 'undefined') {

                        columns = objectParent.getSetting().columns;
                        for (index in data.data.extra) {
                            if (data.data.extra[index] === null) {
                                continue;
                            }

                            var hiddenNum = 0;

                            for (let i = 0; i < columns.length; i++) {
                                if (columns[i].bVisible === false) hiddenNum++;

                                if (columns[i].data == index) {
                                    data.data.extra[index]['datatableIndex'] = i - hiddenNum;
                                }
                            }
                        }
                    }

                    // 如果配置了ajaxDataFilter，回调处理
                    if (typeof curConfig['ajaxDataFilter'] == "function"){
                        data.data = curConfig['ajaxDataFilter'](data.data);
                    }
                    return JSON.stringify(data.data);
                }
            };
        }

        objectParent.setSetting();

        this.objectTable = $(this.IdOrClass + this.table);

        // 添加监听事件
        let eventCallback = this.getConfig('eventCallback');
        if (eventCallback !== undefined) {
            for (var index in eventCallback) {
                this.objectTable.off(index).on(index, eventCallback[index]);
            }
        }
        // 初始化dataTable
        this.objectTable = this.objectTable.DataTable(this.defaultConfig);

        // 判断是否有默认排序
        if (typeof this.getConfig('order') !== "undefined") {
            this.objectTable.order(this.getConfig('order'));
        }

        // 绑定容器刷新按钮
        this.getConfig('reload') && $(this.table).closest('div.card').find('a[data-action="reload"]').on('click', function () {
            objectParent.getConfig('ajax') && objectParent.getDatatable().ajax.reload(null, false);
        });

        // 表单检索
        if (this.getConfig('pageSearch')) {
            $('#datatable-' + this.table).on('click', function () {
                // 加载ajax筛选
                if (objectParent.getConfig('ajax') && objectParent.getConfig('serverSide')) {
                    objectParent.getDatatable().ajax.reload();
                }
            })
        }
        // 编辑按钮处理
        if (this.getConfig('edit')) {
            this.objectTable.on('select deselect', function (e, dt, type, indexes) {
                var table = $(e.currentTarget).attr('id') + '_wrapper_edit';
                if (objectParent.getSelected().length > 1) {
                    $('#' + table).hide();
                } else {
                    $('#' + table).show();
                }
            });
        }
    },

    getSelected: function () {

        return this.objectTable.rows('.selected').data();

        if (data.length === 0) {
            eolWarning('未选中需要操作的数据.');
        }

        return data;
    },

    initComplete: function () {

        // dataTables闭名可访问
        var objectParent = this;

        return function () {
            // 当前dataTable的ID
            table = $(this).attr('id') + '_wrapper';

            let buttons = [];
            let buttonFun = function (name){
                let tmpButton = objectParent.getConfig(name);
                tmpButton.map((item, index) => {
                    buttons.push(Object.assign(item, {kind:name}))
                });
            };

            // 获取button按钮
            buttonFun('button');

            // 获取modal按钮
            buttonFun('modal');

            // 获取 组合 按钮
            buttons = buttons.concat(objectParent.getConfig('buttons'))

            // 渲染按钮
            buttons.sort(function (a,b){return a.sort-b.sort});
            buttons.map((item, index) => {
                // 按钮检测
                if (item.kind === 'button' && (item['id'] === undefined || item['method'] === undefined ||
                    item['url'] === undefined || item['callback'] === undefined)){
                    return;
                }
                // 验证必填
                if (item.kind === 'modal' && (item['id'] === undefined || item['url'] === undefined || item['div'] === undefined)) {
                    return;
                }

                // 设置默认字段
                if (item.message === undefined) item.message = '未知';
                if (item.type === undefined) item.type = 'question';
                if (item.icon === undefined) item.icon = 'la-question';
                if (item.text === undefined) item.text = '未知';
                if (item.color === undefined) item.color = 'danger';
                if (item.requireMessage === undefined) item.requireMessage = '未选中需要操作的数据.';
                if (item.gotoURL === undefined) item.gotoURL = '';
                if (item.cover === undefined) item.cover = true;
                if (item.confirmText === undefined) item.confirmText = item.text + '选中{length}条记录？';
                if (item.param === undefined) item.param = false;

                // 添加按钮
                let aTag = document.createElement('a');
                $(aTag).attr('data-index', index);
                $(aTag).attr('href', 'javascript:;');
                $(aTag).attr('id', table + '_' + item.id);
                $(aTag).html('<span class="la ' + item.icon + '"></span>'+item.text );
                $(aTag).addClass('btn btn-' + item.color + ' mb-1 mr-1 btn-sm');
                $('#' + table + ' div.optionButtons').append(aTag);

                // 点击事件
                $('#' + table + '_' + item.id).on('click', function (){
                    // 获取url
                    let index = $(this).data('index');
                    let curButton = buttons[index];
                    let data = objectParent.getSelected();
                    if (data.length <= 0) {
                        eolWarning(curButton.requireMessage);
                        return;
                    }
                    let ids = $.map(data, function (val) {
                        return val[objectParent.getPrimary()];
                    });
                    let curUrl = curButton.url;
                    if (curButton.kind === 'button'){
                        curUrl = curButton.url.replaceAll('{ids}', ids.toString());
                        eolConfirm(
                            curButton.message, //0
                            curButton.cover ? data.length : curButton.confirmText.replaceAll('{length}', data.length),//1
                            curButton.type,//2
                            curButton['callback'],//3
                            curButton.method,//4
                            curUrl,//5
                            '',//6
                            curButton.gotoURL,//7
                            curButton.cover,//8
                        );
                        return;
                    }

                    // modal类型
                    if (curButton.param){
                        curUrl = curButton.url + (curButton.url.indexOf('?') === -1 ? '?' : '&') +  objectParent.getPrimary() + '=' + ids.toString();
                    }
                    loadModal(curUrl, $(curButton.div).modal('show'));
                });
            });

            // 编辑按钮
            if (objectParent.getConfig('edit')) {
                $('#' + table + ' div.optionButtons').append('<a href="javascript:;" id="' + table + '_edit" class="btn btn-info mb-1 mr-1 btn-sm newbtn1"><span class="la"></span> 编辑</a>');
                $('#' + table + '_edit').on('click', function () {
                    data = objectParent.getSelected();
                    if (data.length === 0) {
                        eolWarning('未选中需要操作的数据');
                        return;
                    }
                    if (data.length !== 1) {
                        eolWarning('编辑操作只支持单条操作.');
                        return;
                    }

                    // console.log(objectParent.getConfig('ajax'))

                    var url = new URL(objectParent.getConfig('ajax'));

                    // 操作编辑
                    $(objectParent.getConfig('editId')).modal().find('.modal-content').load(url.origin + url.pathname + '/' + data[0][objectParent.getConfig('primary')] + '/edit' + url.search, modalLoad);

                    // console.log(data[0][objectParent.getConfig('primary')]);
                    // window.location.href= objectParent.getConfig('ajax')+'/'+data[0][objectParent.getConfig('primary')]+'/edit';

                });
            }
            // 启用按钮
            if (objectParent.getConfig('play')) {
                $('#' + table + ' div.optionButtons').append('<a href="javascript:;" id="' + table + '_play" class="btn btn-success mb-1 mr-1 btn-sm newbtn1"><span class="la"></span> 启用</a>');
                $('#' + table + '_play').on('click', function () {
                    data = objectParent.getSelected();
                    if (data.length <= 0) {
                        eolWarning('未选中需要操作的数据.');
                        return;
                    }

                    var ids = $.map(data, function (val) {
                        return val[objectParent.getPrimary()];
                    });

                    var url = new URL(objectParent.getConfig('ajax'));


                    eolConfirm('启用', data.length, 'question', function (data) {
                        if (data.code === "0000") {
                            objectParent.getDatatable().ajax.reload(null, false);
                            return;
                        }
                        eolAlertError(data.message, data.errors === undefined ? "error" : data.errors);

                    }, 'put', url.origin + url.pathname + '/' + ids.toString() + '?format=json' + url.search.replaceAll('?', '&'), {'status': 1});
                });
            }
            // 暂停按钮
            if (objectParent.getConfig('pause')) {
                $('#' + table + ' div.optionButtons').append('<a href="javascript:;" id="' + table + '_pause"class="btn btn-warning mb-1 mr-1 btn-sm newbtn1"><span class="la"></span> 暂停</a>');
                $('#' + table + '_pause').on('click', function () {
                    data = objectParent.getSelected();
                    if (data.length <= 0) {
                        eolWarning('未选中需要操作的数据.');
                        return;
                    }

                    var ids = $.map(data, function (val) {
                        return val[objectParent.getPrimary()];
                    });

                    var url = new URL(objectParent.getConfig('ajax'));

                    eolConfirm('暂停', data.length, 'question', function (data) {
                        if (data.code === "0000") {
                            objectParent.getDatatable().ajax.reload(null, false);
                            return;
                        }
                        eolAlertError(data.message, data.errors === undefined ? "error" : data.errors);

                    }, 'put', url.origin + url.pathname + '/' + ids.toString() + '?format=json' + url.search.replaceAll('?', '&'), {'status': 2});
                });
            }
            // 删除按钮
            if (objectParent.getConfig('trash')) {
                $('#' + table + ' div.optionButtons').append('<a href="javascript:;" id="' + table + '_trash"class="btn btn-danger mb-1 mr-1 btn-sm newbtn1"><span class="la "></span> 删除</a>');
                $('#' + table + '_trash').on('click', function () {
                    data = objectParent.getSelected();
                    if (data.length <= 0) {
                        eolWarning('未选中需要操作的数据.');
                        return;
                    }

                    var ids = $.map(data, function (val) {
                        return val[objectParent.getPrimary()];
                    });

                    var url = new URL(objectParent.getConfig('ajax'));

                    eolConfirm('删除', data.length, 'question', function (data) {
                        if (data.code === "0000") {
                            objectParent.getDatatable().ajax.reload(null, false);
                            return;
                        }
                        eolAlertError(data.message, data.errors === undefined ? "error" : data.errors);

                    }, 'delete', url.origin + url.pathname + '/' + ids.toString() + '?format=json' + url.search.replaceAll('?', '&'));
                });
            }
        }

    },

    setConfig: function (key, val) {
        this.config[key] = val;
    },

    getConfig: function (key) {
        if (typeof (this.config[key]) === 'undefined') {
            return;
        }

        return this.config[key];
    },
    setSetting: function () {
        var fields = ['ajax', 'columns', 'paging', 'processing', 'serverSide', 'pageLength', 'ajax.url','ajax.type', 'ajax.data', 'dom', 'select',
            'stateSave', 'ordering', 'stateLoaded'];

        for (k in fields) {
            val = this.getConfig(fields[k]);
            if (val !== undefined) {
                if ((fields[k] === 'ajax' && typeof val =='string') || fields[k] === 'ajax.url') {
                    val = val + (val.indexOf('?') !== -1 ? '&' : '?') + 'format=json';
                    this.defaultConfig['ajax']['url'] = val;
                    continue;
                }

                if (fields[k].indexOf('ajax.') !== -1){
                    let tmpField = fields[k].split('.');
                    this.defaultConfig[tmpField[0]][tmpField[1]] = val;
                    continue;
                }

                this.defaultConfig[fields[k]] = val;
            }
        }

    },
    getSetting: function () {
        return this.defaultConfig;
    },
    // 获取主键标识
    getPrimary: function () {
        return typeof this.config['primary'] == 'undefined' ? '' : this.config['primary'];
    },
    getDatatable: function () {
        return this.objectTable;
    }

}
