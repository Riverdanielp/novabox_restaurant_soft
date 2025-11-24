jQuery(function() {
    "use strict";
    function sidebarDesgin(){
        let loggle_status = jQuery('.sidebar-toggle').attr('data-status');
        if(loggle_status == '1'){
            jQuery('.menu-header.small').css('padding-top', '0px');

            jQuery('.menu-header.small span').css('display', 'none');
            jQuery('.menu-header.small svg').css('display', 'block');
        }else{
            jQuery('.menu-header.small').css('padding-top', '15px');

            jQuery('.menu-header.small span').css('display', 'block');
            jQuery('.menu-header.small svg').css('display', 'none');
        }
        // console.log('sidebarDesgin');
    }
    sidebarDesgin();
    jQuery(document).on('click', '.sidebar-toggle', function(){
        sidebarDesgin();
    });
    jQuery(document).on('click', '#push_menu_btn', function(){
        setTimeout(function() {
            sidebarDesgin();
        },500);
    });
    jQuery(document).on('mouseenter', '.main-sidebar', function() {
        let loggle_status = jQuery('.sidebar-toggle').attr('data-status');
        if(loggle_status == '1'){
            jQuery('.menu-header.small').css('padding-top', '15px');

            jQuery('.menu-header.small span').css('display', 'block');
            jQuery('.menu-header.small svg').css('display', 'none');
            
        }
    });
    jQuery(document).on('mouseenter', '#push_menu_btn', function() {
        setTimeout(function() {
            let loggle_status = jQuery('.sidebar-toggle').attr('data-status');
            if(loggle_status == '1'){
                jQuery('.menu-header.small').css('padding-top', '15px');

                jQuery('.menu-header.small span').css('display', 'block');
                jQuery('.menu-header.small svg').css('display', 'none');
                
            }
        },500);
    });
    jQuery(document).on('mouseleave', '.main-sidebar', function() {
        let loggle_status = jQuery('.sidebar-toggle').attr('data-status');
        if(loggle_status == '1'){
            jQuery('.menu-header.small').css('padding-top', '0px');

            jQuery('.menu-header.small span').css('display', 'none');
            jQuery('.menu-header.small svg').css('display', 'block');
        }
    });
    

    // Common Use
    let common_use = jQuery('.common_use').length;
    if(common_use == '1'){
        jQuery('.common_use').css('display', 'none');
    }else{
        jQuery('.common_use').css('display', 'block');
    }
    // Item Stock
    let item_stock = jQuery('.item_stock').length;
    if(item_stock == '1'){
        jQuery('.item_stock').css('display', 'none');
    }else{
        jQuery('.item_stock').css('display', 'block');
    }
    // Sale Customer
    let sale_customer = jQuery('.sale_customer').length;
    if(sale_customer == '1'){
        jQuery('.sale_customer').css('display', 'none');
    }else{
        jQuery('.sale_customer').css('display', 'block');
    }
    // Purchase Expens
    let purchase_expense = jQuery('.purchase_expense').length;
    if(purchase_expense == '1'){
        jQuery('.purchase_expense').css('display', 'none');
    }else{
        jQuery('.purchase_expense').css('display', 'block');
    }
    // Transfer Damage
    let transfer_damage = jQuery('.transfer_damage').length;
    if(transfer_damage == '1'){
        jQuery('.transfer_damage').css('display', 'none');
    }else{
        jQuery('.transfer_damage').css('display', 'block');
    }

    // Attendance
    let account_attendance = jQuery('.account_attendance').length;
    if(account_attendance == '1'){
        jQuery('.account_attendance').css('display', 'none');
    }else{
        jQuery('.account_attendance').css('display', 'block');
    }
    
    // Setting
    let setting_report = jQuery('.setting_report1').length;
    if(setting_report == '1'){
        jQuery('.setting_report1').css('display', 'none');
    }else{
        jQuery('.setting_report1').css('display', 'block');
    }
    

    jQuery(document).on('click', '.grid_view2 .btn-dblue1', function(){
        jQuery('.btn-dblue1').removeClass('active');
        jQuery(this).addClass('active');
    })


    // jQuery('.food_menu_slider').slick({
    //     dots: false,
    //     arrows: true,
    //     infinite: true,
    //     prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-angle-left"></i></button>',
    //     nextArrow: '<button type="button" class="slick-next"><i class="fas fa-angle-right"></i></button>',
    //     speed: 300,
    //     slidesToShow: 7,
    //     slidesToScroll: 8,
    //     responsive: [
    //         {
    //             breakpoint: 1600,
    //             settings: {
    //                 slidesToShow: 6,
    //                 arrows: true,
    //             }
    //         },
    //         {
    //             breakpoint: 1366,
    //             settings: {
    //                 slidesToShow: 4,
    //                 arrows: true,
    //             }
    //         },
    //         {
    //             breakpoint: 1200,
    //             settings: {
    //                 slidesToShow: 3,
    //                 arrows: true,
    //             }
    //         },
    //         {
    //             breakpoint: 992,
    //             settings: {
    //                 slidesToShow: 2,
    //                 arrows: false,
    //             }
    //         },
    //         {
    //             breakpoint: 480,
    //             settings: {
    //                 slidesToShow: 1,
    //                 arrows: false,
    //             }
    //         }
    //     ]
    // });

});
