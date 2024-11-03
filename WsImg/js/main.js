let token;
let folder;
let domain;
jQuery(document).ready(function ($) {

    init();

    get_list(1);

    $('#admin-img-file').change(function () {
        var label = $(".admin-upload-img label");
        var len = this.files.length;
        var cnt = 0;
        if (len == 0) return layer.msg('未选择文件');
        label.text("上传中...");
        layer.msg('上传中...', {
            icon: 16
            , shade: 0.01
            , time: false
        });
        for (var i = 0; i < len; i++) {
            var f = this.files[i];
            var formData = new FormData();
            formData.append('file', f);
            formData.append('token', token);
            formData.append('key', folder + '/' + generateUUID() + '.' + getFileExtension(this.files[i].name));
            formData.append('name', this.files[i].name);
            $.ajax({
                url: 'https://upload.qiniup.com/',
                type: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                dataType: 'json',
                success: function (res) {
                    if (res.key) {
                        $.ajax({
                            url: AJAX_URL + '&do=record',
                            type: 'POST',
                            data: {
                                name: res.name,
                                width: res.w,
                                height: res.h,
                                size: res.size,
                                url: domain + '/' + res.key
                            },
                            dataType: 'json',
                            success: function (d) {
                                if (d.code == 1) {
                                    cnt++;
                                    get_list(1);
                                    if (cnt === len) {
                                        layer.closeAll();
                                        layer.msg('上传成功，点击预览图即可插入编辑器');
                                        label.text("图片上传");
                                    }
                                }
                            },
                            error: function (e) {
                                layer.closeAll();
                                label.text("图片上传");
                                layer.alert('图片入库失败，网络错误');
                            }
                        });
                    } else {
                        layer.closeAll();
                        label.text("图片上传");
                        layer.alert(res.error);
                    }
                },
                error: function (e) {
                    layer.closeAll();
                    label.text("图片上传");
                    if (e.responseText) {
                        layer.alert(eval("(" + e.responseText + ")").error);
                    } else {
                        layer.alert('上传失败，网络错误');
                    }
                }
            });
        }
    });
});

function getFileExtension(name) {
    return name.slice(name.lastIndexOf(".") + 1);
}

function init() {
    $.ajax({
        url: AJAX_URL + '&do=init',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.code == 1) {
                token = res.data.sessionToken;
                folder = res.data.folder;
                domain = res.data.mappingDomain;
            } else {
                layer.alert('初始化微商相册Token失败，原因：' + res.msg);
            }
        },
        error: function (e) {
            layer.alert('初始化微商相册Token网络错误');
        }
    });
}

function generateUUID() {
    let d = new Date().getTime();
    let d2 = (performance && performance.now && performance.now() * 1000) || 0;
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        let r = Math.random() * 16;
        if (d > 0) {
            r = (d + r) % 16 | 0;
            d = Math.floor(d / 16);
        } else {
            r = (d2 + r) % 16 | 0;
            d2 = Math.floor(d2 / 16);
        }
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}

function get_list(page) {
    $.ajax({
        url: AJAX_URL + '&do=list&page=' + page,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            var data = res.data;
            if (data.length > 0) {
                var html = '';
                for (var i in data) {
                    html += `<div class="item"><img src="` + data[i].url + `" width="150px;" onclick="Typecho.insertFileToEditor('` + data[i].name + `','` + data[i].url + `',true);" title="点击插入编辑器"></div>`;
                }
                var next = page;
                next++;
                $('#more').html('<button class="btn" type="button" onclick="$(this).remove();get_list(' + next + ');">加载更多</button>');
            } else {
                if (page == 1) {
                    $('#more').html('<h6 class="typecho-list-table-title">没有任何内容</h6>');
                } else {
                    $('#more').html('<h6 class="typecho-list-table-title">没有更多了</h6>');
                }
            }
            $('#img-data').show();
            if (page == 1) {
                $('#img-list').html(html);
            } else {
                $('#img-list').append(html);
            }
        },
        error: function (e) {
            layer.msg('获取图片列表网络错误');
        }
    });
}