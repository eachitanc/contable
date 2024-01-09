(function ($) {
  //Superponer modales
  $(".bttn-plus-dt span").html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
  $(document).on("show.bs.modal", ".modal", function () {
    var zIndex = 1040 + 10 * $(".modal:visible").length;
    $(this).css("z-index", zIndex);
    setTimeout(function () {
      $(".modal-backdrop")
        .not(".modal-stack")
        .css("z-index", zIndex - 1)
        .addClass("modal-stack");
    }, 0);
  });
  var showError = function (id) {
    $("#" + id).focus();
    $("#e" + id).show();
    setTimeout(function () {
      $("#e" + id).fadeOut(600);
    }, 800);
    return false;
  };
  var bordeError = function (p) {
    $("#" + p).css("border", "2px solid #F5B7B1");
    $("#" + p).css("box-shadow", "0 0 4px 3px pink");
    return false;
  };
  var reloadtable = function (nom) {
    $(document).ready(function () {
      var table = $("#" + nom).DataTable();
      table.ajax.reload();
    });
  };
  var confdel = function (i, t) {
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "../nomina/empleados/eliminar/confirdel.php",
      data: { id: i, tip: t },
    }).done(function (res) {
      $("#divModalConfDel").modal("show");
      $("#divMsgConfdel").html(res.msg);
      $("#divBtnsModalDel").html(res.btns);
    });
    return false;
  };
  //Separadores de mil
  var miles = function (i) {
    $("#" + i).on({
      focus: function (e) {
        $(e.target).select();
      },
      keyup: function (e) {
        $(e.target).val(function (index, value) {
          return value
            .replace(/\D/g, "")
            .replace(/([0-9])([0-9]{2})$/, "$1.$2")
            .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      },
    });
  };
  $('#areaReporte').on('click', '#btnExcelEntrada', function () {
    let tableHtml = $('#areaImprimir').html();
    let encodedTable = btoa(unescape(encodeURIComponent(tableHtml)));
    $('<form action="' + window.urlin + '/almacen/informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encodedTable + '" /></form>').appendTo('body').submit();
  });
  // Valido que el numerico con separador de miles
  $("#divModalForms").on("keyup", "#valorAprob", function () {
    let id = "valorAprob";
    miles(id);
  });
  // Valido que el valor del cdp sea numerico con separador de miles
  $("#divCuerpoPag").on("keyup", "#valorCdp", function () {
    let id = "valorCdp";
    miles(id);
  });
  // Si el campo es mayor desactiva valor aprobado
  $("#divModalForms").on("blur", "#tipoDato", function () {
    let id = "tipoDato";
    let valor = $("#" + id).val();
    if (valor == "1") {
      $("#valorAprob").prop("disabled", false);
    } else {
      $("#valorAprob").prop("disabled", true);
    }
  });
  // Validar formulario nuevo rubros
  $("#divModalForms").on("blur", "#nomCod", function () {
    let id = "nomCod";
    let valor = $("#" + id).val();
    let pto = id_pto.value;
    //Enviar valor y consultar si ya existe en la base de datos
    $.ajax({
      type: "POST",
      url: "datos/consultar/buscar_rubro.php",
      data: { valor: valor, pto: pto },
      success: function (res) {
        if (res === "ok") {
          $("#" + id).focus();
          $("#divModalError").modal("show");
          $("#divMsgError").html("¡El codigo presupuestal ya fue registrado!");
        } else {
          //Dividir cadena con -
          let cadena = res.split("-");
          $("#tipoPresupuesto").val(cadena[1]);
          $("#tipoRecurso").val(cadena[0]);
        }
      },
    });
  });
  var setIdioma = {
    decimal: "",
    emptyTable: "No hay información",
    info: "Mostrando _START_ - _END_ registros de _TOTAL_ ",
    infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
    infoFiltered: "",
    infoPostFix: "",
    thousands: ",",
    lengthMenu: "Ver _MENU_ Filas",
    loadingRecords: "Cargando...",
    processing: "Procesando...",
    search: '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
    zeroRecords: "No se encontraron registros",
    paginate: {
      first: "&#10096&#10096",
      last: "&#10097&#10097",
      next: "&#10097",
      previous: "&#10096",
    },
  };
  var setdom;
  if ($("#peReg").val() === "1") {
    setdom = "<'row'<'col-md-5'l><'bttn-plus-dt col-md-2'B><'col-md-5'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
  } else {
    setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
  }
  $(document).ready(function () {
    let id_t = $("#id_ptp").val();
    //================================================================================ DATA TABLES ========================================
    //dataTable de presupuesto
    $("#tablePresupuesto").DataTable({
      dom: setdom,
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            $.post("datos/registrar/formadd_presupuesto.php", function (he) {
              $("#divTamModalForms").removeClass("modal-xl");
              $("#divTamModalForms").removeClass("modal-sm");
              $("#divTamModalForms").addClass("modal-lg");
              $("#divModalForms").modal("show");
              $("#divForms").html(he);
            });
          },
        },
      ],
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_presupuestos.php",
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "nombre" }, { data: "tipo" }, { data: "vigencia" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tablePresupuesto").wrap('<div class="overflow" />');
    //dataTable cargue de presupuesto
    let id_cpto = $("#id_pto_ppto").val();
    let id_ppto = $("#id_pto_ppto").val();

    $("#tableCargaPresupuesto").DataTable({
      dom: setdom,
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            $.post("datos/registrar/formadd_carga_presupuesto.php", { id_cpto: id_cpto, id_ppto: id_ppto }, function (he) {
              $("#divTamModalForms").removeClass("modal-lg");
              $("#divTamModalForms").removeClass("modal-sm");
              $("#divTamModalForms").addClass("modal-xl");
              $("#divModalForms").modal("show");
              $("#divForms").html(he);
            });
          },
        },
      ],
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_carga_presupuesto.php",
        data: { id_cpto: id_cpto },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "nombre" }, { data: "tipo_dato" }, { data: "valor" }, { data: "botones" }],
      order: [],
    });
    $("#tableCargaPresupuesto").wrap('<div class="overflow" />');
    //dataTable ejecucion de presupuesto
    let id_ejec = $("#id_pto_ppto").val();
    $("#tableEjecPresupuesto").DataTable({
      dom: setdom,
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            let ruta = {
              url: "lista_ejecucion_cdp.php",
              name: "id_ejec",
              valor: id_ejec,
            };
            redireccionar(ruta);
          },
        },
      ],
      language: setIdioma,
      serverSide: true,
      processing: true,
      ajax: {
        url: "datos/listar/datos_ejecucion_presupuesto.php",
        data: function (d) {
          // datos para enviar al servidor
          d.id_ejec = id_ejec;
          d.start = d.start || 0; // inicio de la página
          d.length = d.length || 50; // tamaño de la página
          d.search = $("#tableEjecPresupuesto_filter input").val();
          return d;
        },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "numero" }, { data: "fecha" }, { data: "objeto" }, { data: "valor" }, { data: "xregistrar" }, { data: "accion" }, { data: "botones" }],
      order: [[0, "desc"]],
      pageLength: 25,
    });
    $("#tableEjecPresupuesto").wrap('<div class="overflow" />');

    //dataTable detalle CDP
    let id_ejec2 = $("#id_pto_mvto").val();
    $("#tableEjecCdp").DataTable({
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            $.post("datos/registrar/formadd_ejecucion_presupuesto.php", { id_ejec2: id_ejec2 }, function (he) {
              $("#divTamModalForms").removeClass("modal-sm");
              $("#divTamModalForms").removeClass("modal-xl");
              $("#divTamModalForms").addClass("modal-lg");
              $("#divModalForms").modal("show");
              $("#divForms").html(he);
            });
          },
        },
      ],
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_detalle_cdp.php",
        data: { id_ejec2: id_ejec2 },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "valor" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tableEjecCdp").wrap('<div class="overflow" />');

    //dataTable ejecucion de presupuesto listado de reistros presupuestales
    $("#tableEjecPresupuestoCrp").DataTable({
      buttons: [
        {
          action: function (e, dt, node, config) {
            $.post("datos/registrar/formadd_ejecucion_presupuesto.php", { id_ejec: id_ejec }, function (he) {
              $("#divTamModalForms").removeClass("modal-sm");
              $("#divTamModalForms").removeClass("modal-xl");
              $("#divTamModalForms").addClass("modal-lg");
              $("#divModalForms").modal("show");
            });
          },
        },
      ],
      language: setIdioma,
      serverSide: true,
      processing: true,
      ajax: {
        url: "datos/listar/datos_ejecucion_presupuesto_crp.php",
        data: function (d) {
          // datos para enviar al servidor
          d.id_ejec = id_ejec;
          d.start = d.start || 0; // inicio de la página
          d.length = d.length || 50; // tamaño de la página
          d.search = $("#tableEjecPresupuestoCrp_filter input").val();
          return d;
        },
        type: "POST",
        dataType: "json",
      },
      columns: [
        { data: "numero" },
        { data: "cdp" },
        { data: "fecha" },
        { data: "contrato" },
        { data: "ccnit" },
        { data: "tercero" },
        { data: "valor" },
        { data: "botones" },
      ],
      order: [[0, "desc"]],
      pageLength: 25,
    });
    $("#tableEjecPresupuestoCrp").wrap('<div class="overflow" />');

    //dataTable ejecucion de presupuesto listado de reistros presupuestales cuando es nuevo
    let id_cdp = $("#id_pto_cdp").val();
    $("#tableEjecCrpNuevo").DataTable({
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_detalle_crp_nuevo.php",
        data: { id_cdp: id_cdp },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "valor" }, { data: "botones" }],
      order: [[0, "desc"]],
    });
    $("#tableEjecCrpNuevo").wrap('<div class="overflow" />');
    //dataTable ejecucion de presupuesto listado de reistros presupuestales existente
    let id_crp = $("#id_pto_doc").val();
    $("#tableEjecCrp").DataTable({
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_detalle_crp.php",
        data: { id_crp: id_crp },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "valor" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tableEjecCrp").wrap('<div class="overflow" />');
    //dataTable modificaciones presupuesto
    let id_pto_doc = $("#id_pto_doc").val();
    let id_pto_ppto = $("#id_pto_ppto").val();
    $("#tableModPresupuesto").DataTable({
      dom: setdom,
      buttons: [
        {
          text: ' <span class="fas fa-plus-circle fa-lg"></span>',
          action: function (e, dt, node, config) {
            $.post("datos/registrar/formadd_modifica_presupuesto_doc.php", { id_pto: id_pto_ppto }, function (he) {
              $("#divTamModalForms").removeClass("modal-sm");
              $("#divTamModalForms").removeClass("modal-xl");
              $("#divTamModalForms").addClass("modal-lg");
              $("#divModalForms").modal("show");
              $("#divForms").html(he);
            });
          },
        },
      ],
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_modifica_doc.php",
        data: { id_pto_doc: id_pto_doc, id_pto_ppto: id_pto_ppto },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "num" }, { data: "fecha" }, { data: "documento" }, { data: "numero" }, { data: "valor" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tableModPresupuesto").wrap('<div class="overflow" />');

    //dataTable modificación de presupuesto detalle de modificaciones
    let id_pto_mod = $("#id_pto_mod").val();
    let tipo_doc = $("#tipo_doc").val();
    $("#tableModDetalle").DataTable({
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_modifica_det.php",
        data: { id_pto_mod: id_pto_mod, tipo_mod: tipo_doc },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "valor" }, { data: "valor2" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tableModDetalle").wrap('<div class="overflow" />');

    //dataTable modificación de presupuesto detalle de modificaciones
    $("#tableAplDetalle").DataTable({
      language: setIdioma,
      ajax: {
        url: "datos/listar/datos_modifica_apl.php",
        data: { id_pto_mod: id_pto_mod, tipo_mod: tipo_doc },
        type: "POST",
        dataType: "json",
      },
      columns: [{ data: "rubro" }, { data: "valor" }, { data: "valor2" }, { data: "botones" }],
      order: [[0, "asc"]],
    });
    $("#tableAplDetalle").wrap('<div class="overflow" />');

    //Fin dataTable *****************************************************************************************
  });
  //===================================================================================== INSERT
  //Agregar nuevo Presupuesto
  $("#divForms").on("click", "#btnAddPresupuesto", function () {
    if ($("#nomPto").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El nombre de presupuesto no puede estar vacio!");
    } else if ($("#tipoPto").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
    } else if ($("#tipoPto").val() === "0") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
    } else {
      datos = $("#formAddPresupuesto").serialize();
      $.ajax({
        type: "POST",
        url: "datos/registrar/new_presupuesto.php",
        data: datos,
        success: function (r) {
          if (r === "1") {
            let id = "tablePresupuesto";
            reloadtable(id);
            $("#divModalForms").modal("hide");
            $("#divModalDone").modal("show");
            $("#divMsgDone").html("Adquisición Agregada Correctamente");
          } else {
            $("#divModalError").modal("show");
            $("#divMsgError").html(r);
          }
        },
      });
    }
    return false;
  });
  // Agregar nuevo cargue de rubros del presupuestos
  $("#divForms").on("click", "#btnAddCargaPresupuesto", function () {
    let id_tipoRubro = $("#tipoDato").val();
    let codigo = $("#nomCod").val();
    var campos = codigo.length;
    if ($("#nomCod").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El rubro no puede estar vacio!");
    } else if ($("#nomRubro").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El nombre del rubro no puede estar vacio!");
    } else if ($("#tipoDato").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Tipo de dato no puede ser vacio!");
    } else if ($("#valorAprob").val() === "" && id_tipoRubro === "1") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El valor no puede estar vacio!");
    } else if ($("#tipoRecurso").val() === "" && campos > 1) {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El tipo de recurso no puede estar vacio!");
    } else if ($("#tipoPresupuesto").val() === "" && campos > 1) {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El tipo de presupuesto estar vacio!");
    } else {
      datos = $("#formAddCargaPresupuesto").serialize();
      $.ajax({
        type: "POST",
        url: "datos/registrar/new_carga_presupuesto.php",
        data: datos,
        success: function (r) {
          if (r === "1") {
            let id = "tableCargaPresupuesto";
            reloadtable(id);
            $("#divModalForms").modal("hide");
            $("#divModalDone").modal("show");
            $("#divMsgDone").html("Rubro agregado correctamente...");
          } else {
            $("#divModalError").modal("show");
            $("#divMsgError").html(r);
          }
        },
      });
    }
    return false;
  });
  // Agregar ejcución a presupuesto CDP
  $("#divForms").on("click", "#btnAddEjecutaPresupuesto", function () {
    if ($("#fecha").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡La fecha no puede estar vacio!");
    } else if ($("#numCdp").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El numero de CDP no puede estar vacio!");
    } else if ($("#Objeto").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El objeto no puede ser vacio!");
    } else {
      datos = $("#formAddEjecutaPresupuesto").serialize();
      $.ajax({
        type: "POST",
        url: "datos/registrar/new_ejecucion_presupuesto.php",
        data: datos,
        success: function (r) {
          let cadena = r.split("-");
          if (cadena[0] === "ok") {
            let id = "tableEjecutaPresupuesto";
            reloadtable(id);
            $("#divModalForms").modal("hide");
            $("#divModalDone").modal("show");
            $("#divMsgDone").html("Rubro agregado correctamente...");
            // Redireccionar a la pagina de presupuestos
            $('<form action="lista_ejecucion_cdp.php" method="post"><input type="hidden" name="id_cdp" value="' + cadena[1] + '" /></form>')
              .appendTo("body")
              .submit();
          } else {
            $("#divModalError").modal("show");
            $("#divMsgError").html(r);
          }
        },
      });
    }
    return false;
  });
  // Agregar cargue de rubros al CDP
  $("#divCuerpoPag").on("click", "#btnAddValorCdp", function () {
    if ($("#id_rubroCod").val() == "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Debe seleccionar un rubro...!");
    } else if ($("#valorCdp").val() == "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El valor a registrar no debe estar vacio!");
    } else if ($("#tipoRubro").val() == 0) {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El rubro seleccionado es de tipo mayor!");
    } else {
      datos = $("#formAddValorCdp").serialize();
      $.ajax({
        type: "POST",
        url: "datos/registrar/new_ejecucion_presupuesto.php",
        data: datos,
        success: function (r) {
          let cadena = r.split("-");
          if (cadena[0] === "ok") {
            let id = "tableEjecutaPresupuesto";
            reloadtable(id);
            $("#divModalForms").modal("hide");
            $("#divModalDone").modal("show");
            $("#divMsgDone").html("Rubro agregado correctamente...");
            // Redireccionar a la pagina de presupuestos
            $('<form action="lista_ejecucion_cdp.php" method="post"><input type="hidden" name="id_cdp" value="' + cadena[1] + '" /></form>')
              .appendTo("body")
              .submit();
          } else {
            $("#divModalError").modal("show");
            $("#divMsgError").html(r);
          }
        },
      });
    }
    return false;
  });

  //========================================================================================  FORM UPDATE */
  //1. Editar Presupuesto llama formulario
  $("#modificarPresupuesto").on("click", ".editar", function () {
    let idtbs = $(this).attr("value");
    $.post("datos/actualizar/edita_presupuesto.php", { idtbs: idtbs }, function (he) {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-xl");
      $("#divTamModalForms").addClass("modal-lg");
      $("#divModalForms").modal("show");
      $("#divForms").html(he);
    });
  });
  //1.1. ejecuta editar presupuesto
  $("#divForms").on("click", "#btnUpdatePresupuesto", function () {
    if ($("#nomPto").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡El nombre de presupuesto no puede estar vacio!");
    } else if ($("#tipoPto").val() === "") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
    } else if ($("#tipoPto").val() === "0") {
      $("#divModalError").modal("show");
      $("#divMsgError").html("¡Tipo de presupuesto no puede ser Vacío!");
    } else {
      datos = $("#formUpdatePresupuesto").serialize();
      $.ajax({
        type: "POST",
        url: "datos/actualizar/update_presupuesto.php",
        data: datos,
        success: function (r) {
          if (r === "1") {
            let id = "tablePresupuesto";
            reloadtable(id);
            $("#divModalForms").modal("hide");
            $("#divModalDone").modal("show");
            $("#divMsgDone").html("Adquisición Actualizada Correctamente");
          } else {
            $("#divModalError").modal("show");
            $("#divMsgError").html(r);
          }
        },
      });
    }
    return false;
  });
  //2. Editar detalles de CDP
  $("#modificarEjecPresupuesto").on("click", ".editar", function () {
    let id_cdp = $(this).attr("value");
    let id_ppto = $("#id_pto_ppto").val();
    // Redireccionar a la pagina de presupuestos
    $(
      '<form action="lista_ejecucion_cdp.php" method="post"><input type="hidden" name="id_cdp" value="' +
      id_cdp +
      '" /><input type="hidden" name="id_ejec" value="' +
      id_ppto +
      '" /></form>'
    )
      .appendTo("body")
      .submit();
  });
  //===================================================================================== ELIMINAR
  // Eliminar presupuesto anexa campo a la etiqueta
  $("#modificarPresupuesto").on("click", ".borrar", function () {
    let id = $(this).attr("value");
    let tip = "ppto";
    confdel(id, tip);
  });
  //Eliminar presupuesto
  $("#divBtnsModalDel").on("click", "#btnConfirDelppto", function () {
    $("#divModalConfDel").modal("hide");
    $.ajax({
      type: "POST",
      url: "datos/eliminar/del_presupuestos.php",
      data: {},
      success: function (r) {
        if (r === "1") {
          let id = "tablePresupuesto";
          reloadtable(id);
          $("#divModalDone").modal("show");
          $("#divMsgDone").html("Presupuesto eliminado correctamente");
        } else {
          $("#divModalError").modal("show");
          $("#divMsgError").html(r);
        }
      },
    });
    return false;
  });
  // Eliminar cargue de presupuestos
  $("#modificarCargaPresupuesto").on("click", ".borrar", function () {
    let id = $(this).attr("value");
    let tip = "carga";
    confdel(id, tip);
  });
  //Eliminar cargue de presupuestos
  $("#divBtnsModalDel").on("click", "#btnConfirDelcarga", function () {
    $("#divModalConfDel").modal("hide");
    let pto = id_pto_ppto.value;

    $.ajax({
      type: "POST",
      url: "datos/eliminar/del_carga_presupuesto.php",
      data: { pto: pto },
      success: function (r) {
        if (r === "1") {
          let id = "tableCargaPresupuesto";
          reloadtable(id);
          $("#divModalDone").modal("show");
          $("#divMsgDone").html("Carga de presupuesto eliminado correctamente");
        } else {
          $("#divModalError").modal("show");
          $("#divMsgError").html(r);
        }
      },
    });
    return false;
  });

  //==========================================================================  Menu Gestión cargue presupuesto */
  // 1. Agregar cargue presupuesto
  $("#modificarPresupuesto").on("click", ".carga", function () {
    let id_pto = $(this).attr("value");
    $('<form action="lista_cargue_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  });
  // 2. Agregar ejecucion al presupuesto cuando es gastos
  $("#modificarPresupuesto").on("click", ".ejecuta", function () {
    let id_pto = $(this).attr("value");
    $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  });
  $("#modificarPresupuesto").on("click", ".homologa", function () {
    let id_pto = $(this).attr("value");
    $('<form action="lista_homologacion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  });
  // 3. Agregar modificaciones al presupuestos
  $("#modificarPresupuesto").on("click", ".modifica", function () {
    let id_pto = $(this).attr("value");
    $('<form action="lista_modificacion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  });
  // 4. Volver de edición de cdp a listado de documentos cdp
  $("#divCuerpoPag").on("click", "#volverListaCdps", function () {
    let id_pto = $("#id_pto_presupuestos").val();
    $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  });

  // Cargar lista_ejecucion_contratacion.php por ajax
  $("#divCuerpoPag").on("click", "#botonContrata", function () {
    $.post("lista_ejecucion_contratacion.php", {}, function (he) {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-lg");
      $("#divTamModalForms").addClass("modal-xl");
      $("#divModalForms").modal("show");
      $("#divForms").html(he);
    });
  });
  // funcion imprimir arrow

  // Cargar lista_ejecucion_contratacion.php por ajax
  $("#divCuerpoPag").on("click", "#botonListaCdp", function () {
    $.post("lista_espacios_cdp.php", {}, function (he) {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-lg");
      $("#divTamModalForms").addClass("modal-xl");
      $("#divModalForms").modal("show");
      $("#divForms").html(he);
    });
  });

  // Cargar lista de solicitudes para cdp de otro si
  $("#divCuerpoPag").on("click", "#botonOtrosi", function () {
    $.post("lista_modificacion_otrosi.php", {}, function (he) {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-lg");
      $("#divTamModalForms").addClass("modal-xl");
      $("#divModalForms").modal("show");
      $("#divForms").html(he);
    });
  });
})(jQuery);

const imprimirFormatoCdp = (id) => {
  let url = "soportes/imprimir_formato_cdp.php";
  $.post(url, { id: id }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-xl");
    $("#divTamModalForms").addClass("modal-lg");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

const imprimirFormatoMod = (id) => {
  let url = "soportes/imprimir_formato_mod.php";
  $.post(url, { id: id }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-xl");
    $("#divTamModalForms").addClass("modal-lg");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

const imprimirFormatoCrp = (id) => {
  if (id == "") {
    id = id_pto_save.value;
  }
  if (id == "") {
  } else {
    let url = "soportes/imprimir_formato_crp.php";
    $.post(url, { id: id }, function (he) {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-xl");
      $("#divTamModalForms").addClass("modal-lg");
      $("#divModalForms").modal("show");
      $("#divForms").html(he);
    });
  }
};
function imprSelecCdp(nombre) {
  var ficha = document.getElementById(nombre);
  var ventimp = window.open(" ", "popimpr");
  ventimp.document.write(ficha.innerHTML);
  ventimp.document.close();
  ventimp.print();
  ventimp.close();
}
function imprSelecCrp(nombre) {
  var ficha = document.getElementById(nombre);
  var ventimp = window.open(" ", "popimpr");
  ventimp.document.write(ficha.innerHTML);
  ventimp.document.close();
  ventimp.print();
  ventimp.close();
}

var reloadtable = function (nom) {
  $(document).ready(function () {
    var table = $("#" + nom).DataTable();
    table.ajax.reload();
  });
};
// Mensaje
function mje(titulo) {
  Swal.fire({
    title: titulo,
    icon: "success",
    showConfirmButton: true,
    timer: 1000,
  });
}
// funcion valorMiles
function milesp(i) {
  $("#" + i).on({
    focus: function (e) {
      $(e.target).select();
    },
    keyup: function (e) {
      $(e.target).val(function (index, value) {
        return value
          .replace(/\D/g, "")
          .replace(/([0-9])([0-9]{2})$/, "$1.$2")
          .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
      });
    },
  });
}
// Funcion para redireccionar la recarga de la pagina
function redireccionar(ruta) {
  console.log(ruta);
  setTimeout(() => {
    $(
      '<form action="' +
      ruta.url +
      '" method="post">\n\
    <input type="hidden" name="' +
      ruta.name +
      '" value="' +
      ruta.valor +
      '" />\n\
    </form>'
    )
      .appendTo("body")
      .submit();
  }, 100);
}

function redireccionar2(ruta) {
  setTimeout(() => {
    $(
      '<form action="' +
      ruta.url +
      '" method="post">\n\
    <input type="hidden" name="' +
      ruta.name1 +
      '" value="' +
      ruta.valor1 +
      '" />\n\
    <input type="hidden" name="' +
      ruta.name2 +
      '" value="' +
      ruta.valor2 +
      '" />\n\
    </form>'
    )
      .appendTo("body")
      .submit();
  }, 100);
}

function valorMiles(id) {
  console.log("valor" + id);
  milesp(id);
}
/*  ========================================================= Certificado de disponibilidad presupuestal ========================================================= */
// mostrar list_Ejecucion_cdp.php
function mostrarListaCdp(dato) {
  let ppto = id_pto_ppto.value;
  let ruta = {
    url: "lista_ejecucion_cdp.php",
    name1: "id_adq",
    valor1: dato,
    name2: "id_ejec",
    valor2: ppto,
  };
  redireccionar2(ruta);
}
// genera cdp y rp para nomina
//--!EDWIN
$("#divCuerpoPag").on("click", "#btnPtoNomina", function () {
  $.post("lista_ejecucion_nomina.php", {}, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
});
function CofirmaCdpRp(boton) {
  var cant = document.getElementById("cantidad");
  var valor = Number(cant.value);
  var data = boton.value;
  var datos = data.split("|");
  var tipo = datos[1];
  var ruta = "";
  if (tipo == "PL") {
    ruta = "procesar/causacion_planilla.php";
  } else {
    ruta = "procesar/causacion_nomina.php";
  }
  Swal.fire({
    title: "¿Confirma asignacion de CPD y RP para Nómina?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#00994C",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si!",
    cancelButtonText: "NO",
  }).then((result) => {
    if (result.isConfirmed) {
      boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      boton.disabled = true;
      fetch(ruta, {
        method: "POST",
        body: data,
      })
        .then((response) => response.text())
        .then((response) => {
          if (response == "ok") {
            boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
            cant.value = valor - 1;
            document.getElementById("nCant").innerHTML = valor - 1;
            let tabla = "tableEjecPresupuesto";
            reloadtable(tabla);
            $("#divModalForms").modal("hide");
            mje("Registro exitoso");
          } else {
            mjeError("Error: " + response);
          }
        });
    }
  });
}
//EDWIN!--
// Muestra formulario para cdp desde lsitado de otro si
function mostrarListaOtrosi(dato) {
  let ppto = id_pto_ppto.value;
  let ruta = {
    url: "lista_ejecucion_cdp.php",
    name1: "id_otro",
    valor1: dato,
    name2: "id_ejec",
    valor2: ppto,
  };
  redireccionar2(ruta);
}
/*  ========================================================= Certificado de registro pursupuestal ==========================================*/
//Carga el formulario del registro presupuestal con datos del cdp asociado

const CargarFormularioCrpp = (id) => {
  let dato = id || 0;
  let ruta = {
    url: "lista_ejecucion_crp_nuevo.php",
    name: "id_cdp",
    valor: dato,
  };
  redireccionar(ruta);
};
// Registrar en la tabla documentos la parte general del registro presupuestal
document.addEventListener("submit", (e) => {
  let id_cdp = $("#id_doc").val();
  e.preventDefault();
  if (e.target.id == "formAddCrpp") {
    fetch("datos/crp/registrar_doc_crp.php", {
      method: "POST",
      body: new FormData(formAddCrpp),
    })
      .then((response) => response.json())
      .then((response) => {
        if (response[0].value == "ok") {
          //mje('Registrado todo ok');
        } else {
          mje("Registro modificado");
        }
        formAddCrpp.reset();
        // Redirecciona documento para asignar valores por rubro
        setTimeout(() => {
          $(
            '<form action="lista_ejecucion_crp_nuevo.php" method="post">\n\
            <input type="hidden" name="id_crp" value="' +
            response[0].id +
            '" />\n\
            <input type="hidden" name="id_cdp" value="' +
            id_cdp +
            '" />\n\
            </form>'
          )
            .appendTo("body")
            .submit();
        }, 500);
      });
  }
});
// Autocomplete para la selección del tercero que se asigna al registro presupuestal
document.addEventListener("keyup", (e) => {
  if (e.target.id == "tercerocrp") {
    let valor = $("#id_pto").val();
    $("#tercerocrp").autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "datos/consultar/buscar_terceros.php",
          type: "post",
          dataType: "json",
          data: {
            search: request.term,
            valor: valor,
          },
          success: function (data) {
            response(data);
          },
        });
      },
      select: function (event, ui) {
        $("#tercerocrp").val(ui.item.label);
        $("#id_tercero").val(ui.item.value);
        return false;
      },
      focus: function (event, ui) {
        $("#tercerocrp").val(ui.item.label);
        return false;
      },
    });
  }
});

// Redireccionar a la tabla de crp por acciones en el select
function cambiaListado(dato) {
  let id_pto = $("#id_pto_ppto").val();
  if (dato == "2") {
    $('<form action="lista_ejecucion_pto_crp.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  }
  if (dato == "1") {
    $('<form action="lista_ejecucion_pto.php" method="post"><input type="hidden" name="id_pto" value="' + id_pto + '" /></form>')
      .appendTo("body")
      .submit();
  }
}
// Editar detalle de registro presupuestal al dar clic en listado
function CargarListadoCrpp(id) {
  $('<form action="lista_ejecucion_crp.php" method="post"><input type="hidden" name="id_crp" value="' + id + '" /></form>')
    .appendTo("body")
    .submit();
}
// Guradar detalle de rubros de registro presupuestal
document.addEventListener("click", (e) => {
  if (e.target.id == "registrarRubrosCrp") {
    let error = 0;
    let num = 0;
    var datos = {};
    let id_crp = id_pto_crp.value;
    let formulario = new FormData(formRegistrarRubrosCrp);
    formulario.delete("tableEjecCrpNuevo_length");
    // Validación de valores maximos permitidos
    for (var pair of formulario.entries()) {
      let div1 = document.getElementById(pair[0]);
      let max = div1.getAttribute("max");
      let valormax = parseFloat(max.replace(/\,/g, "", ""));
      let valor = parseFloat(pair[1].replace(/\,/g, "", ""));
      if (valor > valormax) {
        Swal.fire({
          title: "Error",
          text: "El valor ingresado: " + pair[1] + " supera el máximo permitido de: " + max,
          icon: "error",
          showConfirmButton: true,
        });
        error = 1;
        return false;
      }
      datos[pair[0]] = pair[1];
      num++;
    }
    // Creo los datos a Enviar
    var formEnvio = new FormData();
    formEnvio.append("crpp", id_crp);
    formEnvio.append("datos", JSON.stringify(datos));
    formEnvio.append("num", num);
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
    }
    if (error == 0) {
      fetch("datos/crp/registrar_rubros_crp.php", {
        method: "POST",
        body: formEnvio,
      })
        .then((response) => response.json())
        .then((response) => {
          if (response[0].value == "ok") {
            mje("Registrado todo ok");
          } else {
            mje("Registro modificado");
          }
          formRegistrarRubrosCrp.reset();
          // objeto Redireccionar
          let ruta = {
            url: "lista_ejecucion_crp.php",
            name: "id_crp",
            valor: id_crp,
          };
          redireccionar(ruta);
          // Redirecciona documento para asignar valores por rubro
        });
    }
  }
});

// Eliminar registro presupuestal valida que el registro no tenga o facturas registradas o en proceso
function eliminarCrpp(id) {
  Swal.fire({
    title: "¿Está seguro de eliminar el registro?",
    text: "No podrá revertir esta acción",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      fetch("datos/eliminar/del_eliminar_crp.php", {
        method: "POST",
        body: id,
      })
        .then((response) => response.text())
        .then((response) => {
          if (response == "ok") {
            // Reonlidar la tabla
            let id = "tableEjecPresupuestoCrp";
            reloadtable(id);
            mje("Registro eliminado");
          } else {
            mjeError("No se puede eliminar el registro");
          }
        });
    }
  });
}
//================================================== Modificaciones al presupuesto ==================================================
function cambiaListadoModifica(dato) {
  let id_pto = $("#id_pto_ppto").val();
  $(
    '<form action="lista_modificacion_pto.php" method="post">\n\
    <input type="hidden" name="id_pto" value="' +
    id_pto +
    '" />\n\
    <input type="hidden" name="tipo_mod" value="' +
    dato +
    '" /></form>'
  )
    .appendTo("body")
    .submit();
}
// Registrar en la tabla documentos la parte general la modificacion presupuestal
document.addEventListener("submit", (e) => {
  let tipo_doc = $("#id_pto_doc").val();
  e.preventDefault();
  if (e.target.id == "formAddModificaPresupuesto") {
    let formEnvio = new FormData(formAddModificaPresupuesto);
    formEnvio.append("tipo_doc", tipo_doc);
    // Obtener atributos min y max del campo fecha
    let fecha_min = document.querySelector("#fecha").getAttribute("min");
    let fecha_max = document.querySelector("#fecha").getAttribute("max");
    // Validar que la fecha no sea mayor a la fecha maxima y menor a la fecha mínima
    if (formEnvio.get("fecha") > fecha_max || formEnvio.get("fecha") < fecha_min) {
      document.querySelector("#fecha").focus();
      mjeError("La fecha debe estar entre " + fecha_min + " y " + fecha_max, "");
      return false;
    }
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
    }
    fetch("datos/registrar/registrar_modifica_pto_doc.php", {
      method: "POST",
      body: formEnvio,
    })
      .then((response) => response.json())
      .then((response) => {
        if (response[0].value == "ok") {
          //mje('Registrado todo ok');
        } else {
          mje("Registro modificado");
        }
        formAddModificaPresupuesto.reset();
        // Redirecciona documento para asignar valores de detalle
        let ruta = {
          url: "lista_modificacion_det.php",
          name: "id_mod",
          valor: response[0].id,
        };
        redireccionar(ruta);
      });
  }
});
// Cargar lista detalle de moificaciones presupuestales
function cargarListaDetalleMod(id_doc) {
  $('<form action="lista_modificacion_det.php" method="post"><input type="hidden" name="id_mod" value="' + id_doc + '" /></form>')
    .appendTo("body")
    .submit();
}
//Carga el formulario del detalle de modificación presupuestal
function CargarFormModiDetalle(busqueda) {
  fetch("datos/registrar/formadd_modifica_detalle.php", {
    method: "POST",
    body: busqueda,
  })
    .then((response) => response.text())
    .then((response) => {
      console.log(response);
      divformDetalle.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
}
// Autocomplete rubro modificaciones presupuestales detalle
document.addEventListener("keyup", (e) => {
  let valor = 2;
  if (e.target.id == "rubroCod") {
    let tipo_doc = $("#tipo_doc").val();
    //salert(tipo_doc);
    console.log("llego");
    let id_ing = pto_ingresos.value;
    let id_gas = pto_gastos.value;
    let estado = $("#btnIngresos").hasClass("active");
    $("#rubroCod").autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "datos/consultar/consultaRubrosMod.php",
          type: "post",
          dataType: "json",
          data: {
            search: request.term,
            valor: valor,
            estado: estado,
            tipo_doc: tipo_doc,
            id_ingreso: id_ing,
            id_gasto: id_gas,
          },
          success: function (data) {
            response(data);
            var tipo = data[0].tipo;
            if (tipo == 3) {
              $("#id_rubroCod").val("");
            }
          },
        });
      },
      select: function (event, ui) {
        $("#rubroCod").val(ui.item.label);
        $("#id_rubroCod").val(ui.item.value);
        $("#tipoRubro").val(ui.item.tipo);
        return false;
      },
      focus: function (event, ui) {
        $("#rubroCod").val(ui.item.label);
        $("#id_rubroCod").val(ui.item.value);
        $("#tipoRubro").val(ui.item.tipo);
        return false;
      },
    });
  }
});

// Registrar el detalle de las modificaciones
document.addEventListener("submit", (e) => {
  e.preventDefault();
  if (e.target.id == "formAddModDetalle") {
    let formEnvio = new FormData(formAddModDetalle);
    let estado = $("#btnIngresos").hasClass("active");
    let tipo_doc = $("#tipo_doc").val();
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
      if (formEnvio.get("id_rubroCod") == "" || formEnvio.get("id_rubroCod") == "No encontrado...") {
        mjeError("Rubro no valido...", "Verifique la información registrada");
        return;
      }
      if (formEnvio.get("tipoRubro") == 0) {
        mjeError("El rubro no es de detalle...", "Verifique la información registrada");
        return;
      }
      if (formEnvio.get("valorDeb") == 0) {
        mjeError("Valor no valido...", "Verifique la información registrada");
        // focus a campo valorDeb
        return;
      }
    }
    if (estado == true && tipo_doc != "ADI" && tipo_doc != "RED") {
      let valor_max = document.querySelector("#valorDeb").getAttribute("max");
      console.log(valor_max);
      let valor_deb = valorDeb.value;
      valor_deb = parseFloat(valor_deb.replace(/\,/g, "", ""));
      if (valor_deb > 0) {
        // Consulto el saldo del rubro
        let rubro = id_rubroCod.value;
        fetch("datos/consultar/consultaSaldoCdp.php", {
          method: "POST",
          body: JSON.stringify({ rubro: rubro }),
        })
          .then((response) => response.json())
          .then((response) => {
            let valor_saldo = response[0].total;
            if (valor_deb > valor_saldo) {
              document.querySelector("#valorDeb").focus();
              mjeError("El valor no puede ser mayor al saldo del rubro", "");
              return false;
            } else {
              formEnvio.append("estado", estado);
              fetch("datos/registrar/registrar_modifica_pto_det.php", {
                method: "POST",
                body: formEnvio,
              })
                .then((response) => response.json())
                .then((response) => {
                  if (response[0].value == "ok") {
                    $("#suma1").val(response[0].valor1);
                    $("#suma2").val(response[0].valor2);
                    $("#dif").val(response[0].dif);
                  } else {
                    mje("Registro modificado");
                  }
                  formAddModDetalle.reset();
                  let id = "tableModDetalle";
                  reloadtable(id);

                  // Actualizar sumas
                });
            }
          });
      }
    } else {
      formEnvio.append("estado", estado);
      fetch("datos/registrar/registrar_modifica_pto_det.php", {
        method: "POST",
        body: formEnvio,
      })
        .then((response) => response.json())
        .then((response) => {
          if (response[0].value == "ok") {
            $("#suma1").val(response[0].valor1);
            $("#suma2").val(response[0].valor2);
            $("#dif").val(response[0].dif);
          } else {
            mje("Registro modificado");
          }
          formAddModDetalle.reset();
          let id = "tableModDetalle";
          reloadtable(id);

          // Actualizar sumas
        });
    }
  }
});

function valorDif() {
  let dif = $("#dif").val();
  $("#valorDeb").val(dif);
}
// Terminar de registrar movimientos de detalle  verificando sumas sumas iguales en modificacion presupuestal
let terminarDetalleMod = function (dato) {
  let tipo_doc = $("#tipo_doc").val();
  let dif = $("#dif").val();
  if (tipo_doc != "APL" && tipo_doc != "DES") {
    if (dif != 0) {
      mjeError("Las sumas deben ser iguales..", "Puede usar doble click en la casilla para verificar");
      // Envia un registro marcando el documento como descuadrado
    } else {
      cambiaListadoModifica(dato);
    }
  } else {
    cambiaListadoModifica(dato);
  }
};
// Cerrar documento presupuestal modificacion
let cerrarDocumentoMod = function (dato) {
  fetch("datos/consultar/consultaCerrar.php", {
    method: "POST",
    body: dato,
  })
    .then((response) => response.json())
    .then((response) => {
      if (response[0].value == "ok") {
        mje("Documento cerrado");
        let id = "tableModPresupuesto";
        reloadtable(id);
        document.getElementById("editar_" + dato).style.display = "none";
        document.getElementById("eliminar_" + dato).style.display = "none";
      } else {
        mjeError("Documento no aprobado", "Verifique sumas iguales");
      }
    });
};
// Abrir documento modificación presupuestal
let abrirDocumentoMod = function (dato) {
  let doc = id_pto_doc.value;
  fetch("datos/consultar/consultaAbrir.php", {
    method: "POST",
    body: dato,
  })
    .then((response) => response.json())
    .then((response) => {
      if (response[0].value == "ok") {
        mje("Documento abierto");
        let id = "tableModPresupuesto";
        reloadtable(id);
      } else {
        mjeError("Documento no abierto", "Verifique sumas iguales");
      }
    });
};
// Editar rubros de modificacion presupuestal
let editarListaDetalleMod = (id) => { };

// Eliminar rubros de modificaciones presupuestales adición
let eliminarRubroDetalleMod = (id) => {
  Swal.fire({
    title: "¿Está seguro de eliminar el movimiento de modificación?",
    text: "No podrá revertir esta acción",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      fetch("datos/eliminar/del_eliminar_movimiento.php", {
        method: "POST",
        body: JSON.stringify({ dato: id }),
      })
        .then((response) => response.json())
        .then((response) => {
          console.log(response);
          if (response[0].value == "ok") {
            // Reonlidar la tabla
            let id = "tableModDetalle";
            reloadtable(id);
            mje("Registro eliminado");
          } else {
            mjeError("No se puede eliminar el registro");
          }
        });
    }
  });
};

// Establecer consecutivo para certificado de disponibilidad presupuestal
let buscarConsecutivo = function (doc, campo) {
  let fecha = $("#fecha").val();
  let id_doc = $("#id_pto_mvto").val();
  if (id_doc) {
    let id_pto_doc = $("#numCdp").val();
  } else {
    fetch("datos/consultar/consultaConsecutivo.php", {
      method: "POST",
      body: JSON.stringify({ fecha: fecha, documento: doc }),
    })
      .then((response) => response.json())
      .then((response) => {
        $("#numCdp").val(response[0].numero);
      });
  }
};
function eliminarCdp(id) {
  Swal.fire({
    title: "Esta seguro de eliminar el documento?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#00994C",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si!",
    cancelButtonText: "NO",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch("datos/eliminar/del_eliminar_cdp.php", {
        method: "POST",
        body: id,
      })
        .then((response) => response.text())
        .then((response) => {
          if (response == "ok") {
            let tabla = "tableEjecPresupuesto";
            reloadtable(tabla);
          }
        });
    }
  });
}
// Buscar si numero de documento ya existe
let buscarCdp = function (doc, campo) {
  fetch("datos/consultar/consultaDocumento.php", {
    method: "POST",
    body: JSON.stringify({ doc: doc, tipo: campo }),
  })
    .then((response) => response.json())
    .then((response) => {
      console.log(response[0].numero);
      if (response[0].numero > 0) {
        let numini = $("#id_pto_docini").val();
        $("#numCdp").val(numini);
        mje("El documento ya existe");
      }
    });
};
// Redireccionar a lista_ejecucion_cdp
const redirecionarListacdp = (id, id_manu) => {
  let dato = id || 0;
  let ruta = {
    url: "lista_ejecucion_cdp.php",
    name: "id_cdp",
    valor: dato,
  };
  redireccionar(ruta);
};

// Funcion para mostrar formulario de fecha de sessión de usuario
const cambiarFechaSesion = (anno, user, url) => {
  // enviar anno y user a php para cargar informacion registrada
  let servidor = location.origin;
  fetch(servidor + url + "/financiero/fecha/form_fecha_sesion.php", {
    method: "POST",
    body: JSON.stringify({ vigencia: anno, usuario: user }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalPermisos").removeClass("modal-xl");
      $("#divTamModalPermisos").removeClass("modal-lg");
      $("#divTamModalPermisos").addClass("modal-sm");
      $("#divModalPermisos").modal("show");
      divTablePermisos.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};
// funcion para cambiar sessión de usuario
const changeFecha = (url) => {
  let servidor = location.origin;
  let fromEnviar = new FormData(formFechaSesion);
  fetch(servidor + url + "/financiero/fecha/change_fecha_sesion.php", {
    method: "POST",
    body: fromEnviar,
  })
    .then((response) => response.json())
    .then((response) => {
      if (response[0].value == "ok") {
        formFechaSesion.reset();
        $("#divModalPermisos").modal("hide");
        mje("Fecha actualizada");
      } else {
        formFechaSesion.reset();
        $("#divModalPermisos").modal("hide");
        mje("Fecha actualizada");
      }
    });
};
// Funcion para generar formato de cdp
const generarFormatoCdp = (id) => {
  let formato = window.urlin + "/presupuesto/soportes/formato_cdp.php";
  let ruta = {
    url: formato,
    name: "datos",
    valor: id,
  };
  redireccionar(ruta);
};
// Funcion para generar formato de cdp

const generarFormatoCrp = (id) => {
  console.log(id);
  let formato = window.urlin + "/presupuesto/soportes/formato_rp.php";
  let ruta = {
    url: formato,
    name: "datos",
    valor: id,
  };
  redireccionar(ruta);
};

// Funcion para generar formato de Modificaciones
const generarFormatoMod = (id) => {
  let archivo = window.urlin + "/presupuesto/soportes/formato_modifica.php";
  let ruta = {
    url: archivo,
    name: "datos",
    valor: id,
  };
  redireccionar(ruta);
};
// Función eliminar modificación presupuestales
const eliminarModPresupuestal = (id) => {
  Swal.fire({
    title: "Esta seguro de eliminar el documento?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#00994C",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si!",
    cancelButtonText: "NO",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch("datos/eliminar/del_eliminar_cdp.php", {
        method: "POST",
        body: id,
      })
        .then((response) => response.text())
        .then((response) => {
          if (response == "ok") {
            let tabla = "tableModPresupuesto";
            reloadtable(tabla);
            Swal.fire({
              icon: "success",
              title: "Eliminado",
              showConfirmButton: true,
              timer: 1500,
            });
            formAddValorCdp.reset();
          }
        });
    }
  });
};

// Redireccionar a lista_modificacion_det.php
const redirecionarListaMod = (id) => {
  let ruta = {
    url: "lista_modificacion_des.php",
    name: "id_mod",
    valor: id,
  };
  redireccionar(ruta);
};
var modiapl = false;
$("#divCuerpoPag").ready(function () {
  $("#numApl").change(function () {
    modiapl = true;
  });
  $("#tipo_acto").change(function () {
    modiapl = true;
  });
  $("#fecha").change(function () {
    modiapl = true;
  });
  $("#objeto").change(function () {
    modiapl = true;
  });
});
// Registrar desaplazamiento presupuestal
document.addEventListener("submit", (e) => {
  e.preventDefault();
  if (e.target.id == "formAddDezaplazamiento") {
    let formEnvioApl = new FormData(formAddDezaplazamiento);
    if (modiapl) {
      formEnvioApl.append("estado", 0);
    }
    // Validación del formulario
    for (var pair of formEnvioApl.entries()) {
      console.log(pair[0] + ", " + pair[1]);
      // Validación del valor del desaplazamiento
      let valor_max = document.querySelector("#valorDeb").getAttribute("max");
      let valor_des = formEnvioApl.get("valorDeb");
      valor_des = parseFloat(valor_des.replace(/\,/g, "", ""));
      if (valor_des < 1 || valor_des > valor_max) {
        document.querySelector("#valorDeb").focus();
        mjeError("Debe digitar un valor valido", "");
        return false;
      }
    }

    fetch("datos/registrar/registrar_desaplazamiento_apl.php", {
      method: "POST",
      body: formEnvioApl,
    })
      .then((response) => response.json())
      .then((response) => {
        if (response[0].value == "ok") {
          modiapl = false;
          console.log(response);
          id_pto_apl.value = response[0].id;
          rubroCod.value = "";
          id_rubroCod.value = "";
          valorDeb.value = "";
        } else {
          mje("Registro modificado");
        }
        let id = "tableAplDetalle";
        reloadtable(id);
      });
  }
});

// Funcióm para editar el valor del aplazamiento
function editarAplazamiento(id) {
  fetch("datos/consultar/editarRubrosApl.php", {
    method: "POST",
    body: id,
  })
    .then((response) => response.json())
    .then((response) => {
      console.log(response);
      rubroCod.value = response.rubro + " - " + response.nom_rubro;
      id_rubroCod.value = response.rubro;
      valorDeb.value = response.valor;
      valorDeb.max = response.valor;
    });
}

// Ver historial de ejecución del rubro
const verHistorial = (anno) => {
  let rubro = id_rubroCod.value;
  fetch("datos/reportes/form_resumen_rubro.php", {
    method: "POST",
    body: JSON.stringify({ vigencia: anno, rubro: rubro }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalPermisos").removeClass("modal-xl");
      $("#divTamModalPermisos").removeClass("modal-lg");
      $("#divTamModalPermisos").addClass("");
      $("#divModalPermisos").modal("show");
      divTablePermisos.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Ver historial de ejecución del rubro desde CDP
const verHistorialCdp = (anno) => {
  let rubro = id_rubroCdp.value;
  let fecha = ""; //fecha.value;
  fetch("datos/reportes/form_resumen_rubro.php", {
    method: "POST",
    body: JSON.stringify({ vigencia: anno, rubro: rubro, fecha: fecha }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalPermisos").removeClass("modal-xl");
      $("#divTamModalPermisos").removeClass("modal-lg");
      $("#divTamModalPermisos").addClass("");
      $("#divModalPermisos").modal("show");
      divTablePermisos.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Consultar saldo del cdp
const consultaSaldoCdp = (anno) => {
  let rubro = id_rubroCdp.value;
  let valor = valorCdp.value;
  valor = parseFloat(valor.replace(/\,/g, "", ""));
  fetch("datos/consultar/consultaSaldoCdp.php", {
    method: "POST",
    body: JSON.stringify({ vigencia: anno, rubro: rubro }),
  })
    .then((response) => response.json())
    .then((response) => {
      let saldo = response[0].total;
      valorCdp.max = response[0].total;
      if (saldo < valor) {
        mjeError("El saldo del rubro es insuficiente .....", "");
        valorDeb.focus();
        // Inhabilitar el boton de guardar
      }
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Consultar saldo del rubro en modificacion
const consultaSaldoRubro = (anno) => {
  let estado = $("#btnIngresos").hasClass("active");
  let tipo_mod = tipo_doc.value;
  var guardarButton = document.getElementById("registrarMovDetalle");

  console.log(estado);
  if (estado == false && tipo_mod != "ADI") {
    let rubro = id_rubroCod.value;
    let valor = valorDeb.value;
    valor = parseFloat(valor.replace(/\,/g, "", ""));
    fetch("datos/consultar/consultaSaldoCdp.php", {
      method: "POST",
      body: JSON.stringify({ vigencia: anno, rubro: rubro }),
    })
      .then((response) => response.json())
      .then((response) => {
        console.log(response);
        let saldo = response[0].total;
        valorDeb.max = response[0].total;
        if (saldo < valor) {
          guardarButton.disabled = true;
          mjeError("El saldo del rubro es insuficiente", "");
          valorDeb.focus();
        } else {
          guardarButton.disabled = false;
        }
      })
      .catch((error) => {
        console.log("Error:");
      });
  }
};

// Funcion para realizar el registro presupuestal a un crp
document.addEventListener("submit", (e) => {
  e.preventDefault();
  if (e.target.id == "formRegistroCrp") {
    let formEnvio = new FormData(formRegistroCrp);
    let datos = {};
    let i = 0;
    let j = 0;
    let k = 0;
    if (modiapl) {
      formEnvio.append("estado", 0);
      k++;
    }
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
      i++;
    }
    if (i >= 10) {
      i = i - 1 - k;
    }

    for (var pair of formEnvio.entries()) {
      j++;

      if (j > 8 && j <= i) {
        datos[pair[0]] = pair[1];
        // obtengo el valor max de campo marcado con pair[0]
        let valor_max = document.querySelector(`#${pair[0]}`).getAttribute("max");
        valor_max = parseFloat(valor_max.replace(/\,/g, "", ""));
        // obtengo el valor de campo marcado con pair[0]
        let valor_rubro = parseFloat(pair[1].replace(/\,/g, "", ""));
        // si el valor es mayor que el valor max, entonces no se puede registrar
        if (valor_rubro > valor_max) {
          document.querySelector(`#${pair[0]}`).focus();
          mjeError("El valor no puede ser mayor que el saldo", "");
          return false;
        }
      }
    }
    formEnvio.append("datos", JSON.stringify(datos));
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
      let pptou = pair[6];
      // Validar el formulario
      if (formEnvio.get("numCdp") == "") {
        document.querySelector("#numCdp").focus();
        mjeError("Debe digitar un número de CRP", "");
        return false;
      }
      // Validar el formulario
      if (formEnvio.get("id_tercero") == "") {
        document.querySelector("#tercero").focus();
        mjeError("Debe seleccionar un tercero valido", "");
        return false;
      }
    }
    fetch("datos/registrar/registrar_crp.php", {
      method: "POST",
      body: formEnvio,
    })
      .then((response) => response.json())
      .then((response) => {
        console.log("respuesta: " + response[0].value);
        if (response[0].value == "ok") {
          console.log(response[0].value);
          mje("Registro guardado exitosamente");
          // redireccionar a lista_ejecucion_pto_crp.php
          id_pto_save.value = response[0].id1;
          let ruta = {
            url: "lista_ejecucion_pto_crp.php",
            name: "id_pto",
            valor: pptou,
          };
          // detener la ejecución de redireccionar
          setTimeout(() => {
            redireccionar(ruta);
          }, 2000);
        } else {
          mjeError("El registro no fue guardado", "");
        }
        let id = "tableEjecCrpNuevo";
        reloadtable(id);
      });
  }
});

// Ver historial de CDP para liquidación de saldos sin ejecutar
const verLiquidarCdp = (id) => {
  fetch("lista_historial_cdp.php", {
    method: "POST",
    body: JSON.stringify({ id: id }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalForms").removeClass("modal-sm");
      $("#divTamModalForms").removeClass("modal-lg");
      $("#divTamModalForms").addClass("modal-xl");
      $("#divModalForms").modal("show");
      divForms.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};
// Ver historial de CDP para liquidación de saldos sin ejecutar
const CargarFormularioLiquidar = (id) => {
  fetch("datos/registrar/form_liquidar_saldo_cdp.php", {
    method: "POST",
    body: JSON.stringify({ id: id }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalForms3").removeClass("modal-lg");
      $("#divTamModalForms3").removeClass("modal-sm");
      $("#divTamModalForms3").addClass("modal-xl");
      $("#divModalForms3").modal("show");
      divForms3.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};
// Ver historial de CDP para liquidación de saldos sin ejecutar
const CargarFormularioLiquidarCrp = (id) => {
  fetch("datos/registrar/form_liquidar_saldo_crp.php", {
    method: "POST",
    body: JSON.stringify({ id: id }),
  })
    .then((response) => response.text())
    .then((response) => {
      $("#divTamModalForms3").removeClass("modal-lg");
      $("#divTamModalForms3").removeClass("modal-sm");
      $("#divTamModalForms3").addClass("modal-xl");
      $("#divModalForms3").modal("show");
      divForms3.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Autocomplete para seleccionar terceros
document.addEventListener("keyup", (e) => {
  if (e.target.id == "tercero") {
    $("#tercero").autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "datos/consultar/buscar_terceros.php",
          type: "POST",
          dataType: "json",
          data: {
            term: request.term,
          },
          success: function (data) {
            response(data);
          },
        });
      },
      select: function (event, ui) {
        $("#tercero").val(ui.item.label);
        $("#id_tercero").val(ui.item.id);
        return false;
      },
      focus: function (event, ui) {
        $("#tercero").val(ui.item.label);
        return false;
      },
    });
  }
});

//========================================================= LIQUIDAR SALDO DE CDP =====================================
// Funcion para liquidar saldo de CDP
const EnviarLiquidarCdp = async (id) => {
  let formEnvio = new FormData(modLiberaCdp2);
  for (var pair of formEnvio.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }
  // validar que concepto este lleno
  if (formEnvio.get("objeto") == "") {
    document.querySelector("#objeto").focus();
    mjeError("Debe digitar un concepto", "");
    return false;
  }
  try {
    const response = await fetch("datos/registrar/registrar_liquidacion_cdp.php", {
      method: "POST",
      body: formEnvio,
    });
    const data = await response.json();
    id_doc_neo.value = data[0].id;
    if (data[0].value == "ok") {
      mje("Registro guardado exitosamente");
    }
    console.log(data);
  } catch (error) {
    console.error(error);
  }
};

// Registra el movimiento de detalle de la liberación de saldo del cdp
const registrarLiquidacionDetalle = async (id) => {
  if (id_doc_neo.value != "") {
    let campo_form = id.split("_");
    let input = document.getElementById("valor" + campo_form[1]);
    let formEnvio = new FormData(modLiberaCdp2);
    formEnvio.append("dato", id);
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
    }
    if (input.value == 0) {
      mjeError("El valor no puede ser cero", "");
      return false;
    }
    if (input.value > input.max) {
      mjeError("El valor no puede ser mayor al saldo", "");
      return false;
    }
    try {
      const response = await fetch("datos/registrar/registrar_liquidacion_cdp_det.php", {
        method: "POST",
        body: formEnvio,
      });
      const data = await response.json();
      console.log(data);
      if (data[0].value == "ok") {
        input.value = data[0].valor;
        input.max = data[0].valor;
        mje("Registro guardado exitosamente");
      }
    } catch (error) {
      console.error(error);
    }
  } else {
    mjeError("Debe registrar el documento con el botón guardar", "");
  }
};

// Eliminar registro de detalle de la liberación de saldo del cdp
const eliminarLiberacion = (id) => {
  Swal.fire({
    title: "¿Está seguro de eliminar el registro?",
    text: "No podrá revertir esta acción",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, eliminar",
    cancelButtonText: "Cancelar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await fetch("datos/eliminar/del_eliminar_liberacion_cdp.php", {
          method: "POST",
          body: JSON.stringify({ id: id }),
        });
        const data = await response.json();
        console.log(data);
        if (data[0].value == "ok") {
          $("#" + id).remove();
          mje("Registro eliminado");
        }
      } catch (error) {
        console.error(error);
      }
    }
  });
};

// ============================================================================================= FIN

// ================================================== REGISTRAR LIQUIDACION DE SALDO DE CRP =====================================
// Funcion para liquidar saldo de CDP
const EnviarLiquidarCrp = async (id) => {
  let formEnvio = new FormData(modLiberaCrp);
  for (var pair of formEnvio.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }
  // validar que concepto este lleno
  if (formEnvio.get("objeto") == "") {
    document.querySelector("#objeto").focus();
    mjeError("Debe digitar un concepto", "");
    return false;
  }
  try {
    const response = await fetch("datos/registrar/registrar_liquidacion_crp.php", {
      method: "POST",
      body: formEnvio,
    });
    const data = await response.json();
    id_doc_neo.value = data[0].id;
    if (data[0].value == "ok") {
      mje("Registro guardado exitosamente");
    }
    console.log(data);
  } catch (error) {
    console.error(error);
  }
};

// Registra el movimiento de detalle de la liberación de saldo del crp
const registrarLiquidacionDetalleCrp = async (id) => {
  console.log(id);

  if (id_doc_neo.value != "") {
    let campo_form = id.split("_");
    let input = document.getElementById("valor" + campo_form[1]);
    let formEnvio = new FormData(modLiberaCrp);
    formEnvio.append("dato", id);
    for (var pair of formEnvio.entries()) {
      console.log(pair[0] + ", " + pair[1]);
    }
    if (input.value == 0) {
      mjeError("El valor no puede ser cero", "");
      return false;
    }
    let valor_libera = parseFloat(input.value.replace(/\,/g, "", ""));
    let valor_max = parseFloat(input.max.replace(/\,/g, "", ""));
    if (valor_libera > valor_max) {
      mjeError("El valor no puede ser mayor al saldo del RP", "");
      return false;
    }
    try {
      const response = await fetch("datos/registrar/registrar_liquidacion_crp_det.php", {
        method: "POST",
        body: formEnvio,
      });
      const data = await response.json();
      console.log(data);
      if (data[0].value == "ok") {
        input.value = data[0].valor;
        input.max = data[0].valor;
        mje("Registro guardado exitosamente");
        let tabla = "tableEjecPresupuesto";
        reloadtable(tabla);
      }
    } catch (error) {
      console.error(error);
    }
  } else {
    mjeError("Debe registrar el documento con el botón guardar", "");
  }
};
// ============================================================================================= FIN

//================================================ ANULACION DE DOCUMENTO =============================================
// Funcion para anular documento
const anulacionCrp = (id) => {
  let url = "form_fecha_anulacion.php";
  $.post(url, { id: id }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-xl");
    $("#divTamModalForms").addClass("modal-lg");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

const anulacionCdp = (id) => {
  let url = "form_fecha_anulacion_cdp.php";
  $.post(url, { id: id }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-xl");
    $("#divTamModalForms").addClass("modal-lg");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

const generarInformeConsulta = (id) => {
  let url = "informes/informe_ejecucion_gas_xls_consulta.php";
  $.post(url, { id: id }, function (he) {
    $("#divTamModalForms").removeClass("modal-sm");
    $("#divTamModalForms").removeClass("modal-lg");
    $("#divTamModalForms").addClass("modal-xl");
    $("#divModalForms").modal("show");
    $("#divForms").html(he);
  });
};

// Enviar datos para anulacion
const changeEstadoAnulacion = async () => {
  let formEnvio = new FormData(formAnulacionCrpp);
  for (var pair of formEnvio.entries()) {
    console.log(pair[0] + ", " + pair[1]);
    // obtener el valor de la etiqueta min del imput fecha
    let fecha_min = document.querySelector("#fecha").getAttribute("min");
    // validar que el value del campo  fecha no sea menor a fecha_min
    if (formEnvio.get("fecha") < fecha_min) {
      mjeError("La fecha no puede ser menor al cierre de periodo", "Fecha permitida: " + fecha_min);
      return false;
    }
  }
  try {
    const response = await fetch("datos/registrar/registrar_anulacion_doc.php", {
      method: "POST",
      body: formEnvio,
    });
    const data = await response.json();
    console.log(data);
    if (data[0].value == "ok") {
      // realizar un case para opciones 1.2.3
      if (data[0].tipo == 1) {
        let tabla = "tableEjecPresupuesto";
        reloadtable(tabla);
      }
      if (data[0].tipo == 2) {
        let tabla = "tableEjecPresupuestoCrp";
        reloadtable(tabla);
      }
      if (data[0].tipo == 3) {
        let tabla = "tableModPresupuesto";
        reloadtable(tabla);
      }
      mje("Anulación guardada con  éxito...");
      // cerrar modal
      $("#divModalForms").modal("hide");
    }
  } catch (error) {
    console.error(error);
  }
};

// ================================================   FIN LIQUIDAR SALDO DE CDP =====================================

const cargarReportePresupuesto = (id) => {
  let url = "";
  if (id == 1) {
    url = "informes/informe_ejecucion_ing_list.php";
  }
  if (id == 2) {
    url = "informes/informe_ejecucion_gas_list.php";
  }
  if (id == 3) {
    url = "informes/informe_ejecucion_gas_libros.php";
  }
  if (id == 4) {
    url = "informes/informe_ejecucion_gas_xls_mes_form.php";
  }
  if (id == 5) {
    url = "informes/informe_ejecucion_ing_xls_mes_form.php";
  }
  if (id == 6) {
    url = "informes/informe_ejecucion_ing_xls_mes_form.php";
  }
  if (id == 7) {
    url = "informes/informe_ejecucion_gas_libros_anula.php";
  }
  fetch(url, {
    method: "POST",
    body: JSON.stringify({ id: id }),
  })
    .then((response) => response.text())
    .then((response) => {
      areaReporte.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Funcion para generar formato de Modificaciones
const generarInforme = (boton) => {
  let id = boton.value;
  let fecha_corte = fecha.value;
  let archivo = '';
  const areaImprimir = document.getElementById("areaImprimir");
  if (id == 1) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls.php";
  }
  if (id == 2) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_ing_xls.php";
  }
  if (id == 3) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls_mes.php";
  }
  if (id == 4) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_ing_xls_mes.php";
  }
  if (id == 5) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_gas_xls_consulta.php";
  }
  boton.disabled = true;
  var span = boton.querySelector("span")
  span.classList.add("spinner-border", "spinner-border-sm");
  fetch(archivo, {
    method: "POST",
    body: fecha_corte,
  })
    .then((response) => response.text())
    .then((response) => {
      boton.disabled = false;
      span.classList.remove("spinner-border", "spinner-border-sm")
      areaImprimir.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
  //redireccionar3(ruta);
};
// Funcion para generar libros presupuestales
const generarInformeLibros = (boton) => {
  let id = boton.value;
  let tipo = tipo_libro.value;
  let fecha_corte = fecha.value;
  let archivo = 0;
  if (tipo == 1) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_cdp_xls.php";
  }
  if (tipo == 2) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_crp_xls.php";
  }
  if (tipo == 3) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_cop_xls.php";
  }
  if (tipo == 4) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_pag_xls.php";
  }
  if (tipo == 5) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_cxp.php";
  }
  if (tipo == 6) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_ft04.php";
  }
  if (tipo == 7) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_cdp_anula_xls.php";
  }
  if (tipo == 8) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_crp_anula_xls.php";
  }
  if (tipo == 9) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_rad_xls.php";
  }
  if (tipo == 10) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_rec_xls.php";
  }
  if (tipo == 11) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_mod_anula_xls.php";
  }
  if (tipo == 13) {
    archivo = window.urlin + "/presupuesto/informes/informe_libro_pag_anula.php";
  }
  if (id == 20) {
    archivo = window.urlin + "/presupuesto/informes/informe_ejecucion_ing_xls.php ";
  }
  boton.disabled = true;
  var span = boton.querySelector("span")
  span.classList.add("spinner-border", "spinner-border-sm");
  areaImprimir.innerHTML = "";
  fetch(archivo, {
    method: "POST",
    body: fecha_corte,
  })
    .then((response) => response.text())
    .then((response) => {
      boton.disabled = false;
      span.classList.remove("spinner-border", "spinner-border-sm")
      areaImprimir.innerHTML = response;
    })
    .catch((error) => {
      console.log("Error:");
    });
};

// Funcion para redireccionar la recarga de la pagina
function redireccionar3(ruta) {
  console.log(ruta);
  setTimeout(() => {
    $(
      '<form action="' +
      ruta.url +
      '" method="post">\n\
    <input type="hidden" name="' +
      ruta.name +
      '" value="' +
      ruta.valor +
      '" />\n\
    </form>'
    )
      .appendTo("body")
      .submit();
  }, 100);
}

const abrirLink = (link) => {
  if (link == 1) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_sia/index.php");
  if (link == 2) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_chip/cgr_ingresos.php");
  if (link == 3) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_chip/cgr_gastos.php");
  if (link == 4) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/ejec_pptal_ing.php");
  if (link == 5) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/ejec_pptal_gastos.php");
  if (link == 6) window.open("http://localhost:3080/2022/USUARIOS_REG/mvto_ppto_gas/relacion_compromisos_corte.php");
  if (link == 7) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/modificaciones_mensual.php");
  if (link == 8) window.open("http://localhost:3080/2022/USUARIOS_REG/mod_informes/modificaciones_mensual_ing.php");
  if (link == 9) window.open("http://localhost:3080/2022/USUARIOS_REG/2193/2193_hom_ing.php");
  if (link == 10) window.open("http://localhost:3080/2022/USUARIOS_REG/2193_gas/2193_hom_ing.php");
  if (link == 11) window.open("http://localhost:3080/2022/USUARIOS_REG/2193/a.php");
  if (link == 12) window.open("http://localhost:3080/2022/USUARIOS_REG/2193_gas/a.php");
  if (link == 13) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_sia/busca_contrato.php");
  if (link == 14) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/libro_auxiliar.php");
  if (link == 15) window.open("http://localhost:3080/2022/USUARIOS_REG/balance_prueba/balance_prueba.php");
  if (link == 16) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/mayor_balance_corte_f.php");
  if (link == 17) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/balance_general_corte.php");
  if (link == 18) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contabilidad/estado_resultados_corte.php");
  if (link == 19) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contaduria_gral/a.php");
  if (link == 20) window.open("http://localhost:3080/2022/USUARIOS_REG/informes_contaduria_gral/cuenta_puntos.php");
  if (link == 21) window.open("");

  // generar funcion numeros para
};
