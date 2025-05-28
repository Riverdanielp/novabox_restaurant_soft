// // Al principio de tu archivo o antes del document.ready
// function padTo2Digits(num) {
//   return num.toString().padStart(2, '0');
// }
// function getTodayDatetimeRange() {
//   const today = new Date();
//   const yyyy = today.getFullYear();
//   const mm = padTo2Digits(today.getMonth() + 1);
//   const dd = padTo2Digits(today.getDate());
//   // 00:00
//   const start = `${yyyy}-${mm}-${dd}T00:00`;
//   // 23:59
//   const end = `${yyyy}-${mm}-${dd}T23:59`;
//   return {start, end};
// }
//pdf,print,export datatable
let jqry = $.noConflict();
jqry(document).ready(function () {
  "use strict";

  // Obtén los valores de los inputs de fecha y hora
  function formatDatetimeLocalForTitle(dt) {
    if (!dt) return '';
    // dt será "YYYY-MM-DDTHH:MM"
    let [date, time] = dt.split('T');
    if (!date || !time) return dt;
    // Opcional: puedes reordenar la fecha si tu formato local lo requiere
    return date + ' ' + time;
  }
  let startDate = jqry('#startDate').val();
  let endDate = jqry('#endDate').val();
  let rango = '';
  if (startDate && endDate) {
    rango = ' | ' + formatDatetimeLocalForTitle(startDate) + ' - ' + formatDatetimeLocalForTitle(endDate);
  } else if (startDate && !endDate) {
    rango = ' | ' + formatDatetimeLocalForTitle(startDate);
  } else if (endDate && !startDate) {
    rango = ' | ' + formatDatetimeLocalForTitle(endDate);
  }

  let datatable_name = $(".datatable_name").attr("data-id_name");
  let title = $(".datatable_name").attr("data-title");
  let today = new Date();
  let dd = today.getDate();
  let mm = today.getMonth() + 1;
  let yyyy = today.getFullYear();
  if (dd < 10) dd = "0" + dd;
  if (mm < 10) mm = "0" + mm;
  today = yyyy + "-" + mm + "-" + dd;

  let TITLE = title + rango;

  jqry(`#${datatable_name},#datatable2`).DataTable({
    autoWidth: false,
    ordering: true,
    order: [[0, "desc"]],
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
