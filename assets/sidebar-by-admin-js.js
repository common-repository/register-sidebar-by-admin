jQuery(document).ready(function ($) {
    $('.create-new-sidebar').click(function () {
        $('.register_new_sidebar_ctr').slideToggle(400).toggleClass('active');
    });
    $(document).on('click','.edit-sidebar',function (e) {
        e.preventDefault();
        $formID = $(this).parent('.item').attr('data-sidebar-id');
        if($formID){
            if($(".sidebar-edit-form-"+$formID).hasClass('active')){
                $(".sidebar-edit-form-"+$formID).slideToggle(400,function () {
                    $(".sidebar-edit-form-"+$formID).toggleClass('active');
                    $('div.item').removeClass('active');
                });
            }else{
                $(".edit_sidebar_ctr").hide().removeClass('active');
                $('div.item').removeClass('active');
                $(this).parent('.item').addClass('active');
                $(".sidebar-edit-form-"+$formID).slideToggle(400).toggleClass('active');
            }
        }
    });
    $(document).on('click','.trash-sidebar',function (e) {
        e.preventDefault();
        if (confirm("are you sure you want to delete this?")){
            $formID = $(this).parent('.item').attr('data-sidebar-id');
            if($formID){
                $('#deleteSidebar-'+$formID).submit();
            }
        }
    });
});