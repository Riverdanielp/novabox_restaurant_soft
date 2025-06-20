$(document).ready(function () {
    "use strict";
    let base_url = $("base").attr("data-base");
    let role = $("base[data-role]").attr("data-role");
    let kitchen_id = $("#kitchen_id").val();
    let csrf_value_ = $("#csrf_value_").val();
    let sale_no = $("#sale_no").val();
    let table = $("#table").val();
    let order_type_txt = $("#order_type").val();
    let quantity_ln = $("#quantity_ln").val();
    let modifiers_ln = $("#modifiers_ln").val();
    let note_ln = $("#note_ln").val();
    let Qty_Old = $("#Qty_Old").val();
    let Qty_New = $("#Qty_New").val();
    let dine_ln = $("#dine_ln").val();
    let take_away_ln = $("#take_away_ln").val();
    let delivery_ln = $("#delivery_ln").val();
    let customer_name_ln = $("#customer_name_ln").val();
    let text_not_ready_ln = $("#text_not_ready_ln").val();
    let text_ready_ln = $("#text_ready_ln").val();
    let text_in_preparation_ln = $("#text_in_preparation_ln").val();
    let text_ready = $("#text_ready").val();
    let text_done = $("#text_done").val();
    let text_not_ready = $("#text_not_ready").val();
    let text_in_preparation = $("#text_in_preparation").val();

  $(document).on("click", "#refresh_orders_button", function () {
    $("#refresh_it_or_not").html("Yes");
    $("#group_by_order_item").val("").trigger("change");
    refresh_orders();
  });


    $(document).on("change", "#group_by_order_item", function () {
    let menu_id = $(this).val();
    let menu_name = $(
      "#group_by_order_item option[value='" + menu_id + "']"
    ).text();

    $("#order_holder .single_order").each(function (i, obj) {
      let $this = $(this);
      let found = 0;
      $(this)
        .find(".items_holder .single_item")
        .each(function () {
          let this_menu_name = $(this).find(".item_name").html();
          if (this_menu_name == menu_name) {
            found++;
          }
        });
      if (found > 0) {
        $this.css("display", "block");
      } else {
        $this.css("display", "none");
      }
    });
    $("#refresh_it_or_not").html("No");
  });

  $("#notification_list_holder")
    .slimscroll({
      height: "240px",
    })
    .parent()
    .css({
      background: "#f5f5f5",
      border: "0px solid #184055",
    });
  $("#order_holder.order_holder .single_order .items_holder")
    .slimscroll({
      height: "270px",
    })
    .parent()
    .css({
      background: "#fff",
      border: "0px solid #184055",
    });
  $(document).on("click", ".items_holder .single_item", function () {
    let single_order = $(this).parent().parent().parent();
    // if (single_order.attr("data-order-type") == "Dine In") {
      if ($(this).attr("data-selected") == "selected") {
        if (
          $(this).attr("data-cooking-status") == "Done" ||
          $(this).attr("data-cooking-status") == "Started Cooking"
        ) {
          $(this).attr("data-selected", "selected");
        }
        $(this).attr("data-selected", "unselected");
        let single_order_selected_item = single_order.find(
          '.single_item[data-selected="selected"]'
        ).length;
        if (single_order_selected_item == 0) {
          single_order.find(".start_cooking_button").fadeOut();
          single_order.find(".done_cooking").fadeOut();
        }
        $(this).find(".select_single_item").prop("checked",false);
      } else {
        $(this).attr("data-selected", "selected");
        if ($(this).find(".single_item_cooking_status").html() == text_not_ready) {
          single_order.find(".start_cooking_button").fadeIn();
        }
        if (
          $(this).find(".single_item_cooking_status").html() == text_in_preparation
        ) {
          single_order.find(".done_cooking").fadeIn();
        }
        single_order.find('.start_cooking_button').fadeIn();
        single_order.find('.done_cooking').fadeIn();
        $(this).find(".select_single_item").prop("checked",true);
      }
    // } else {
    //   swal({
    //     title: "Alert",
    //     text: "¡Debes seleccionar todo para pedidos para llevar o entrega a domicilio, ya que estos están empaquetados!",
    //     confirmButtonColor: "#b6d6f6",
    //   });
    // }
  });
  $(document).on("click", ".select_all_of_an_order", function () {
    let order_id = $(this).attr("id").substr(23);
    if($("#all_select_all_of_an_order_"+order_id).is(":checked")){
        $("#single_order_" + order_id + " .items_holder .single_item").attr(
          "data-selected",
          "selected"
        );
        $("#single_order_" + order_id + " .items_holder .single_item").attr(
          "data-selected",
          "selected"
        );
        $("#start_cooking_button_" + order_id).fadeIn();
        $("#done_cooking_" + order_id).fadeIn();
        $(this).parent().parent().parent().find(".select_single_item").prop("checked",true);
    }else{
      $(this).parent().parent().parent().find(".select_single_item").prop("checked",false);
        $("#single_order_" + order_id + " .items_holder .single_item").attr(
          "data-selected",
          "unselected"
        );
         
        $(
          "#single_order_" +
            order_id +
            ' .items_holder .single_item[data-cooking-status="Done"]'
        ).attr("data-selected", "selected");
        
        $(
          "#single_order_" +
            order_id +
            ' .items_holder .single_item[data-cooking-status="Started Cooking"]'
        ).attr("data-selected", "selected");
        $("#start_cooking_button_" + order_id).fadeOut();
        $("#done_cooking_" + order_id).fadeOut();
    }
    
    
  }); 

  $(document).on("click", ".start_cooking_button", function () {
    let sale_id = $(this).attr("id").substr(21);
    if ($("#single_order_" + sale_id).attr("data-order-type") == "Dine In") {
      if (
        $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).length > 0
      ) {
        let previous_id = "";
        let j = 1;
        let total_items = $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).length;
        $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).each(function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(15);
          } else {
            previous_id += $(this).attr("id").substr(15) + ",";
          }
          j++;
        });

        bgColorAdd();
        let previous_id_array = previous_id.split(",");
        previous_id_array.forEach(function (entry) {
          $("#detail_item_id_" + entry).attr("data-selected", "selected");
       
           
          $(
            "#detail_item_id_" +
              entry +
              " .single_item_right_side .single_item_cooking_status"
          ).html(text_in_preparation_ln);

          $(
            "#detail_item_id_" +
              entry +
              " .single_item_right_side .single_item_cooking_status"
          ).addClass("start_cooking_button");

        });
        
        if (previous_id != "") {
          let url = base_url + "Kitchen/update_cooking_status_ajax";
          $.ajax({
            url: url,
            method: "POST",
            data: {
              previous_id: previous_id,
              cooking_status: "Started Cooking",
              csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
              bgColorAdd();
              // swal({
              //   title: "Alert",
              //   text: "Cooking Started!!",
              //   confirmButtonColor: "#b6d6f6",
              // });
            },
            error: function () {
              alert("error");
            },
          });
        }
      } else {
        swal({
          title: "Alert!",
          text: "Por favor seleccione un artículo para cocinar!",
          confirmButtonColor: "#b6d6f6",
        });
      }
    } else {
      let previous_id = "";
      let j = 1;
      let total_items = $(
        "#single_order_" + sale_id + " .items_holder .single_item"
      ).length;

      $("#single_order_" + sale_id + " .items_holder .single_item").each(
        function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(15);
          } else {
            previous_id += $(this).attr("id").substr(15) + ",";
          }
          j++;
        }
      );
      let previous_id_array = previous_id.split(",");
      previous_id_array.forEach(function (entry) {
        
        $("#detail_item_id_" + entry).attr("data-selected", "selected");
        $("#detail_item_id_" + entry).css("background-color", "#B5D6F6");
        // Si quieres actualizar el texto de estado en tiempo real:
        $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_in_preparation);
        $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("start_cooking_button");

        // $("#detail_item_id_" + entry).attr("data-selected", "selected");
       
       
        // $(
        //   "#detail_item_id_" +
        //     entry +
        //     " .single_item_right_side .single_item_cooking_status"
        // ).html(text_in_preparation);

        // $(
        //   "#detail_item_id_" +
        //     entry +
        //     " .single_item_right_side .single_item_cooking_status"
        // ).addClass("start_cooking_button");
      });
      if (previous_id != "") {
        let url =
          base_url + "Kitchen/update_cooking_status_delivery_take_away_ajax";
        $.ajax({
          url: url,
          method: "POST",
          data: {
            previous_id: previous_id,
            cooking_status: "Started Cooking",
            csrf_irestoraplus: csrf_value_,
              kitchen_id: kitchen_id,
          },
          success: function (response) {
            bgColorAdd();
            // swal({
            //   title: "Alert",
            //   text: "Cooking Started!!",
            //   confirmButtonColor: "#b6d6f6",
            // });
          },
          error: function () {
            alert("error");
          },
        });
      }
    }
  });

  $(document).on("click", ".done_cooking", function () {
    let sale_id = $(this).attr("id").substr(13);
    if ($("#single_order_" + sale_id).attr("data-order-type") == "Dine In") {
      if (
        $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).length > 0
      ) {
        let previous_id = "";
        let j = 1;
        let total_items = $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).length;
        $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).each(function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(15);
          } else {
            previous_id += $(this).attr("id").substr(15) + ",";
          }
          j++;
        });
        let previous_id_array = previous_id.split(",");
        previous_id_array.forEach(function (entry) {
          $("#detail_item_id_" + entry).attr("data-selected", "selected");
          $("#detail_item_id_" + entry).css("background-color", "#5DB745");
          // Opcional: cambia el texto de estado
          $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_ready);
          $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("done_cooking");
          $("#detail_item_id_" + entry + " .single_item_cooking_status").removeClass("start_cooking_button");

        });
      
        if (previous_id != "") {
          let url = base_url + "Kitchen/update_cooking_status_ajax";
          $.ajax({
            url: url,
            method: "POST",
            data: {
              previous_id: previous_id,
              cooking_status: "Done",
              csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
              // swal({
              //   title: "Alert",
              //   text: "Cocina Terminada",
              //   confirmButtonColor: "#b6d6f6",
              // });
            },
            error: function () {
              alert("error");
            },
          });
        }
      } else {
        swal({
          title: "Alert!",
          text: "¡Seleccione un artículo para cocinar el artículo listo!",
          confirmButtonColor: "#b6d6f6",
        });
      }
    } else {
      let previous_id = "";
      let j = 1;
      let total_items = $(
        "#single_order_" +
          sale_id +
          " .items_holder .single_item[data-selected=selected]"
      ).length;
      if (total_items > 0) {
        $(
          "#single_order_" +
            sale_id +
            " .items_holder .single_item[data-selected=selected]"
        ).each(function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(15);
          } else {
            previous_id += $(this).attr("id").substr(15) + ",";
          }
          j++;
        });
        let previous_id_array = previous_id.split(",");
        previous_id_array.forEach(function (entry) {
          console.log('entry', entry);
          $("#detail_item_id_" + entry).attr("data-selected", "selected");
          $("#detail_item_id_" + entry).css("background-color", "#5DB745");
          // Opcional: cambia el texto de estado
          $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_ready);
          $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("done_cooking");
          $("#detail_item_id_" + entry + " .single_item_cooking_status").removeClass("start_cooking_button");
        });
        if (previous_id != "") {
          let url =
            base_url + "Kitchen/update_cooking_status_delivery_take_away_ajax";
          $.ajax({
            url: url,
            method: "POST",
            data: {
              previous_id: previous_id,
              cooking_status: "Done",
              csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
              // swal({
              //   title: "Alert",
              //   text: "Cooking Done!!",
              //   confirmButtonColor: "#b6d6f6",
              // });
            },
            error: function () {
              alert("error");
            },
          });
        }
      } else {
        swal({
          title: "Alert",
          text: "¡Seleccione un artículo para cocinar el artículo listo!",
          confirmButtonColor: "#b6d6f6",
        });
      }
    }
  });

  $(document).on("click", "#help_button", function () {
    $("#help_modal").fadeIn("500");
  });
  $(document).on("click", ".cross_button_to_close", function () {
    $("#help_modal").fadeOut("500");
  });
  $("#select_all_items").on("click", function () {
    if (
      $("#order_details_holder .single_order[data-selected=selected]").attr(
        "data-order-type"
      ) == "Dine In"
    ) {
      $("#items_holder_of_order .single_item_in_order").css(
        "background-color",
        "#B5D6F6"
      );
      $("#items_holder_of_order .single_item_in_order").attr(
        "data-selected",
        "selected"
      );
    } else {
      swal({
        title: "Alert",
        text: "No es necesario seleccionar o deseleccionar ningún artículo para llevar o entregar, porque hay que entregar todos los artículos en un paquete",
        confirmButtonColor: "#b6d6f6",
      });
    }
  });
  $(document).on("click", "#deselect_all_items", function () {
    if (
      $("#order_details_holder .single_order[data-selected=selected]").attr(
        "data-order-type"
      ) == "Dine In"
    ) {
      $("#items_holder_of_order .single_item_in_order").css(
        "background-color",
        "#ffffff"
      );
      $("#items_holder_of_order .single_item_in_order").attr(
        "data-selected",
        "deselected"
      );
    } else {
      swal({
        title: "Alert",
        text: "No es necesario seleccionar o deseleccionar ningún artículo para llevar o entregar, porque hay que entregar todos los artículos en un paquete",
        confirmButtonColor: "#b6d6f6",
      });
    }
  });
  $(document).on(
    "click",
    "#items_holder_of_order .single_item_in_order",
    function () {
      if (
        $(
          '#items_holder_of_order .single_item_in_order[data-order-type="Dine In"]'
        ).length > 0
      ) {
        // $('.single_item_in_order').css('background-color','#ffffff');
        // $('.single_item_in_order').attr('data-selected','unselected');
        if ($(this).attr("data-selected") == "selected") {
          $(this).css("background-color", "#ffffff");
          $(this).attr("data-selected", "unselected");
        } else {
          $(this).css("background-color", "#B5D6F6");
          $(this).attr("data-selected", "selected");
        }
      }
    }
  );
  $(document).on("click", "#start_cooking", function () {
    if (
      $("#order_details_holder .single_order[data-selected=selected]").attr(
        "data-order-type"
      ) == "Dine In"
    ) {
      if (
        $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).length > 0
      ) {
        // let previous_id = $('#items_holder_of_order .single_item_in_order[data-selected=selected]').attr('id').substr(12);
        let previous_id = "";
        let j = 1;
        let total_items = $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).length;
        $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).each(function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(12);
          } else {
            previous_id += $(this).attr("id").substr(12) + ",";
          }
          j++;
        });
        let previous_id_array = previous_id.split(",");

        previous_id_array.forEach(function (entry) {
          console.log('entry', entry);
          // CAMBIA AQUÍ EL SELECTOR:
          $("#detail_item_id_" + entry).attr("data-selected", "selected");
          $("#detail_item_id_" + entry).css("background-color", "#B5D6F6");
          // Si quieres actualizar el texto de estado en tiempo real:
          $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_in_preparation);
          $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("start_cooking_button");
          // $("#single_item_" + entry).attr("data-selected", "selected");
          // $("#single_item_" + entry).css("background-color", "#B5D6F6");
        });
        if (previous_id != "") {
          $.ajax({
            url: base_url + "Kitchen/update_cooking_status_ajax",
            method: "POST",
            data: {
              previous_id: previous_id,
              cooking_status: "Started Cooking",
              csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
              bgColorAdd();
              // swal({
              //   title: "Alert",
              //   text: "Cooking Started!!",
              //   confirmButtonColor: "#b6d6f6",
              // });
            },
            error: function () {
              alert("error");
            },
          });
        }
      } else {
        swal({
          title: "Alert!",
          text: "Por favor seleccione un artículo para cocinar!",
          confirmButtonColor: "#b6d6f6",
        });
      }
    } else {
      let previous_id = "";
      let j = 1;
      let total_items = $(
        "#items_holder_of_order .single_item_in_order"
      ).length;
      $("#items_holder_of_order .single_item_in_order").each(function (i, obj) {
        if (j == total_items) {
          previous_id += $(this).attr("id").substr(12);
        } else {
          previous_id += $(this).attr("id").substr(12) + ",";
        }
        j++;
      });
      let previous_id_array = previous_id.split(",");
      previous_id_array.forEach(function (entry) {
        // console.log('entry', entry);
        $("#detail_item_id_" + entry).attr("data-selected", "selected");
        $("#detail_item_id_" + entry).css("background-color", "#B5D6F6");
        // Si quieres actualizar el texto de estado en tiempo real:
        $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_in_preparation);
        $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("start_cooking_button");
        // $("#single_item_" + entry).attr("data-selected", "selected");
        // $("#single_item_" + entry).css("background-color", "#B5D6F6");
      });
      if (previous_id != "") {
        $.ajax({
          url:
            base_url + "Kitchen/update_cooking_status_delivery_take_away_ajax",
          method: "POST",
          data: {
            previous_id: previous_id,
            cooking_status: "Started Cooking",
            csrf_irestoraplus: csrf_value_,
              kitchen_id: kitchen_id,
          },
          success: function (response) {
            bgColorAdd();
            // swal({
            //   title: "Alert",
            //   text: "Cooking Started!!",
            //   confirmButtonColor: "#b6d6f6",
            // });
          },
          error: function () {
            alert("error");
          },
        });
      }
    }
  });
  $(document).on("click", "#cooking_done", function () {
    if (
      $("#order_details_holder .single_order[data-selected=selected]").attr(
        "data-order-type"
      ) == "Dine In"
    ) {
      if (
        $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).length > 0
      ) {
        // let previous_id = $('#items_holder_of_order .single_item_in_order[data-selected=selected]').attr('id').substr(12);
        let previous_id = "";
        let j = 1;
        let total_items = $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).length;
        $(
          "#items_holder_of_order .single_item_in_order[data-selected=selected]"
        ).each(function (i, obj) {
          if (j == total_items) {
            previous_id += $(this).attr("id").substr(12);
          } else {
            previous_id += $(this).attr("id").substr(12) + ",";
          }
          j++;
        });
        let previous_id_array = previous_id.split(",");
        previous_id_array.forEach(function (entry) {
          $("#detail_item_id_" + entry).attr("data-selected", "selected");
          $("#detail_item_id_" + entry).css("background-color", "#5DB745");
          // Opcional: cambia el texto de estado
          $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_ready);
          $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("done_cooking");
          $("#detail_item_id_" + entry + " .single_item_cooking_status").removeClass("start_cooking_button");
          // $("#single_item_" + entry).attr("data-selected", "selected");
          // $("#single_item_" + entry).css("background-color", "#B5D6F6");
        });
        if (previous_id != "") {
          $.ajax({
            url: base_url + "Kitchen/update_cooking_status_ajax",
            method: "POST",
            data: {
              previous_id: previous_id,
              cooking_status: "Done",
              csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
              // swal({
              //   title: "Alert",
              //   text: "Cooking Done!!",
              //   confirmButtonColor: "#b6d6f6",
              // });
            },
            error: function () {
              alert("error");
            },
          });
        }
      } else {
        swal({
          title: "Alert!",
          text: "Por favor seleccione un artículo para cocinar el artículo listo!",
          confirmButtonColor: "#b6d6f6",
        });
      }
    } else {
      let previous_id = "";
      let j = 1;
      let total_items = $(
        "#items_holder_of_order .single_item_in_order"
      ).length;
      $("#items_holder_of_order .single_item_in_order").each(function (i, obj) {
        if (j == total_items) {
          previous_id += $(this).attr("id").substr(12);
        } else {
          previous_id += $(this).attr("id").substr(12) + ",";
        }
        j++;
      });
      let previous_id_array = previous_id.split(",");
      previous_id_array.forEach(function (entry) {
        $("#detail_item_id_" + entry).attr("data-selected", "selected");
        $("#detail_item_id_" + entry).css("background-color", "#5DB745");
        // Opcional: cambia el texto de estado
        $("#detail_item_id_" + entry + " .single_item_cooking_status").html(text_ready);
        $("#detail_item_id_" + entry + " .single_item_cooking_status").addClass("done_cooking");
        $("#detail_item_id_" + entry + " .single_item_cooking_status").removeClass("start_cooking_button");
        // $("#single_item_" + entry).attr("data-selected", "selected");
        // $("#single_item_" + entry).css("background-color", "#B5D6F6");
      });
      if (previous_id != "") {
        // $.ajax({
        //   url:
        //     base_url + "Kitchen/update_cooking_status_delivery_take_away_ajax",
        //   method: "POST",
        //   data: {
        //     previous_id: previous_id,
        //     cooking_status: "Done",
        //     csrf_irestoraplus: csrf_value_,
        //       kitchen_id: kitchen_id,
        //   },
        //   success: function (response) {
        //     // swal({
        //     //   title: "Alert",
        //     //   text: "Cooking Done!!",
        //     //   confirmButtonColor: "#b6d6f6",
        //     // });
        //   },
        //   error: function () {
        //     alert("error");
        //   },
        // });
      }
    }
  });
  $(document).on("click", "#order_details_holder .single_order", function () {
    let sale_id = $(this).attr("id").substr(13);
    $("#order_details_holder .single_order").attr(
      "data-selected",
      "unselected"
    );
    $("#order_details_holder .single_order").css("background-color", "#ffffff");
    $(this).attr("data-selected", "selected");
    $(this).css("background-color", "#b6d6f6");
    $("#selected_order_for_refreshing_help").html(sale_id);
    $.ajax({
      url: base_url + "Kitchen/get_order_details_kitchen_ajax",
      method: "POST",
      data: {
        sale_id: sale_id,
        csrf_irestoraplus: csrf_value_,
          kitchen_id: kitchen_id,
      },
      success: function (response) {
        response = JSON.parse(response);

        let order_type = "";
        if (response.order_type == "1") {
          order_type = "Dine In";
        } else if (response.order_type == "2") {
          order_type = "Take Away";
        } else if (response.order_type == "3") {
          order_type = "Delivery";
        }
        let draw_table_for_order = "";

        for (let key in response.items) {
          //construct div
          let this_item = response.items[key];

          let selected_modifiers = "";
          let selected_modifiers_id = "";
          let selected_modifiers_price = "";
          let modifiers_price = 0;
          let total_modifier = this_item.modifiers.length;
          let i = 1;
          for (let mod_key in this_item.modifiers) {
            let this_modifier = this_item.modifiers[mod_key];
            //get selected modifiers
            if (i == total_modifier) {
              selected_modifiers += this_modifier.name;
              selected_modifiers_id += this_modifier.modifier_id;
              selected_modifiers_price += this_modifier.modifier_price;
              modifiers_price = (
                parseFloat(modifiers_price) +
                parseFloat(this_modifier.modifier_price)
              ).toFixed(2);
            } else {
              selected_modifiers += this_modifier.name + ",";
              selected_modifiers_id += this_modifier.modifier_id + ",";
              selected_modifiers_price += this_modifier.modifier_price + ",";
              modifiers_price = (
                parseFloat(modifiers_price) +
                parseFloat(this_modifier.modifier_price)
              ).toFixed(2);
            }
            i++;
          }
          let backgroundForSingleItem = "";
          if (this_item.cooking_status == "Done") {
            backgroundForSingleItem = 'style="background-color:#598527;"';
          } else if (this_item.cooking_status == "Started Cooking") {
            backgroundForSingleItem = 'style="background-color:#0c5889;"';
          }

          draw_table_for_order +=
            "<div " +
            backgroundForSingleItem +
            ' data-order-type="' +
            order_type +
            '" data-selected="unselected" class="single_item_in_order fix floatleft" id="single_item_' +
            this_item.previous_id +
            '">';
          draw_table_for_order +=
            '<h3 class="item_name">' + this_item.menu_name + "</h3>";
          draw_table_for_order +=
            '<p class="item_qty">'+quantity_ln+': ' + this_item.qty + "</p>";
          draw_table_for_order +=
            '<p class="modifier_name">'+modifiers_ln+': ' +
            selected_modifiers +
            "</p>";
          draw_table_for_order +=
            '<p class="note">'+note_ln+': ' + this_item.menu_note + "</p>";
          draw_table_for_order += "</div>";
        }
        //empty order detail segment
        $("#items_holder_of_order").empty();
        //add to top
        $("#items_holder_of_order").prepend(draw_table_for_order);
      },
      error: function () {
        alert("error");
      },
    });
  });

  //this is to set height when site load
  window.height_should_be =
    parseInt($(window).height()) - parseInt($(".top").height());
  $(".bottom_left").css("height", height_should_be + "px");
  $(".bottom_right").css("height", height_should_be + "px");
  //end

  $(document).on("click", "#notification_button", function () {
    // $('#notification_button').css('background-color','#F3F3F3');
    // $('#notification_button').css('color','buttontext');
    $("#notification_list_modal").fadeIn("500");
  });
  $(document).on("click", "#notification_close", function () {
    $("#notification_list_modal").fadeOut("500");
    $(".single_notification_checkbox").prop("checked", false);
    $("#select_all_notification").prop("checked", false);
  });
  $(document).on("click", "#notification_remove_all", function () {
    if ($(".single_notification_checkbox:checked").length > 0) {
      let r = confirm("Are you sure to delete all notifications?");
      if (r == false) {
        return false;
      }
      let notifications = "";
      let j = 1;
      let checkbox_length = $(".single_notification_checkbox:checked").length;
      $(".single_notification_checkbox:checked").each(function (i, obj) {
        if (j == checkbox_length) {
          notifications += $(this).val();
        } else {
          notifications += $(this).val() + ",";
        }
        j++;
      });
      if (notifications != "") {
        let notifications_array = notifications.split(",");
        notifications_array.forEach(function (entry) {
          $("#single_notification_row_" + entry).remove();
        });
        //Then read the values from the array where 0 is the first
        //Since we skipped the first element in the array, we start at 1
        $.ajax({
          url: base_url + "Kitchen/remove_multiple_notification_ajax",
          method: "POST",
          data: {
            notifications: notifications,
            csrf_irestoraplus: csrf_value_,
              kitchen_id: kitchen_id,
          },
          success: function (response) {
            // $('#single_notification_row_'+response).remove();
          },
          error: function () {
            alert("error");
          },
        });
      }
    } else {
      swal({
        title: "Alert",
        text: "No notification is selected",
        confirmButtonColor: "#b6d6f6",
      });
    }
  });
  $(document).on("click", ".single_serve_b", function () {
    let notification_id = $(this).attr("id").substr(26);
    $.ajax({
      url: base_url + "Kitchen/remove_notication_ajax",
      method: "POST",
      data: {
        notification_id: notification_id,
        csrf_irestoraplus: csrf_value_,
          kitchen_id: kitchen_id,
      },
      success: function (response) {
        $("#single_notification_row_" + response).remove();
      },
      error: function () {
        alert("error");
      },
    });
  });
  $(document).on("change", "#select_all_notification", function () {
    if ($(this).is(":checked")) {
      $(".single_notification_checkbox").prop("checked", true);
    } else {
      $(".single_notification_checkbox").prop("checked", false);
    }
  });


// ==================================================
    $(window).on("resize", function () {
        window.height_should_be =
            parseInt($(window).height()) - parseInt($(".top").height());
        $(".bottom_left").css("height", height_should_be + "px");
        $(".bottom_right").css("height", height_should_be + "px");
    });
// =============================================
    $(".all_order_holder")
        .slimscroll({
            height: "99.5%",
        })
        .parent()
        .css({
            background: "#f5f5f5",
            border: "0px solid #184055",
        });
    $("#items_holder_of_order")
        .slimscroll({
            height: "270px",
        })
        .parent()
        .css({
            background: "#f5f5f5",
            border: "0px solid #184055",
        });

    setInterval(function () {
        if ($("#refresh_it_or_not").html() == "Yes") {
            refresh_orders();
        }
        new_notification_interval();
    }, 15000);
    new_notification_interval();

    setInterval(function () {
        $("#order_details_holder .single_order").each(function (i, obj) {
            let order_id = $(this).attr("id").substr(13);
            let minutes = $("#ordered_minute_" + order_id).html();
            let seconds = $("#ordered_second_" + order_id).html();
            upTime($(this), minutes, seconds);
        });
    }, 1000);

    function upTime(object, minute, second) {
        let order_id = object.attr("id").substr(13);
        if (
            $("#ordered_minute_" + order_id).html() == "00" &&
            $("#ordered_second_" + order_id).html() == "00"
        ) {
            return false;
        }
        second++;
        if (second == 60) {
            minute++;
            second = 0;
        }

        minute = minute.toString();
        second = second.toString();
        minute = minute.length == 1 ? "0" + minute : minute;
        second = second.length == 1 ? "0" + second : second;
        $("#ordered_minute_" + order_id).html(minute);
        $("#ordered_second_" + order_id).html(second);

        // upTime2.to=setTimeout(function(){ upTime2(object,second,minute,hour); },1000);
    }

    function new_notification_interval() {
        $.ajax({
            url: base_url + "Kitchen/get_new_notifications_ajax",
            method: "POST",
            data: {
                csrf_irestoraplus: csrf_value_,
                kitchen_id: kitchen_id,
            },
            success: function (response) {
                response = JSON.parse(response);
                let notification_counter_update = response.length;
                let notification_counter_previous = $("#notification_counter").html();
                $("#notification_counter").html(notification_counter_update);
                if (notification_counter_update > notification_counter_previous) {
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#dc3545");
                        $("#notification_button").css("color", "#fff");
                    }, 500);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#ccc");
                        $("#notification_button").css("color", "buttontext");
                    }, 1000);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#dc3545");
                        $("#notification_button").css("color", "#fff");
                    }, 1500);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#ccc");
                        $("#notification_button").css("color", "buttontext");
                    }, 2000);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#dc3545");
                        $("#notification_button").css("color", "#fff");
                    }, 2500);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#ccc");
                        $("#notification_button").css("color", "buttontext");
                    }, 3000);
                    setTimeout(function () {
                        $("#notification_button").css("background-color", "#dc3545");
                        $("#notification_button").css("color", "#fff");
                    }, 3500);

                    let bell_new_order_notification = new Howl({
                        src: [base_url + "assets/media/kitchen_bell.mp3"],
                    });
                    bell_new_order_notification.play();
                }

                let notifications_list = "";
                for (let key in response) {
                    let this_notification = response[key];
                    notifications_list +=
                        '<div class="single_row_notification fix" id="single_notification_row_' +
                        this_notification.id +
                        '">';
                    notifications_list += '<div class="fix single_notification_check_box">';
                    notifications_list +=
                        '<input class="single_notification_checkbox" type="checkbox" id="single_notification_' +
                        this_notification.id +
                        '" value="' +
                        this_notification.id +
                        '">';
                    notifications_list += "</div>";
                    notifications_list +=
                        '<div class="fix single_notification">' +
                        this_notification.notification +
                        "</div>";
                    notifications_list += '<div class="single_serve_button">';
                    notifications_list +=
                        '<button class="single_serve_b bg-blue-btn btn" id="notification_serve_button_' +
                        this_notification.id +
                        '">Delete</button>';
                    notifications_list += "</div>";
                    notifications_list += "</div>";
                }
                $("#notification_list_holder").html(notifications_list);
            },
            error: function () {
                console.log("Notification refresh error");
            },
        });
    }

    refresh_orders();
    function bgColorAdd() {
      $(".header_portion").each(function() {
        let counter = 1;
        // Busca los .single_item_cooking_status dentro de los items de ESTA orden
        $(this)
          .closest(".single_order")
          .find(".single_item .single_item_cooking_status")
          .each(function() {
            let this_text = $(this).text().trim();
            if (this_text === text_in_preparation || this_text === text_in_preparation_ln) {
              counter++;
            }
          });
        // Si hay algún item "En Preparacion", pinta la cabecera
        if (counter != 1) {
          $(this).addClass("light-yellow-background");
        } else {
          $(this).removeClass("light-yellow-background");
        }
      });
    }

    function refresh_orders() {
      let url = base_url + "Kitchen/get_new_orders_ajax";
      $("#refresh_it_or_not").html("Yes");
  
      // ---- CONTROL DE IMPRESIONES PERSISTENTE ----
      const PRINTED_SALES_KEY = "kitchen_printed_sales";
      const PRINTED_SALES_MAX_AGE_MS = 20 * 60 * 60 * 1000; // 20 horas
  
      if (typeof window.printed_sales === "undefined") {
          window.printed_sales = loadPrintedSales();
          window.is_first_load = true;
      }
  
      function loadPrintedSales() {
          let obj = {};
          try {
              let raw = window.localStorage.getItem(PRINTED_SALES_KEY);
              if (raw) {
                  let data = JSON.parse(raw);
                  let now = Date.now();
                  for (let sale_no in data) {
                      if (now - data[sale_no].time <= PRINTED_SALES_MAX_AGE_MS) {
                          obj[sale_no] = data[sale_no];
                      }
                  }
              }
          } catch (e) { }
          return obj;
      }
  
      function savePrintedSales(obj) {
          try {
              window.localStorage.setItem(PRINTED_SALES_KEY, JSON.stringify(obj));
          } catch (e) { }
      }
  
      // function resetPrintedSales() {
      //     window.localStorage.removeItem(PRINTED_SALES_KEY);
      // }
  
      // ---- FIN CONTROL DE IMPRESIONES PERSISTENTE ----
  
      $.ajax({
          url: url,
          method: "POST",
          data: {
              outlet_id: window.localStorage.getItem("ls_outlet_id"),
              csrf_irestoraplus: csrf_value_,
              kitchen_id: kitchen_id,
          },
          success: function (response) {
              window.order_items = [];
              response = JSON.parse(response);
              window.last_orders = response;
  
              let current_sales = {};
              let current_info = {};
              let now = Date.now();
              for (let key in response) {
                  let sale_no = response[key].sale_no;
                  // Serializa SOLO los items con sus campos relevantes
                  current_sales[sale_no] = JSON.stringify(response[key].items.map(item => ({
                      id: item.previous_id || item.id || item.sales_details_id,
                      tmp_qty: item.tmp_qty,
                      qty: item.qty
                  })));
                  current_info[sale_no] = JSON.stringify(response[key]);
              }
  
              if (window.is_first_load) {
                  for (let sale_no in current_sales) {
                      window.printed_sales[sale_no] = {
                          hash: current_sales[sale_no],
                          time: now
                      };
                  }
                  savePrintedSales(window.printed_sales);
                  window.is_first_load = false;
              } else {
                  for (let sale_no in current_sales) {
                      let sale = window.printed_sales[sale_no];
  
                      if (typeof sale === "undefined") {
                          printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 1);
                          window.printed_sales[sale_no] = {
                              hash: current_sales[sale_no],
                              time: now
                          };
                          savePrintedSales(window.printed_sales);
                      } else if (sale.hash !== current_sales[sale_no]) {
                          // --- Comparador robusto de items ---
                          const oldItems = JSON.parse(sale.hash);
                          const newItems = JSON.parse(current_sales[sale_no]);
                          let hasRelevantChanges = false;
  
                          // 1. Hay un item nuevo o qty cambió
                          for (let i = 0; i < newItems.length; i++) {
                              const newItem = newItems[i];
                              const oldItem = oldItems.find(item => item.id == newItem.id);
                              if (!oldItem) {
                                  hasRelevantChanges = true; // Nuevo item
                                  break;
                              }
                              if (parseFloat(newItem.tmp_qty) > 0 || (parseFloat(newItem.tmp_qty) !== parseFloat(oldItem.tmp_qty))) {
                                  hasRelevantChanges = true; // Cambio qty
                                  break;
                              }
                          }
                          // 2. Algún item fue eliminado
                          if (!hasRelevantChanges) {
                              for (let i = 0; i < oldItems.length; i++) {
                                  const oldItem = oldItems[i];
                                  const newItem = newItems.find(item => item.id == oldItem.id);
                                  if (!newItem) {
                                      hasRelevantChanges = true;
                                      break;
                                  }
                              }
                          }
  
                          if (hasRelevantChanges) {
                              // printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 0);
                              
                              // Comparar items por ID para saber cuáles son realmente nuevos
                              const oldItems = JSON.parse(sale.hash);
                              const newItems = JSON.parse(current_sales[sale_no]);
                              const allOrderItems = JSON.parse(current_info[sale_no]).items;

                              // Detectar ids de items nuevos
                              const newItemIds = newItems
                                  .filter(newItem => !oldItems.some(oldItem => oldItem.id == newItem.id))
                                  .map(item => item.id);

                              // Filtrar los items nuevos por ID
                              const onlyNewItems = allOrderItems.filter(item =>
                                  newItemIds.includes(item.previous_id || item.id || item.sales_details_id)
                              );

                              if (onlyNewItems.length > 0) {
                                  const orderInfoSelected = {...JSON.parse(current_info[sale_no]), items: onlyNewItems};
                                  printDirectlyFromOrderData(orderInfoSelected, kitchen_id, 0);
                              } else {
                                  printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 0);
                              }
                              window.printed_sales[sale_no] = {
                                  hash: current_sales[sale_no],
                                  time: now
                              };
                              savePrintedSales(window.printed_sales);
                          }
                      }
                  }
  
                  // Si NO quieres borrar los tickets impresos aunque el backend ya no los mande:
                  // (esto previene que reimprima como "nuevo" si vuelve el ticket)
                  /*
                  for (let sale_no in window.printed_sales) {
                      if (!(sale_no in current_sales)) {
                          delete window.printed_sales[sale_no];
                      }
                  }
                  savePrintedSales(window.printed_sales);
                  */
              }
              // --- Fin control impresión ---
  
              // Render de la UI de pedidos
              let order_list_left = "";
              let i = 1;
              for (let key in response) {
                  let items_tmp = response[key].items;
                  if (items_tmp.length) {
                      let order_name = "";
                      let order_type = "";
                      if (response[key].order_type == "1") {
                          order_name = response[key].sale_no;
                          order_type = dine_ln;
                      } else if (response[key].order_type == "2") {
                          order_name = response[key].sale_no;
                          order_type = take_away_ln;
                      } else if (response[key].order_type == "3") {
                          order_name = response[key].sale_no;
                          order_type = delivery_ln;
                      }
                      if (Number(response[key].is_kitchen_bell) == 1) {
                          let bell_new_order_notification_1 = new Howl({
                              src: [base_url + "assets/media/kitchen_bell.mp3"],
                          });
                          bell_new_order_notification_1.play();
                      }
                      let tables_booked = response[key].orders_table_text;
  
                      let selected_unselected =
                          $("#selected_order_for_refreshing_help").html() ==
                          response[key].sales_id
                              ? "selected"
                              : "unselected";
                      let selected_background =
                          $("#selected_order_for_refreshing_help").html() ==
                          response[key].sales_id
                              ? ' style="background-color:#b6d6f6" '
                              : "";
                      let width = 100;
                      let total_kitchen_type_items = response[key].total_kitchen_type_items;
                      let total_kitchen_type_started_cooking_items =
                          response[key].total_kitchen_type_started_cooking_items;
                      let total_kitchen_type_done_items =
                          response[key].total_kitchen_type_done_items;
                      let splitted_width = (
                          parseFloat(width) / parseFloat(total_kitchen_type_items)
                      ).toFixed(2);
                      let percentage_for_started_cooking = (
                          parseFloat(splitted_width) *
                          parseFloat(total_kitchen_type_started_cooking_items)
                      ).toFixed(2);
                      let percentage_for_done_cooking = (
                          parseFloat(splitted_width) * parseFloat(total_kitchen_type_done_items)
                      ).toFixed(2);
  
                      let comanda_name =
                          response[key].number_slot_name != null ? response[key].number_slot_name : "";
                      let usuario_name =
                          response[key].full_name != null ? response[key].full_name : "";
                      let table_name =
                          response[key].table_name != null ? response[key].table_name : "";
                      let waiter_name =
                          response[key].waiter_name != null ? response[key].waiter_name : "";
                      let customer_name =
                          response[key].customer_name != null
                              ? response[key].customer_name
                              : "";
  
                      let booked_time = new Date(Date.parse(response[key].date_time));
                      let now2 = new Date();
  
                      let diffMs = now2 - booked_time;
                      let days = parseInt(diffMs / (1000 * 60 * 60 * 24));
                      let hours = parseInt((diffMs / (1000 * 60 * 60)) % 24);
                      let totalMinutes = parseInt(diffMs / (1000 * 60));
                      let totalSeconds = parseInt(diffMs / 1000);
                      let minute = parseInt((diffMs / (1000 * 60)) % 60);
                      let second = parseInt((diffMs / (1000) % 60));
                      minute = minute.toString().padStart(2, "0");
                      second = second.toString().padStart(2, "0");
  
                      let table_name_txt = '';
                      if (tables_booked > 0) {
                          table_name_txt = table + ': ' + tables_booked;
                      }
                      if (total_kitchen_type_items != total_kitchen_type_done_items) {
                          order_list_left +=
                              '<div class="fix floatleft single_order" data-order-type="' +
                              order_type +
                              '" data-selected="' +
                              selected_unselected +
                              '" id="single_order_' +
                              response[key].sales_id +
                              '">';
                          order_list_left +=
                              '<div class="header_portion light-blue-background fix">';
                          order_list_left += '<div class="fix floatleft" style="width:70%;">';
                          order_list_left += '<p class="order_number table_no"><b>#' + comanda_name + '</b> - ' + order_name + '</p>';
                          order_list_left += '<p class="order_number sale_no"> ' + response[key].waiter_name + "</p> ";
                          order_list_left += '<p class="order_number customer_name">' + response[key].customer_name + '</p>';
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix floatleft" style="width:30%;">';
                          order_list_left += '<p class="order_duration  order_number order_type"><span>' + order_type + '</span></p>';
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix floatleft" style="width:30%;">';
                          order_list_left += '<p class="order_duration "><span id="kitchen_time_minute_' +
                              response[key].sales_id +
                              '">' +
                              response[key].minutos +
                              '</span><span id="kitchen_time_second_' +
                              response[key].sales_id +
                              '">' +
                              "</span></p>";
                          order_list_left += "</div>";
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix items_holder">';
  
                          // Renderiza TODOS los items (no deduplicar)
                          let items = response[key].items;
                          let i_counter = 0;
                          order_list_left += '<div class="single_el_wrapper"><div class="el_wrapper"><label for="all_select_all_of_an_order_' + response[key].sales_id + '" id="select_all_of_an_order_' + response[key].sales_id + '" class="select_all_of_an_order"><input id="all_select_all_of_an_order_' + response[key].sales_id + '" type="checkbox"><span>Select. Todo</span></label></div></div>'
                          for (let key_item in items) {
                              let single_item = items[key_item];
                              window.order_items.push(single_item);
  
                              let alternative_name = single_item.alternative_name ? " (" + single_item.alternative_name + ")" : '';
                              let item_background = "";
                              let font_style = "color: #212121";
                              let cook_status_btn = '';
                              let cooking_status = text_not_ready_ln;
                              if (single_item.cooking_status == "Done") {
                                  cooking_status = text_ready_ln;
                                  cook_status_btn = 'done_cooking';
                              } else if (single_item.cooking_status == "Started Cooking") {
                                  cooking_status = text_in_preparation_ln;
                                  cook_status_btn = 'start_cooking_button';
                              }
                              let qty_str = quantity_ln + ': ' + single_item.qty;
                              if (single_item.qty != single_item.tmp_qty && parseFloat(single_item.tmp_qty)) {
                                  qty_str = Qty_Old + ': ' + (single_item.qty - single_item.tmp_qty) + " | " + Qty_New + ': ' + single_item.tmp_qty;
                              }
                              let status_class = "";
                              if (single_item.cooking_status == "Done") status_class = "status-done";
                              else if (single_item.cooking_status == "Started Cooking") status_class = "status-started";
                              else status_class = "status-new";
                              // NUEVO BLOQUE HTML PARA ITEM
                              order_list_left +=
                                  '<div data-selected="unselected" class="fix single_item ' + status_class + ' ' + item_background +
                                  '" data-order-id="' + response[key].sales_id +
                                  '" data-item-id="' + single_item.previous_id +
                                  '" data-item-uid="' + (single_item.previous_id || single_item.id || single_item.sales_details_id) +
                                  '" id="detail_item_id_' + single_item.previous_id +
                                  '" data-cooking-status="' + single_item.cooking_status + '">' +
                                  '<div class="single_item_content">' +
                                  '<div class="item_name_row">' +
                                  '<label><input type="checkbox" class="select_single_item">' +
                                  '<span class="item_name" style="' + font_style + '">' + single_item.menu_name + alternative_name + '</span></label>' +
                                  '</div>' +
                                  '<div class="item_details_row">' +
                                  '<span class="item_qty" style="font-weight:bold;' + font_style + '">' + qty_str + '</span>' +
                                  '<span class="single_item_cooking_status ' + cook_status_btn + '" style="' + font_style + '">' + cooking_status + '</span>' +
                                  '</div>';
  
                              // Modificadores y notas (opcional)
                              let modifiers = single_item.modifiers;
                              let modifiers_length = modifiers.length;
                              if (modifiers_length > 0) {
                                  let modifiers_name = "";
                                  for (let m = 0; m < modifiers_length; m++) {
                                      modifiers_name += modifiers[m].name + (m < modifiers_length - 1 ? ", " : "");
                                  }
                                  order_list_left +=
                                      '<div class="item_modifiers_row" style="' + font_style + '"><small>- ' +
                                      modifiers_name + '</small></div>';
                              }
  
                              if (single_item.menu_note && single_item.menu_note != "" && single_item.menu_note != undefined && single_item.menu_note != "undefined") {
                                  order_list_left +=
                                      '<div class="item_note_row" style="' + font_style + '"><small><b>Nota: </b> ' +
                                      single_item.menu_note + '</small></div>';
                              }
                              order_list_left += '</div>'; // single_item_content
                              order_list_left += '</div>'; // single_item
  
                              if ((items.length - 1) > i_counter) {
                                  order_list_left += "<hr class='hr_kitchen_panel'>";
                              }
                              i_counter++;
                          }
  
                          order_list_left += "</div>";
                          order_list_left +=
                              '<div class="single_order_button_holder" id="single_order_button_holder_' +
                              response[key].sales_id +
                              '">';
                          order_list_left +=
                              '<button class="start_cooking_button cook_bg" id="start_cooking_button_' +
                              response[key].sales_id +
                              '">Cocinar</button>' +
                              '<button class="done_cooking" id="done_cooking_' +
                              response[key].sales_id +
                              '">Terminar</button>' +
                              '<button class="print_kitchen_ticket" onclick="printKitchenKOTBySaleId(' + "'" +
                              response[key].sale_no + "'" + ', ' + kitchen_id + ')" id="print_kitchen_ticket_' +
                              response[key].sales_id +
                              '">Imprimir</button>';
  
                          order_list_left += "</div>";
                          order_list_left += "</div>";
                      }
                      i++;
                  }
              }
              $("#order_holder").html(order_list_left);
              bgColorAdd();
              $("#order_holder.order_holder .single_order .items_holder")
                  .slimscroll({
                      height: "270px",
                  })
                  .parent()
                  .css({
                      background: "#fff",
                      border: "0px solid #184055",
                  });
  
              let group_order_by_item_option =
                  '<select id="group_by_order_item" class="group_by_order_item">';
              group_order_by_item_option += '<option value="">Select Item</option>';
              for (let key in window.order_items) {
                  let single_ordered_item = window.order_items[key];
                  group_order_by_item_option +=
                      '<option value="' +
                      single_ordered_item.food_menu_id +
                      '">' +
                      single_ordered_item.menu_name +
                      "</option>";
              }
              group_order_by_item_option += "</select>";
  
              $("#group_by_order_item_holder").html(group_order_by_item_option);
              $("#group_by_order_item").select2({ dropdownCssClass: "bigdrop" });
          },
          error: function () {
              console.log("New order refresh error");
          },
      });
  
      // material icon init
      $(".select2").select2();
      $.datable();
  
      function searchItems(searchedValue) {
          let resultObject = search(searchedValue, window.order_items);
          return resultObject;
      }
  
      function search(nameKey, myArray) {
          let foundResult = [];
          for (let i = 0; i < myArray.length; i++) {
              if (myArray[i].menu_name.toLowerCase().includes(nameKey.toLowerCase())) {
                  foundResult.push(myArray[i]);
              }
          }
          return foundResult.sort(function (a, b) {
              return parseInt(b.sold_for) - parseInt(a.sold_for);
          });
      }
  }

    function refresh_ordersOld() {
      let url = base_url + "Kitchen/get_new_orders_ajax";
      $("#refresh_it_or_not").html("Yes");
  
      // Control de impresiones en memoria (solo vive mientras no recargues)
      if (typeof window.printed_sales === "undefined") {
          window.printed_sales = {};
          window.is_first_load = true;
      }
  
      $.ajax({
          url: url,
          method: "POST",
          data: {
              outlet_id: window.localStorage.getItem("ls_outlet_id"),
              csrf_irestoraplus: csrf_value_,
              kitchen_id: kitchen_id,
          },
          success: function (response) {
              window.order_items = [];
              response = JSON.parse(response);
              window.last_orders = response;
  
              // --- Control de impresión ---
              // 1. En la primera carga, solo registra los tickets actuales (NO imprime)
              // 2. En siguientes cargas, imprime solo los nuevos o modificados
  
              // Recolecta los sale_no actuales y su hash (puedes ajustar el hash según lo que consideres "cambio")
              let current_sales = {};
              let current_info = {};
              for (let key in response) {
                  let sale_no = response[key].sale_no;
                  // Puedes usar solo los items, o todo el objeto según tu lógica
                  current_sales[sale_no] = JSON.stringify(response[key].items);
                  current_info[sale_no] = JSON.stringify(response[key]);
              }
  
              // Primera carga: solo registra, NO imprime
              if (window.is_first_load) {
                window.printed_sales = { ...current_sales };
                window.is_first_load = false;
              } else {
                  // Siguientes cargas: imprime los nuevos y modificados
                  for (let sale_no in current_sales) {
                      // // console.log(sale_no);
                      // // console.log(current_sales[sale_no]);
                      // // console.log(current_info[sale_no]);
                      // if (typeof window.printed_sales[sale_no] === "undefined") {
                      //     // NUEVO pedido
                      //     // console.log('NUEVO pedido');
                      //     // console.log(current_info[sale_no]);
                      //     // fetchAndPrint(sale_no, kitchen_id, "1");
                      //     printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 1)
                      //     window.printed_sales[sale_no] = current_sales[sale_no];
                      // } else if (window.printed_sales[sale_no] !== current_sales[sale_no]) {
                      //     // MODIFICADO
                      //   // console.log('MODIFICADO');
                      //   // console.log(current_info[sale_no]);
                      //     // fetchAndPrint(sale_no, kitchen_id, "0");
                      //     printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 0);
                      //     window.printed_sales[sale_no] = current_sales[sale_no];
                      // }
                      // // Si no cambió, no imprime

                      if (typeof window.printed_sales[sale_no] === "undefined") {
                          // NUEVO pedido
                          printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 1);
                          window.printed_sales[sale_no] = current_sales[sale_no];
                          // console.log('Nuevo pedido',current_sales[sale_no]);
                      } else if (window.printed_sales[sale_no] !== current_sales[sale_no]) {
                          // MODIFICADO - pero verificamos si hay cambios relevantes (tmp_qty > 0)
                          const oldItems = JSON.parse(window.printed_sales[sale_no]);
                          const newItems = JSON.parse(current_sales[sale_no]);
                          
                          let hasRelevantChanges = false;
                          
                          // Comparar item por item
                          if (oldItems.length === newItems.length) {
                              for (let i = 0; i < newItems.length; i++) {
                                  const newItem = newItems[i];
                                  const oldItem = oldItems.find(item => item.id === newItem.id);
                                  
                                  // Si encontramos un item con tmp_qty > 0, es un cambio relevante
                                  if (oldItem && (parseFloat(newItem.tmp_qty) > 0 || 
                                                (parseFloat(newItem.tmp_qty) !== parseFloat(oldItem.tmp_qty)))) {
                                      hasRelevantChanges = true;
                                      break;
                                  }
                              }
                          } else {
                              // Si la cantidad de items cambió, es un cambio relevante
                              hasRelevantChanges = true;
                          }
                          
                          if (hasRelevantChanges) {
                              printDirectlyFromOrderData(JSON.parse(current_info[sale_no]), kitchen_id, 0);
                              window.printed_sales[sale_no] = current_sales[sale_no];
                          }
                      }



                  }
                  // // Limpia los pedidos que ya no existen
                  // for (let sale_no in window.printed_sales) {
                  //     if (!(sale_no in current_sales)) {
                  //         delete window.printed_sales[sale_no];
                  //     }
                  // }
              }
              // --- Fin control de impresión ---
  
              // Render de la UI de pedidos
              let order_list_left = "";
              let i = 1;
              for (let key in response) {
                  let items_tmp = response[key].items;
                  if (items_tmp.length) {
                      let order_name = "";
                      let order_type = "";
                      if (response[key].order_type == "1") {
                          order_name = response[key].sale_no;
                          order_type = dine_ln;
                      } else if (response[key].order_type == "2") {
                          order_name = response[key].sale_no;
                          order_type = take_away_ln;
                      } else if (response[key].order_type == "3") {
                          order_name = response[key].sale_no;
                          order_type = delivery_ln;
                      }
                      // Toca campana si hay pedido nuevo
                      if (Number(response[key].is_kitchen_bell) == 1) {
                          let bell_new_order_notification_1 = new Howl({
                              src: [base_url + "assets/media/kitchen_bell.mp3"],
                          });
                          bell_new_order_notification_1.play();
                      }
                      let tables_booked = response[key].orders_table_text;
  
                      let selected_unselected =
                          $("#selected_order_for_refreshing_help").html() ==
                          response[key].sales_id
                              ? "selected"
                              : "unselected";
                      let selected_background =
                          $("#selected_order_for_refreshing_help").html() ==
                          response[key].sales_id
                              ? ' style="background-color:#b6d6f6" '
                              : "";
                      let width = 100;
                      let total_kitchen_type_items = response[key].total_kitchen_type_items;
                      let total_kitchen_type_started_cooking_items =
                          response[key].total_kitchen_type_started_cooking_items;
                      let total_kitchen_type_done_items =
                          response[key].total_kitchen_type_done_items;
                      let splitted_width = (
                          parseFloat(width) / parseFloat(total_kitchen_type_items)
                      ).toFixed(2);
                      let percentage_for_started_cooking = (
                          parseFloat(splitted_width) *
                          parseFloat(total_kitchen_type_started_cooking_items)
                      ).toFixed(2);
                      let percentage_for_done_cooking = (
                          parseFloat(splitted_width) * parseFloat(total_kitchen_type_done_items)
                      ).toFixed(2);
  
                      let comanda_name =
                          response[key].number_slot_name != null ? response[key].number_slot_name : "";
                      let usuario_name =
                          response[key].full_name != null ? response[key].full_name : "";
                      let table_name =
                          response[key].table_name != null ? response[key].table_name : "";
                      let waiter_name =
                          response[key].waiter_name != null ? response[key].waiter_name : "";
                      let customer_name =
                          response[key].customer_name != null
                              ? response[key].customer_name
                              : "";
                      // let booked_time = new Date(Date.parse(response[key].date_time));
                      // let now = new Date();
  
                      // let days = parseInt((now - booked_time) / (1000 * 60 * 60 * 24));
                      // let hours = parseInt(
                      //     (Math.abs(now - booked_time) / (1000 * 60 * 60)) % 24
                      // );
                      // let minute = parseInt(
                      //     (Math.abs(now.getTime() - booked_time.getTime()) / (1000 * 60)) % 60
                      // );
                      // let second = parseInt(
                      //     (Math.abs(now.getTime() - booked_time.getTime()) / 1000) % 60
                      // );
                      // minute = minute.toString();
                      // second = second.toString();
                      // minute = minute.length == 1 ? "0" + minute : minute;
                      // second = second.length == 1 ? "0" + second : second;

                      let booked_time = new Date(Date.parse(response[key].date_time));
                      let now = new Date();

                      // Diferencia total en milisegundos
                      let diffMs = now - booked_time;

                      // Días completos (opcional, si los necesitas)
                      let days = parseInt(diffMs / (1000 * 60 * 60 * 24));

                      // Horas completas (opcional, si las necesitas)
                      let hours = parseInt((diffMs / (1000 * 60 * 60)) % 24);

                      // Total de minutos transcurridos (sin usar % 60)
                      let totalMinutes = parseInt(diffMs / (1000 * 60));

                      // Total de segundos transcurridos (sin usar % 60)
                      let totalSeconds = parseInt(diffMs / 1000);

                      // Si quieres los minutos "restantes" después de horas (opcional, como antes)
                      let minute = parseInt((diffMs / (1000 * 60)) % 60);

                      // Si quieres los segundos "restantes" después de minutos (opcional, como antes)
                      let second = parseInt((diffMs / 1000) % 60);

                      // Formateo a dos dígitos (opcional)
                      minute = minute.toString().padStart(2, "0");
                      second = second.toString().padStart(2, "0");

                      // Si necesitas el tiempo total en minutos y segundos (sin descomponer en horas/días)
                      // console.log(`Tiempo transcurrido: ${totalMinutes} minutos (${totalSeconds} segundos)`);

                      // Si necesitas el formato anterior (días, horas, minutos, segundos)
                      // console.log(`Formato desglosado: ${days}d ${hours}h ${minute}m ${second}s`);


  
                      let table_name_txt = '';
                      if (tables_booked > 0){
                          table_name_txt = table +': ' + tables_booked;
                      }
                      if (total_kitchen_type_items != total_kitchen_type_done_items) {
                          order_list_left +=
                              '<div class="fix floatleft single_order" data-order-type="' +
                              order_type +
                              '" data-selected="' +
                              selected_unselected +
                              '" id="single_order_' +
                              response[key].sales_id +
                              '">';
                          order_list_left +=
                              '<div class="header_portion light-blue-background fix">';
                          order_list_left += '<div class="fix floatleft" style="width:70%;">';
                          order_list_left +='<p class="order_number table_no"><b>#' + comanda_name + '</b> - ' + order_name + '</p>';
                          order_list_left += '<p class="order_number sale_no"> ' + response[key].waiter_name +  "</p> ";
                          order_list_left += '<p class="order_number customer_name">'+response[key].customer_name+'</p>';
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix floatleft" style="width:30%;">';
                          order_list_left += '<p class="order_duration  order_number order_type"><span>'+order_type+'</span></p>';
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix floatleft" style="width:30%;">';
                          order_list_left += '<p class="order_duration "><span id="kitchen_time_minute_' +
                              response[key].sales_id +
                              '">' +
                              totalMinutes +
                              '</span>:<span id="kitchen_time_second_' +
                              response[key].sales_id +
                              '">' +
                              second +
                              "</span></p>";
                          order_list_left += "</div>";
                          order_list_left += "</div>";
                          order_list_left += '<div class="fix items_holder">';
                          let items = response[key].items;
                          let i_counter = 0;
                          order_list_left += '<div class="single_el_wrapper"><div class="el_wrapper"><label for="all_select_all_of_an_order_' + response[key].sales_id +'" id="select_all_of_an_order_' + response[key].sales_id +'" class="select_all_of_an_order"><input id="all_select_all_of_an_order_' + response[key].sales_id +'" type="checkbox"><span>Select. Todo</span></label></div></div>'
  
                          for (let key_item in items) {
  
                              let single_item = items[key_item];
                              let searched_found = searchItems(single_item.menu_name);
                              if (searched_found.length == 0) {
                                  window.order_items.push(single_item);
                              }
  
                              let alternative_name = single_item.alternative_name?" ("+single_item.alternative_name+")":'';
                              let item_background = "";
                              let font_style = "color: #212121";
                              let cook_status_btn = '';
                              let cooking_status = text_not_ready_ln;
                              if (single_item.cooking_status == "Done") {
                                  cooking_status = text_ready_ln;
                                  cook_status_btn = 'done_cooking';
                              } else if (single_item.cooking_status == "Started Cooking") {
                                  cooking_status = text_in_preparation_ln;
                                  cook_status_btn = 'start_cooking_button';
                              }
  
                              let qty_str = '<p class="item_qty" style="font-weight:bold; ' +
                              font_style +
                              '">'+quantity_ln+': ' +
                              single_item.qty +
                              "</p>";
  
                                if(single_item.qty!=single_item.tmp_qty && parseFloat(single_item.tmp_qty)){
  
                                  qty_str = '<p class="item_qty" style="font-weight:bold; ' +
                                          font_style +
                                          '">'+Qty_Old+': ' +
                                          (single_item.qty - single_item.tmp_qty) +
                                          "</p>";
                                  qty_str += '<p class="item_qty" style="font-weight:bold; ' +
                                          font_style +
                                          '">'+Qty_New+': ' +
                                          single_item.tmp_qty +
                                          "</p>";
  
                                }
                             
                              order_list_left +=
                                  '<div data-selected="unselected" class="fix single_item ' +
                                  item_background +
                                  '" data-order-id="' +
                                  response[key].sales_id +
                                  '" data-item-id="' +
                                  single_item.previous_id +
                                  '" id="detail_item_id_' +
                                  single_item.previous_id +
                                  '" data-cooking-status="' +
                                  single_item.cooking_status +
                                  '">';
                              order_list_left += '<div class="single_item_left_side fix">';
                              order_list_left += '<div class="fix floatleft item_quantity">';
                              order_list_left +=
                                  '<p class="item_quanity_text" style="' +
                                  font_style +
                                  '">' +
                                  single_item.qty +
                                  "</p>";
                              order_list_left += "</div>";
                              order_list_left += '<div class="fix floatleft item_detail"><div><label ><input type="checkbox" class="select_single_item">';
                              order_list_left +=
                                  '<span class="item_name" style="' +
                                  font_style +
                                  '">' +
                                  single_item.menu_name +
                                  "</span></label>"+alternative_name+"</div>";
                              if (single_item.menu_combo_items != "" && single_item.menu_combo_items!=undefined && single_item.menu_combo_items!="undefined") {
                                  order_list_left +=
                                      '<p class="note" style="' +
                                      font_style +
                                      '">Items- ' +
                                      single_item.menu_combo_items +
                                      "</p>";
                              }
                              order_list_left += qty_str;
  
                              let modifiers = single_item.modifiers;
                              let modifiers_length = modifiers.length;
                              let w = 1;
                              let modifiers_name = "";
                              for (let key_modifier in modifiers) {
                                  if (w == modifiers_length) {
                                      modifiers_name += modifiers[key_modifier].name;
                                  } else {
                                      modifiers_name += modifiers[key_modifier].name + ", ";
                                  }
                                  w++;
                              }
                              if (modifiers_length > 0) {
                                  order_list_left +=
                                      '<p class="modifiers" style="' +
                                      font_style +
                                      '">- ' +
                                      modifiers_name +
                                      "</p>";
                              }
  
                              if (single_item.menu_note != "" && single_item.menu_note!=undefined && single_item.menu_note!="undefined") {
                                  order_list_left +=
                                      '<p class="note" style="' +
                                      font_style +
                                      '">- ' +
                                      single_item.menu_note +
                                      "</p>";
                              }
  
                              order_list_left += "</div>";
                              order_list_left += "</div>";
                              order_list_left += '<div class="single_item_right_side fix">';
                              order_list_left +=
                                  '<p class="single_item_cooking_status '+cook_status_btn+'" style="' +
                                  font_style +
                                  ';">' +
                                  cooking_status +
                                  "</p>";
                              order_list_left += "</div>";
                              order_list_left += "</div>";
                              if((items.length-1)>i_counter){
                                 order_list_left += "<hr class='hr_kitchen_panel'>";
                              }
  
                              i_counter++;
                          }
  
                          order_list_left += "</div>";
                          order_list_left +=
                              '<div class="single_order_button_holder" id="single_order_button_holder_' +
                              response[key].sales_id +
                              '">';
                          order_list_left +=
                            '<button class="start_cooking_button cook_bg" id="start_cooking_button_' +
                            response[key].sales_id +
                            '">Cocinar</button>' +
                            '<button class="done_cooking" id="done_cooking_' +
                            response[key].sales_id +
                            '">Terminar</button>' +
  
                            '<button class="print_kitchen_ticket" onclick="printKitchenKOTBySaleId(' + "'" +
                            response[key].sale_no + "'" + ', ' + kitchen_id + ')" id="print_kitchen_ticket_' +
                            response[key].sales_id +
                            '">Imprimir</button>';
  
                          order_list_left += "</div>";
                          order_list_left += "</div>";
                      }
                      i++;
                  }
              }
              $("#order_holder").html(order_list_left);
              bgColorAdd();
              $("#order_holder.order_holder .single_order .items_holder")
                  .slimscroll({
                      height: "270px",
                  })
                  .parent()
                  .css({
                      background: "#fff",
                      border: "0px solid #184055",
                  });
  
              let group_order_by_item_option =
                  '<select id="group_by_order_item" class="group_by_order_item">';
              group_order_by_item_option += '<option value="">Select Item</option>';
              for (let key in window.order_items) {
                  let single_ordered_item = window.order_items[key];
                  group_order_by_item_option +=
                      '<option value="' +
                      single_ordered_item.food_menu_id +
                      '">' +
                      single_ordered_item.menu_name +
                      "</option>";
              }
              group_order_by_item_option += "</select>";
  
              $("#group_by_order_item_holder").html(group_order_by_item_option);
              $("#group_by_order_item").select2({ dropdownCssClass: "bigdrop" });
          },
          error: function () {
              console.log("New order refresh error");
          },
      });
  
      // material icon init
      $(".select2").select2();
      $.datable();
  
      function searchItems(searchedValue) {
          let resultObject = search(searchedValue, window.order_items);
          return resultObject;
      }
  
      function search(nameKey, myArray) {
          let foundResult = new Array();
          for (let i = 0; i < myArray.length; i++) {
              if (myArray[i].menu_name.toLowerCase().includes(nameKey.toLowerCase())) {
                  foundResult.push(myArray[i]);
              }
          }
          return foundResult.sort(function (a, b) {
              return parseInt(b.sold_for) - parseInt(a.sold_for);
          });
      }
  }

    function toggleFullscreen(elem) {
      elem = elem || document.documentElement;
      if (
        !document.fullscreenElement &&
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement &&
        !document.msFullscreenElement
      ) {
        if (elem.requestFullscreen) {
          elem.requestFullscreen();
        } else if (elem.msRequestFullscreen) {
          elem.msRequestFullscreen();
        } else if (elem.mozRequestFullScreen) {
          elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
          elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        }
      }
    }
    
    $(document).on("click", ".fullscreen", function (e) {
      toggleFullscreen();
      $(this).attr("data-tippy-content", "");
  
      if ($(this).find("i").hasClass("fa-expand-arrows-alt")) {
        $(this)
          .find("i")
          .removeClass("fa-expand-arrows-alt")
          .addClass("fa-times");
        $(this).attr("data-tippy-content", fullscreen_2);
      } else {
        $(this)
          .find("i")
          .removeClass("fa-times")
          .addClass("fa-expand-arrows-alt");
        $(this).attr("data-tippy-content", fullscreen_1);
      }
      tippy(".fullscreen", {
        animation: "scale",
      });
    });


});