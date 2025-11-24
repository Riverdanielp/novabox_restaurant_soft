jQuery(document).ready(function () {
  jQuery(".right__sidebar__toggle").on("click", function () {
    jQuery(".main-header").toggleClass("active_sidebar");
    jQuery(".main-sidebar2").toggleClass("active_sidebar");
    jQuery(".sidebar2_logo").find(".logo__mini").toggle(0);
    jQuery(".sidebar2_logo").find(".logo__lg").toggle(0);
  });

  const thisForArabic = () => {
    if (jQuery(".content-header").hasClass("dashboard_content_header")) {
      console.log("find it");
    } else {
      jQuery(".content-header").addClass("lang_arabic");
    }
  };
 /*  thisForArabic();*/


  function setActiveCurrentURL(){
    // Get the current URL
    let currentUrl = window.location.href;
    // Find the active_sub_menu when the current location matches the link
    jQuery('.treeview').has('a[href="' + currentUrl + '"]').addClass('active_sub_menu');
    jQuery('.treeview2').has('a[href="' + currentUrl + '"]').addClass('active_sub_menu');
    jQuery('.treeview').has('a[href="' + currentUrl + '"]').addClass('menu-open');
    jQuery('.treeview2').has('a[href="' + currentUrl + '"]').addClass('menu-open');
    jQuery('.treeview').has('a[href="' + currentUrl + '"]').find('a[href="' + currentUrl + '"]').parent().addClass('treeMenuActive');
    jQuery('.treeview2').has('a[href="' + currentUrl + '"]').find('a[href="' + currentUrl + '"]').parent().addClass('treeMenuActive');
  }
  setActiveCurrentURL();

  jQuery(document).ready(function(){
    jQuery(".menu-open").click(function(e){
      // Toggle the visibility of the inner UL with animation
      jQuery(this).children(".treeview-menu").slideToggle();
    });
  });

  let activeSubMenu = jQuery(".menu-open");
  if (activeSubMenu.length) {
    let scrollPosition = activeSubMenu.position().top - 100;
    jQuery(".sidebar-menu").scrollTop(scrollPosition);
  }


  jQuery(document).on('click', '.biponi_silver', function(e){
    e.preventDefault();
    toastr['error'](('Available for Gold, Pharmacy and Enterprise  packages.'), '');
  });

});
