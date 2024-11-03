function ws_img_login() {
    $('#login_btn').attr('onclick', 'ws_img_cancel_login($(this))');
    $('#login_btn').text('取消登录');
    $('#token-0-1').hide();
    $('#ws_img_login').show();
}

function ws_img_cancel_login() {
    $('#login_btn').attr('onclick', 'ws_img_login($(this))');
    $('#login_btn').text('点击登录');
    $('#token-0-1').show();
    $('#ws_img_login').hide();
}

function invokeSetTime(obj) {
    var countdown = 60;
    settime(obj);

    function settime(obj) {
        if (countdown == 0) {
            $(obj).attr("disabled", false);
            $(obj).text("获取验证码");
            countdown = 60;
            return;
        } else {
            $(obj).attr("disabled", true);
            $(obj).text("(" + countdown + ") s 重新发送");
            countdown--;
        }
        setTimeout(function () {
            settime(obj)
        }, 1000);
    }
}

var captcha = new TencentCaptcha('2051150327', function (res) {
    if (res.ret === 0) {
        var data = {
            phone: $('#phone').val(),
            ticket: res.ticket,
            randstr: res.randstr
        };
        layer.msg('发送中...', {
            icon: 16
            , shade: 0.01
            , time: false
        });
        $.ajax({
            url: SITE_URL + '/action/send',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (res) {
                layer.closeAll();
                if (res.code == 1) {
                    layer.msg('发送成功');
                    new invokeSetTime("#send");
                } else {
                    layer.alert('发送短信验证码失败，原因：' + res.msg);
                }
            },
            error: function (e) {
                layer.alert('发送短信验证码网络错误');
            }
        });
    }
});

function send_sms() {
    var phone = $('#phone').val();
    if (!phone) return layer.msg('手机号码不可为空');
    captcha.show();
}

function login() {
    var phone = $('#phone').val();
    if (!phone) return layer.msg('手机号码不可为空');
    var code = $('#code').val();
    if (!code) return layer.msg('验证码不可为空');
    layer.msg('登录中...', {
        icon: 16
        , shade: 0.01
        , time: false
    });
    $.ajax({
        url: SITE_URL + '/action/login',
        type: 'POST',
        data: {phone: phone, code: code},
        dataType: 'json',
        success: function (res) {
            layer.closeAll();
            if (res.code == 1) {
                var token = res.data.loginInfo.token;
                $('#token-0-1').attr('value', token);
                $('#token-0-1').show();
                $('#ws_img_login').hide();
                $('#login_btn').attr('onclick', 'ws_img_login($(this))');
                $('#login_btn').text('点击登录');
                layer.msg('登录成功，获取Token成功');
            } else {
                layer.alert('登录失败，原因：' + res.msg);
            }
        },
        error: function (e) {
            layer.alert('登录失败网络错误');
        }
    });
}