//pdf,print,export datatable
let jqry = $.noConflict();
jqry(document).ready(function () {
  "use strict";

  //use for every report view
  let today = new Date();
  let dd = today.getDate();
  let mm = today.getMonth() + 1; //January is 0!
  let yyyy = today.getFullYear();

  if (dd < 10) {
    dd = "0" + dd;
  }

  if (mm < 10) {
    mm = "0" + mm;
  }
  today = yyyy + "-" + mm + "-" + dd;

  //get title and datatable id name from hidden input filed that is before in the table in view page for every datatable
  let datatable_name = $(".datatable_name").attr("data-id_name");
  let title = $(".datatable_name").attr("data-title");
  let TITLE = title + "" +
    "" + today;
  jqry(`#${datatable_name},#datatable2`).DataTable({
    autoWidth: false,
    ordering: true,
    order: [[0, "desc"]],
    "lengthMenu": [
        [20, 50, 100, 500, -1],
        [20, 50, 100, 500, "Todos"]
    ],
    // dom: "Bfrtip",
    dom: '<"top-left-item col-sm-12 col-md-6"lf> <"top-right-item col-sm-12 col-md-6"B> t <"bottom-left-item col-sm-12 col-md-6 "i><"bottom-right-item col-sm-12 col-md-6 "p>',
    buttons: [
      {
        extend: "print",
        title: TITLE,
        text: '<i class="fa-solid fa-print"></i> Print',
        titleAttr: "print",
      },
      {
        extend: "copyHtml5",
        title: TITLE,
        text: '<i class="fa-solid fa-copy"></i> Copy',
        titleAttr: "Copy",
      },
      {
        extend: "excelHtml5",
        title: TITLE,
        text: '<i class="fa-solid fa-file-excel"></i> Excel',
        titleAttr: "Excel",
      },
      {
        extend: "csvHtml5",
        title: TITLE,
        text: '<i class="fa-solid fa-file-csv"></i> CSV',
        titleAttr: "CSV",
      },
      {
        extend: "pdfHtml5",
        title: TITLE,
        text: '<i class="fa-solid fa-file-pdf"></i> PDF',
        titleAttr: "PDF",
      },
    ],
    language: {
      paginate: {
        previous: "Previous",
        next: "Next",
      },
    },
  });
});
