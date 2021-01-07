
$(window).load(function(){
    setTimeout(function() {
        $("#loading").fadeOut( 400, "linear" )
    }, 300);
});
$(function() { 
    "use strict";
    $(".bootstrap-datepicker").bsdatepicker({
        format: "dd/mm/yyyy"
    });
});

function readURL(input, row) {
    if(row == undefined){
        row = 0;
    }
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $(".thumbnail").eq(row).attr("src", e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

// jQuery(document).ready(function() {
    $('body').on('click', '.btn-submit', function(event) {
        var password_old = $('.password_old').val();
        var password_new = $('.password_new').val();
        var password_newconf = $('.password_newconf').val();
        if (password_old == '' || password_new == '' || password_newconf == '') {            
            $('.txt-error').removeClass("hide");
           
        } else {
            if (password_new != password_newconf) {
                $('#ModalChangePass').modal('hide');
                title = "แจ้งเตือน";
                msg = "รหัสผ่านไม่ตรงกันกรุณาตรวจสอบ" ;
                $('#ModalDel').modal('show');
                $('.modal-title').html(title);
                $('.modal-body').html(msg);
                $('.alert_default').addClass('hide');
                $('.alert_changepass').removeClass('hide');
                return false;
            }
       
        $('.txt-error').addClass("hide");
        $.ajax({
            type: "POST",
            url: base_url + "Admin/Change_password",
            data: { password_old: password_old, password_new: password_new, password_newconf: password_newconf },
            dataType: "HTML",
            success: function(result) {
                if (result == "success") {
                    $('#ModalChangePass').modal('hide');
                    title = "รายละเอียด";
                    msg = "สำเร็จ กรุณาล็อกอินใหม่อีกครั้ง" ;
                    $('#ModalDel').modal('show');
                    $('.modal-title').html(title);
                    $('.modal-body').html(msg);
                    $('.alert_default').addClass('hide');
                    $('.alert_changepass').addClass('hide');
                    $('.alert_changepass_success').removeClass('hide');
                } else {
                    $('#ModalChangePass').modal('hide');
                    title = "แจ้งเตือน";
                    msg = "รหัสผ่านไม่ถูกต้อง กรุณากรอกข้อมูลใหม่อีกครั้ง" ;
                    $('#ModalDel').modal('show');
                    $('.modal-title').html(title);
                    $('.modal-body').html(msg);
                    $('.alert_default').addClass('hide');
                    $('.alert_changepass').removeClass('hide');
                }
            }
        });
        }
    });
    $('body').on('click','.btn-close-alert-changepass-success', function(event) {
        window.location = base_url + '';
    });
    $('body').on('click','.btn-close-alert-changepass', function(event) {
        $('#ModalDel').modal('hide');
        $('#ModalChangePass').modal('show');
    });

// });