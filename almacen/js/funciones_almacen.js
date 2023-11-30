(function ($) {
    //Superponer modales
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
    };
    var confdel = function (i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: window.urlin + '/almacen/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    var ConfirmAction = function (msg, id) {
        let btns = '<button class="btn btn-primary btn-sm" id="' + id + '">Aceptar</button>' +
            '<button type="button" class="btn btn-secondary  btn-sm"  data-dismiss="modal">Cancelar</button>'
        $('#divModalConfDel').modal('show');
        $('#divMsgConfdel').html(msg);
        $('#divBtnsModalDel').html(btns);
    };
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ Filas",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
        "zeroRecords": "No se encontraron registros",
        "paginate": {
            "first": "&#10096&#10096",
            "last": "&#10097&#10097",
            "next": "&#10097",
            "previous": "&#10096"
        }
    };
    var setdom;
    if ($("#peReg").val() === '1') {
        setdom = "<'row'<'col-md-5'l><'bttn-plus-dt col-md-2'B><'col-md-5'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    } else {
        setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    }
    function CreaDataTable(id_producto, id_bodega) {
        if ($.fn.DataTable.isDataTable('#tableLotes')) {
            $('#tableLotes').DataTable().destroy();
        };
        $('#tableLotes').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/lotes_producto.php',
                type: 'POST',
                dataType: 'json',
                data: { id_producto: id_producto, id_bodega: id_bodega }
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'lote' },
                { 'data': 'marca' },
                { 'data': 'invima' },
                { 'data': 'fecha' },
                { 'data': 'cantidad' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableLotes').wrap('<div class="overflow" />');
    };
    $(document).ready(function () {
        //dataTable adquisiciones
        $('#tableEntradasAlmacenProveedor').DataTable({

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_entradas_almacen.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_adq' },
                { 'data': 'objeto' },
                { 'data': 'fecha' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableEntradasAlmacenProveedor').wrap('<div class="overflow" />');
        //datatable lista de pedidos
        let tipo_bien = $('#txtTipoBien').val();
        $('#tablePedidos').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_pedido.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_pedidos.php',
                type: 'POST',
                dataType: 'json',
                data: { tipo_bien: tipo_bien },
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'entrega' },
                { 'data': 'solicita' },
                { 'data': 'responsable' },
                { 'data': 'fecha' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tablePedidos').wrap('<div class="overflow" />');
        $('#tableBodegas').DataTable({
            language: setIdioma,
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_bodega.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            "ajax": {
                url: 'datos/listar/datos_bodegas.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'sede' },
                { 'data': 'bodega' },
                { 'data': 'responsable' },
                { 'data': 'fecha' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableBodegas').wrap('<div class="overflow" />');
        $('#tableMarcas').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_marca.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').remove('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_marcas.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'marca' },
                { 'data': 'fecha' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableMarcas').wrap('<div class="overflow" />');
        //datatable detalle de pedido
        let id_pdo = $('#id_pdo').val();
        $('#tableDetallePedido').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("form_reg_detalle_pedido.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: '../listar/datos_detalle_pedidos.php',
                type: 'POST',
                dataType: 'json',
                data: { id_pdo: id_pdo },
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'bien' },
                { 'data': 'lote' },
                { 'data': 'vence' },
                { 'data': 'cantidad' },
                { 'data': 'botones' },
            ],
            "order": [
                [1, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableDetallePedido').wrap('<div class="overflow" />');
        //datatable Confirma entradas
        let ids_confentradas = $('#idsconfentrada').val();
        $('#tableEntradasAlmacen').DataTable({
            language: setIdioma,

            "ajax": {
                url: '../listar/datos_confirmar_entradas.php',
                type: 'POST',
                dataType: 'json',
                data: { ids_confentradas: ids_confentradas },
            },
            "columns": [
                { 'data': 'id_prod' },
                { 'data': 'id_api' },
                { 'data': 'bnsv' },
                { 'data': 'cant_act' },
                { 'data': 'precio' },
                { 'data': 'fec_venc' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableEntradasAlmacen').wrap('<div class="overflow" />');
        //datatable entrada a almacen por prestamos
        let tipo = $('#slctipoEntrada').val();
        $('#tableEntradasAlmacenPresDona').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_entra_prest_dona.php", { tipo: tipo }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_entradas_prestamo.php?tipo=' + tipo,
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_pd' },
                { 'data': 'consecutivo' },
                { 'data': 'ccnit' },
                { 'data': 'nom_completo' },
                { 'data': 'acta_remision' },
                { 'data': 'fec_presta_dona' },
                { 'data': 'detalle' },
                { 'data': 'total' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            columnDefs: [{
                class: 'text-wrap',
                targets: [3, 4, 6]
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableEntradasAlmacenPresDona').wrap('<div class="overflow" />');
        //datatable entrada a almacen por prestamos o donación
        let id_pd = $('#id_prestdonac').val();
        $('#tableRegPresDona').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("form_add_prest_dona.php", { id_pd: id_pd }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                },
            }],

            language: setIdioma,
            "ajax": {
                url: '../listar/datos_entradas_pres_dona.php',
                type: 'POST',
                dataType: 'json',
                data: { id_pd: id_pd },
            },
            "columns": [
                { 'data': 'id_pd' },
                { 'data': 'bien_servi' },
                { 'data': 'cant_ingresa' },
                { 'data': 'valu_ingresa' },
                { 'data': 'iva' },
                { 'data': 'subtotalsiniva' },
                { 'data': 'subtotalconiva' },
                { 'data': 'lote' },
                { 'data': 'fecha_vence' },
                { 'data': 'botones' },
            ],
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableRegPresDona').wrap('<div class="overflow" />');
        $('.bttn-excel button').html('<span class="fas fa-file-excel fa-lg"></span>');
        $('.bttn-excel').attr('title', 'Exportar a Excel');
        //dataTable lista de devoluciones
        let id_tipo = $('#tipoSalida').val();
        $('#tableListDevoluciones').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_devolucion.php", { id_tipo: id_tipo }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_lista_devoluciones.php',
                type: 'POST',
                dataType: 'json',
                data: { id_tipo: id_tipo }
            },
            "columns": [
                { 'data': 'id_devolucion' },
                { 'data': 'consecutivo' },
                { 'data': 'tercero' },
                { 'data': 'acta' },
                { 'data': 'fec_acta' },
                { 'data': 'observacion' },
                { 'data': 'estado' },
                { 'data': 'accion' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableListDevoluciones').wrap('<div class="overflow" />');
        //dataTable lista de detalles devoluciones
        let id_devDetalles = $('#id_dev_det').length > 0 ? $('#id_dev_det').val() : 0;
        let id_tipo_sal_det = $('#id_tipo_sal_det').val();
        $('#tableDetallesDevolucion').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("../datos/registrar/form_add_detalles_devolucion.php", { id_tipo_sal_det: id_tipo_sal_det }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: '../datos/listar/datos_lista_detalles_devolucion.php',
                type: 'POST',
                dataType: 'json',
                data: { id_devDetalles: id_devDetalles }
            },
            "columns": [
                { 'data': 'id_prod' },
                { 'data': 'prod' },
                { 'data': 'cantidad' },
                { 'data': 'lote' },
                { 'data': 'fec_vence' },
                { 'data': 'accion' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableDetallesDevolucion').wrap('<div class="overflow" />');
        //listar kardex por artículo
        let id_articulo = $('#id_articulo').val();
        let id_tipo_B = $('#id_tipo_B').val();
        let bodega = $('#slcBodega').val();
        let id_marca = $('#slcMarcaXprod').val();
        $('#tableKardexArticulo').wrap('<div class="overflow" />');
        //listar kardex por artículo
        let t_traslado = $('#tipo_trasl_alm').val();
        $('#tableTrasladosAlmacen').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_traslados_almacen.php", { t_traslado: t_traslado }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_traslados_almacen.php',
                type: 'POST',
                dataType: 'json',
                data: { t_traslado: t_traslado }
            },
            "columns": [
                { 'data': 'id_traslado' },
                { 'data': 'tipo' },
                { 'data': 'sede_sale' },
                { 'data': 'bodega_sale' },
                { 'data': 'sede_entra' },
                { 'data': 'bodega_entra' },
                //{ 'data': 'acta' },
                //{ 'data': 'observacion' },
                { 'data': 'fecha' },
                { 'data': 'acciones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableTrasladosAlmacen').wrap('<div class="overflow" />');
        //dataTable detalles de traslado
        let id_trasl_alma = $('#id_up_tra_alm').val();
        $('#tableDetallesTraslado').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("../datos/registrar/form_add_detalles_traslado.php", { id_trasl_alma: id_trasl_alma }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: '../datos/listar/datos_lista_detalles_traslado.php',
                type: 'POST',
                dataType: 'json',
                data: { id_trasl_alma: id_trasl_alma }
            },
            "columns": [
                { 'data': 'id_prod' },
                { 'data': 'prod' },
                { 'data': 'cantidad' },
                { 'data': 'lote' },
                { 'data': 'observacion' },
                { 'data': 'accion' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableDetallesTraslado').wrap('<div class="overflow" />');
        //listar datos por artículo y lote
        let desc_lote = $('#desc_lote').val();
        $('#tableAjusteInventario').DataTable({

            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_lote_ajustar.php',
                type: 'POST',
                dataType: 'json',
                data: { desc_lote: desc_lote },
            },
            "columns": [
                { 'data': 'fec_reg' },
                { 'data': 'sede' },
                { 'data': 'bodega' },
                { 'data': 'remision' },
                { 'data': 'tercero' },
                { 'data': 'entrada' },
                { 'data': 'salida' },
                { 'data': 'cantidad' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableAjusteInventario').wrap('<div class="overflow" />');
    });
    $('#divForms').on('click', '#regEntraXPrestDona', function () {
        //$('#').val() == ''
        if ($('#compleTerecero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tercero');
        } else if ($('#id_tercero_pd').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar una tercero válido');
        } else if ($('#numActaRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el número de acta y/o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar fecha de acta y/o remisión');
        } else {
            let datos = $('#formRegEntraPrestDona').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/entrada_prest_dona.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableEntradasAlmacenPresDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro realizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    $('#divForms').on('click', '#addEntraXPrestDona', function () {
        if ($('#buscProd').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar un bien y/o producto');
        } else if (parseInt($('#numCantRecb').val()) <= 0 || $('#numCantRecb').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Cantidad debe ser mayor a cero');
        } else if (parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor unitario debe ser mayor o igual a cero');
        } else {
            let datos = $('#formAddEntraPrestDona').serialize();
            $.ajax({
                type: 'POST',
                url: '../../registrar/add_prest_dona_kardex.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableRegPresDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro realizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    $('#existencia_lote').on('focus', function () {
        if ($('#tot_existe_lote').length) {
            let valor = $('#tot_existe_lote').val();
            $('#existencia_lote').val(valor);
        }
        return false;
    });
    $('#modificarEntradasAlmacen').on('click', '.detalles', function () {
        let ids = $(this).attr('value');
        $.post("datos/listar/datos_num_entradas.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '.confirmar_entrega_almacen', function () {
        let ids = $(this).attr('value');
        $.post("datos/registrar/inicia_entrega.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnRegEntraAlmacen', function () {
        if ($('#numActaRem').val() == '' && $('#numFactura').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un número de acta o factura*');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de acta, remisión o factura+');
        } else {
            var datos = $('#formRegEntraAlmacen').serialize();
            var idf = $('#identificador').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_header_entrada_almacen.php',
                dataType: 'json',
                data: datos,
                success: function (r) {
                    if (r.status == 1) {
                        let ids = idf + '|' + r.msg;
                        $('<form action="datos/registrar/confirma_entradas.php" method="post"><input type="hidden" name="ids" value="' + ids + '" /></form>').appendTo('body').submit();
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r.msg);
                    }

                }
            });
        }
        return false;
    });
    $('#divForms').on('click', '.confirmar_entrega_almac', function () {
        let ids = $(this).attr('value');
        $('<form action="datos/registrar/confirma_entradas.php" method="post"><input type="hidden" name="ids" value="' + ids + '" /></form>').appendTo('body').submit();
    });
    $('#divForms').on('click', '.aprobarEAlmacen', function () {
        let id = $(this).attr('value');
        $("#rechazar_" + id).remove();
        $("#aprobar_" + id).addClass('deshabilitar');
        $('#divForms button').removeAttr('disabled');
        $('#divForms button').addClass('gchanges');
        $("#estado_" + id).val('1');
        return false;
    });
    $('#divForms').on('click', '.rechazarEAlmacen', function () {
        let id = $(this).attr('value');
        $("#aprobar_" + id).remove();
        $("#rechazar_" + id).addClass('deshabilitar');
        $('#entrega_' + id).removeClass('oculto');
        $('#entrega_' + id).attr('name', 'entrega_' + id);
        $('#divForms button').removeAttr('disabled');
        $('#divForms button').addClass('gchanges');
        $("#estado_" + id).val('2');
        return false;
    });
    $('#divModalReg').on('click', '.gchanges', function () {
        var aprobar = 1;
        var maximo = 0;
        $('input[type=number]').each(function () {
            var min = parseInt($(this).attr('min'));
            var max = parseInt($(this).attr('max'));
            var val = $(this).val().length ? parseInt($(this).val()) : 'NO';
            maximo += val;
            $(this).removeClass('border-danger');
            if (val == 'NO') {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max) + ' válido');
            } else if (val < min || val > max) {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max));
            }
            if (aprobar == 0) {
                return false;
            }
        });
        $('input[name="txtMarca[]"]').each(function () {
            var elemento = $(this).parent().parent();
            var val = $(this).val();
            var aprobar = 1;
            if (Number(val) == 0) {
                aprobar = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Valor de Marca no válido');
            }
            if (aprobar == 0) {
                return false;
            }
        });
        var cantmax = parseInt($('#numCantMax').val());
        if (cantmax < maximo) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad total debe ser menor o igual a ' + cantmax);
            return false;
        }
        $('input[type=text]').each(function () {
            var val = $(this).val().length ? parseInt($(this).val()) : 'NO';
            $(this).removeClass('border-danger');
            if (val == 'NO') {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Se debe registrar este campo');
            }
            if (aprobar == 0) {
                return false;
            }
        });
        $('input[type=date]').each(function () {
            var val = $(this).val().length ? new Date($(this).val()) : 'NO';
            var fecmin = new Date($('#dateFecMin').val());
            $(this).removeClass('border-danger');
            if (val == 'NO') {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Se debe registrar este campo');
            } else if (val < fecmin) {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha de vencimiento debe ser mayor a ' + fecmin.toISOString().split('T')[0]);
            }
            if (aprobar == 0) {
                return false;
            }
        });
        if (aprobar == 1) {
            let datos = $('#formCantRegAlmacen').serialize();
            let encabezados = $('#formEncabEntraAlmacen').serialize();
            let id_contrato = $('#id_contrato').val();
            datos = datos + '&' + encabezados + '&id_contrato=' + id_contrato;
            $.ajax({
                type: 'POST',
                url: '../../actualizar/up_kardex.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableEntradasAlmacen';
                        reloadtable(id_t);
                        $('#divModalReg').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Kardex actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    /*
    $("#tableEntradasAlmacen").on('dblclick', 'tr', function (e) {
        e.preventDefault();
        if ($('#txtNoRemEntrada').val() == '' && $('#txtNoFactEntrada').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un numero de factura o remisión');
            return false;
        }
        var campos = '';
        $(this).children("td").each(function () {
            campos += $(this).text() + '|';
        })
        if (campos != '') {
            $.post("form_reg_entrada.php", { campos: campos }, function (he) {
                $('#divTamModalReg').removeClass('modal-2x');
                $('#divTamModalReg').removeClass('modal-xl');
                $('#divTamModalReg').removeClass('modal-sm');
                $('#divTamModalReg').addClass('modal-lg');
                $('#divModalReg').modal('show');
                $("#divFormsReg").html(he);
            });
        }
    });*/
    $("#divModalReg").on('click', '.buscaMarca', function () {
        let elemento = $(this).parent();
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../../datos/listar/marcas.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                elemento.find('input[name="txtMarca[]"]').val(ui.item.id);
            }
        });
    });
    $("#divModalForms").on('input', '#txtMarcaI', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../../datos/listar/busca_marca.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#idMarcaI').val(ui.item.id);
            }
        });
    });
    $("#tableEntradasAlmacen").on('click', 'tr .recepcionar', function (e) {
        e.preventDefault();
        var datostr = $(this).parent().parent()
        var otro = datostr.parent();
        var campos = '';
        $(otro).children("td").each(function () {
            campos += $(this).text() + '|';
        })
        let id_entra = $('#id_entrada').val();
        $.post("form_reg_entrada.php", { campos: campos, id_entra: id_entra }, function (he) {
            $('#divTamModalReg').removeClass('modal-2x');
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });
    $('#divModalReg').on('click', '#btnMasEntradas', function () {
        var html = '';
        var valuni = $('#valorUnitario').val();
        var fecmin = $('#dateFecMin').val();
        var cantmax = $('#numCantMax').val();
        html += '<div class="form-row">';
        html += '<div class="form-group col-md-2">';
        html += '<input type="number" name="cantidad[]" class="form-control form-control-sm" min="0" max="' + cantmax + '">';
        html += '</div>';
        html += '<div class="form-group col-md-2">';
        html += '<input class="form-control form-control-sm" value="' + valuni + '" readonly>';
        html += ' </div>';
        html += ' <div class="form-group col-md-3">';
        html += '<input type="text" name="lote[]" class="form-control form-control-sm">';
        html += ' </div>';
        html += '<div class="form-group col-md-2">';
        html += '<input type="date" name="fec_vence[]" class="form-control form-control-sm" min="' + fecmin + '">';
        html += '</div>';
        html += '<div class="form-group col-md-3">';
        html += '<div class="input-group input-group-sm">';
        html += '<input type="text" class="form-control buscaMarca">';
        html += '<input type="hidden" name="txtMarca[]" value="0">';
        html += '<div class="input-group-append">';
        html += '<a class="btn btn-outline-danger delinputs"><span class="fas fa-minus-circle fa-lg"></span></a>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        $('#divMasInputs').append(html);

    });
    $('#divModalReg').on('click', '.delinputs', function () {
        $(this).parent().parent().parent().parent().remove();
    });
    $('#slctipoEntrada').on('change', function () {
        let tipo = $(this).val();
        $('<form action="lista_entradas.php" method="post"><input type="hidden" name="tipo" value="' + tipo + '" /></form>').appendTo('body').submit();
    });
    $("#buscarArticulo").on('input', function () {
        var tipoB = $('#slctipoBusqueda').val();
        var sede = $('#slcSede').val();
        var bodega = $('#slcBodega').val();
        if (tipoB == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de busqueda');
            return false;
        }
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'POST',
                    url: "datos/listar/datos_seleccionar_articulo.php",
                    dataType: "json",
                    data: {
                        term: request.term,
                        tipoB: tipoB
                    },
                    success: function (data) {
                        return response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                let id = ui.item.id;
                $("#id_articulo").val(id);
                $.ajax({
                    type: 'POST',
                    url: "datos/listar/marcas.php",
                    data: { id: id },
                    success: function (data) {
                        $('#slcMarcaXprod').html(data);
                    }
                });
            }
        });
    });
    $('#btnGeneraKardex').on('click', function () {
        var tipoB = $('#slctipoBusqueda').val();
        var sede = $('#slcSede').val();
        var bodega = $('#slcBodega').val();
        var articulo = $('#id_articulo').val();
        var describe = $('#buscarArticulo').val();
        var fec1 = $('#fecha1').val();
        var fec2 = $('#fecha2').val();
        var marca = $('#slcMarcaXprod').val();
        if (sede == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una sede');
        } else if (bodega == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una bodega');
        } else if (tipoB == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de busqueda');
        } else if (articulo == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un articulo');
        } else {
            $('<form action="kardex.php" method="post">' +
                '<input type="hidden" name="tipo" value="' + tipoB + '" />' +
                '<input type="hidden" name="describe" value="' + describe + '" />' +
                '<input type="hidden" name="articulo" value="' + articulo + '" />' +
                '<input type="hidden" name="id_marca" value="' + marca + '" />' +
                '<input type="hidden" name="sede" value="' + sede + '" />' +
                '<input type="hidden" name="bodega" value="' + bodega + '" />' +
                '<input type="hidden" name="fecha1" value="' + fec1 + '" />' +
                '<input type="hidden" name="fecha2" value="' + fec2 + '" />' +
                '</form>').appendTo('body').submit();
        }
    });
    $('#btnImprimeKardex').on('click', function () {
        var tipoB = $('#slctipoBusqueda').val();
        var sede = $('#slcSede').val();
        var bodega = $('#slcBodega').val();
        var articulo = $('#id_articulo').val();
        var describe = $('#buscarArticulo').val();
        if (sede == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una sede');
        } else if (bodega == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una bodega');
        } else if (tipoB == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de busqueda');
        } else if (articulo == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un articulo');
        } else {
            $.post('informes/imp_kardex.php', { articulo: articulo, bodega: bodega }, function (he) {
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $("#divModalForms").on('input', '#buscaBienAlmacen', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'POST',
                    url: window.urlin + "/almacen/datos/listar/datos_producto_almacen.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        return response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_prod').val(ui.item.id);
                $('#numCanProd').focus();
            }
        });
    });
    $("#buscarArticuloAjIn").on('input', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'POST',
                    url: "datos/listar/datos_articulo_ajuste_inventario.php",
                    dataType: "json",
                    data: {
                        term: request.term,
                    },
                    success: function (data) {
                        return response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_prod_ajuste').val(ui.item.id);
                $('#desc_prod_ajuste').val(ui.item.label);
                $('#buscarLoteAjIn').focus();
            }
        });
    });
    $("#buscarLoteAjIn").on('input', function () {
        let id_prod = $('#id_prod_ajuste').val();
        let desc_pro = $('#desc_prod_ajuste').val();
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'POST',
                    url: "datos/listar/datos_lote_ajuste_inventario.php",
                    dataType: "json",
                    data: {
                        term: request.term,
                        id_prod: id_prod
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                let datos = ui.item.id + '|' + ui.item.label + '|' + id_prod + '|' + desc_pro;
                $('<form action="ajuste_inventario.php" method="post"><input type="hidden" name="datos" value="' + datos + '" /></form>').appendTo('body').submit();
            }
        });
    });
    $("#divModalForms").on('input', '#buscProd', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../listar/datos_bien_servicio.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_bnsvc').val(ui.item.id);
            }
        });
    });
    $(".searchArticle").on('input', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/almacen/datos/listar/datos_bien_servicio.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $(this).parent().find('.valArt').val(ui.item.id);
                $(this).parent().find('span').html(ui.item.id);
                let id_3 = $('#idArtc3').val();
                if (id_3 > 0) {
                    $.ajax({
                        type: 'POST',
                        url: window.urlin + "/almacen/datos/listar/existencias.php",
                        data: { id_3: id_3 },
                        success: function (r) {
                            $('#existencias').html(r);
                        }
                    });
                } else {
                    $('#existencias').html('');
                }
            }
        });
    });
    $("#divForms").on('input', '#muestraExistencias', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../datos/listar/datos_bien_servicio.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                let id_3 = ui.item.id;
                $('#id_prod').val(id_3);
                if (id_3 > 0) {
                    $.ajax({
                        type: 'POST',
                        url: '../datos/listar/existencias.php',
                        data: { id_3: id_3 },
                        success: function (r) {
                            $('#existencias').html(r);
                        }
                    });
                } else {
                    $('#existencias').html('xs');
                }
            }
        });
    });
    $("#listaLotes").on('click', function () {
        var id_prod = $('#id_articulo').val();
        var id_bodega = $('#slcBodega').val();
        if (id_prod > 0 && id_bodega > 0) {
            CreaDataTable(id_prod, id_bodega);
        } else {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un elemento de la lista deplegable');
        }
    });
    $("#btnUnificaArtc").on('click', function () {
        var id_prod1 = $('#idArtc1').val();
        var id_prod2 = $('#idArtc2').val();
        $('.is-invalid').removeClass('is-invalid');
        if (id_prod1 == '0') {
            $('#articulo1').focus();
            $('#articulo1').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un artículo principal');
        } else if (id_prod2 == '0') {
            $('#articulo2').focus();
            $('#articulo2').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un artículo a unificar');
        } else if (id_prod1 == id_prod2) {
            $('#articulo1').focus();
            $('#articulo2').addClass('is-invalid');
            $('#articulo1').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los artículos deben ser diferentes');
        } else {
            let msg = "¿Está seguro de unificar los artículos?. <br><b>Esta acción no se puede deshacer</b>";
            let id = 'btnConfirmAcction';
            ConfirmAction(msg, id);
        }
    });
    $('#divBtnsModalDel').on('click', '#btnConfirmAcction', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'actualizar/unifica_articulo.php',
            data: { id_prod1: id_prod1, id_prod2: id_prod2 },
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Artículos unificados correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $("#btnTransformaArt").on('click', function () {
        var id_prod3 = $('#idArtc3').val();
        var id_prod4 = $('#idArtc4').val();
        $('.is-invalid').removeClass('is-invalid');
        if (id_prod3 == '0') {
            $('#articulo3').focus();
            $('#articulo3').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un artículo a transformar');
        } else if (id_prod4 == '0') {
            $('#articulo4').focus();
            $('#articulo4').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un artículo para transformar');
        } else if (id_prod3 == id_prod4) {
            $('#articulo3').focus();
            $('#articulo4').addClass('is-invalid');
            $('#articulo3').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los artículos deben ser diferentes');
        } else if ($('#numArt4').val() == '' || parseInt($('#numArt4').val() < 1)) {
            $('#numArt4').focus();
            $('#numArt4').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar la cantidad por cada unidad transformada');
        } else if (!($('input[name="radTransfor"]:checked').length)) {
            $('#tipoT').focus();
            $('#tipoT').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar el tipo de transformación');
        } else {
            var boton = $(this);
            let aprobar = 1;
            var suma = 0;
            if ($('.xdisponiblex').length) {
                $('.xdisponiblex').each(function () {
                    var valor = Number($(this).val());
                    var nan = ($(this).val());
                    var max = Number($(this).attr('max'));
                    if (nan == '') {
                        aprobar = 0;
                        $(this).focus();
                        $(this).addClass('is-invalid');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe indicar la cantidad por cada unidad transformada');
                    } else if (valor < 0 || valor > max) {
                        aprobar = 0;
                        $(this).focus();
                        $(this).addClass('is-invalid');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('El valor debe ser mayor a 0 y menor o igual a ' + max);
                    }
                    if (aprobar == 0) {
                        return false;
                    } else {
                        suma = suma + valor;
                    }
                });

            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para el producto actual no existen cantidades disponibles');
            }
            if (aprobar == 1 && suma > 0) {
                boton.find('i').addClass('fa-spin');
                boton.attr('disabled', true);
                let msg = "¿Está seguro de Transformar este artículo?. <br><b>Esta acción no se puede deshacer</b>";
                let id = 'btnConfirTrans';
                ConfirmAction(msg, id);
            } else {
                $('.xdisponiblex').focus();
                $('.xdisponiblex').addClass('is-invalid');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Revisar Cantidades, por lo menos debe ser 1');
            }
            boton.find('i').removeClass('fa-spin');
            boton.attr('disabled', false);
        }
        return false;
    });
    $('#divBtnsModalDel').on('click', '#btnConfirTrans', function () {
        let datos = $('#formExisteTransform').serialize();
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/almacen/actualizar/transformar_articulo.php',
            data: datos,
            success: function (r) {
                if (r == 'ok') {
                    $('#articulo3').val('');
                    $('#idArtc3').val('0');
                    $('#articulo4').val('');
                    $('#numArt4').val('');
                    $('#idArtc4').val('0');
                    $('#basic-addon3').html(' ');
                    $('#basic-addon4').html(' ');
                    $('#existencias').html('');
                    let id = 'tableRegPresDona';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Artículos unificados correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $("#divModalForms").on('input', '#compleTerecero', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/almacen/datos/listar/datos_terceros.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_tercero_pd').val(ui.item.id);
                $('#numActaRemDev').focus();
            }
        });
    });
    $('#modificarEntradasAlmacenPresDon').on('click', '.detalles', function () {
        let id_pd = $(this).attr('value');
        $('<form action="datos/registrar/entradas_prestamo_donacion.php" method="post"><input type="hidden" name="id_pd" value="' + id_pd + '" /></form>').appendTo('body').submit();
    });
    $('#modificarEntradasAlmacenPresDon').on('click', '.editar', function () {
        let id_pd = $(this).attr('value');
        let tipo = $('#slctipoEntrada').val();
        $.post("datos/actualizar/form_up_entra_prest_dona.php", { id_pd: id_pd, tipo: tipo }, function (he) {
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    $('#divModalForms').on('click', '#upEntraXPrestDona', function () {
        //$('#').val() == ''
        if ($('#compleTerecero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un tercero');
        } else if ($('#id_tercero_pd').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un tercero válido');
        } else if ($('#numActaRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el número de acta y/o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar fecha de acta y/o remisión');
        } else {
            let datos = $('#formRegEntraPrestDona').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_entrada_pres_dona.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableEntradasAlmacenPresDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro realizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    $('#modificarEntradasAlmacenPresDon').on('click', '.borrar', function () {
        let id_pd = $(this).attr('value');
        let tip = 'PresDona';
        confdel(id_pd, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelPresDona', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_pres_dona.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableEntradasAlmacenPresDona';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Orden eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#tableRegPresDona').on('click', '.editar', function () {
        let id_entrada = $(this).attr('value');
        $.post("../actualizar/form_up_presdon.php", { id_entrada: id_entrada }, function (he) {
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#modEntraXPrestDona', function () {
        if ($('#buscProd').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar un bien y/o producto');
        } else if (parseInt($('#numCantRecb').val()) <= 0 || $('#numCantRecb').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Cantidad debe ser mayor a cero');
        } else if (parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor unitario debe ser mayor o igual a cero');
        } else {
            let datos = $('#formUpEntraPrestDona').serialize();
            $.ajax({
                type: 'POST',
                url: '../../actualizar/mod_prest_dona_kardex.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableRegPresDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    $('#tableRegPresDona').on('click', '.borrar', function () {
        let id_pd = $(this).attr('value');
        let tip = 'DetEntradaPrDo';
        confdel(id_pd, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelDetEntradaPrDo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../../eliminar/del_detalle_pres_dona.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableRegPresDona';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Detalle de entrada eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnCerrarPreDon').on('click', function () {
        var table = $('#tableRegPresDona').DataTable();
        let filas = table.rows().count();
        if (filas <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Imposible cerrar, no se ha agregado ningun elemento');
            return false;
        }
        let id_pd = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: '../../actualizar/mod_estado_predon.php',
            data: { id_pd: id_pd },
            success: function (r) {
                if (r == '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Se ha cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#slctipoSalida').on('change', function () {
        let tipo = $(this).val();
        $('<form action="lista_salidas.php" method="post"><input type="hidden" name="tipo" value="' + tipo + '" /></form>').appendTo('body').submit();
    });
    //buscar por lote
    $('#divModalForms').on('input', '#numLoteDev', function () {
        var tercero = $('#id_terdev').val();
        var tipo_sal = parseInt($('#id_tipo_sal_det').val());
        if (tipo_sal != 1) {
            tercero = 0;
        }
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../datos/listar/datos_lotes.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term, tercero: tercero },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_proDev').val(ui.item.id);
                $('#numCantDev').attr('max', ui.item.max);
                $('#id_entrada_dev').attr('value', ui.item.id_entrada);
                $('#numCantDev').focus();
            }
        });
    });
    //buscar lote por sede y bodega
    $('#divModalForms').on('input', '#numLoteSedeBodega', function () {
        var sede = $('#id_sede_sale').val();
        var bodega = $('#id_bodega_sale').val();
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../datos/listar/datos_lotes_sede_bodega.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term, sede: sede, bodega: bodega },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_proTras').val(ui.item.id);
                $('#numCantTras').attr('max', ui.item.max);
                $('#id_entrada_Tras').attr('value', ui.item.id_entrada);
                $('#numCantTras').focus();
            }
        });
    });
    //registrar devolución 
    $('#divModalForms').on('click', '#btnAddSalidaXDevol', function () {
        var validar = 1;
        if ($('#id_tercero_pd').length) {
            if ($('#compleTerecero').val() == '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe ingresar un tercero');
                validar = 0;
            } else if ($('#id_tercero_pd').val() == '0') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('No se ha ingresado ningun tercero válido');
                validar = 0;
            }
            if (validar == 0) {
                return false;
            }
        } else {
            if ($('#slcFianza').val() == '0') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe seleccionar una Entrada Fianza');
                return false;
            }
        }
        if ($('#numActaRemDev').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de acta o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de la acta o remisión para este registro');
        } else {
            let rem = $('#formAddSalidaXDevol').serialize();
            $.ajax({
                type: 'POST',
                url: "registrar/new_salida_X_devolucion.php",
                data: rem,
                success: function (r) {
                    if (r == 1) {
                        let id = 'tableListDevoluciones';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#modificarSalidaXDev').html('');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //actualizar o modificar devolucion
    $('#modificarSalidaXDev').on('click', '.editar', function () {
        let id_dev = $(this).attr('value');
        $.post("datos/actualizar/form_up_devolucion.php", { id_dev: id_dev }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnActSalidaXDevol', function () {
        if ($('#compleTerecero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un tercero');
        } else if ($('#id_tercero_pd').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha ingresado ningun tercero válido');
        } else if ($('#numActaRemDev').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de acta o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de la acta o remisión para este registro');
        } else {
            let rem = $('#formActSalidaXDevol').serialize();
            $.ajax({
                type: 'POST',
                url: "actualizar/up_salida_X_devolucion.php",
                data: rem,
                success: function (r) {
                    if (r == 1) {
                        let id = 'tableListDevoluciones';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#modificarSalidaXDev').html('');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Rregistrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //eliminar devolucion
    $('#modificarSalidaXDev').on('click', '.borrar', function () {
        let id_dev = $(this).attr('value');
        let tip = 'SalXDevol';
        confdel(id_dev, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelSalXDevol', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_salxdevolucion.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableListDevoluciones';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Orden de entrada eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //detalles devolucion
    $('#modificarSalidaXDev').on('click', '.detalles', function () {
        let id_dev = $(this).attr('value');
        $('<form action="registrar/detalles_devolucion.php" method="post"><input type="hidden" name="id_dev" value="' + id_dev + '" /></form>').appendTo('body').submit();
    });
    //agregar detalles devolucion
    $('#divModalForms').on('click', '#btnAddDetallesDevol', function () {
        $('.is-invalid').removeClass('is-invalid');
        var boton = $(this);
        var aprobar = 1;
        var suma = 0;
        if ($('.xdisponiblex').length) {
            $('.xdisponiblex').each(function () {
                var valor = Number($(this).val());
                var nan = ($(this).val());
                var max = Number($(this).attr('max'));
                if (nan == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe indicar la cantidad por cada unidad transformada');
                } else if (valor < 0 || valor > max) {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El valor debe ser mayor a 0 y menor o igual a ' + max);
                }
                if (aprobar == 0) {
                    return false;
                } else {
                    suma = suma + valor;
                }
            });

        } else {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Para el producto actual no existen cantidades disponibles');
        }
        if (aprobar == 1 && suma > 0) {
            boton.find('i').addClass('fa-spin');
            boton.attr('disabled', true);
            let detalle = $('#formAddDetalleDevol').serialize();
            let masdet = $('#formDatosDevolucion').serialize();
            let datos = detalle + '&' + masdet
            $.ajax({
                type: 'POST',
                url: '../registrar/new_detalle_devolucion_kardex.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableDetallesDevolucion';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Orden de entrada agregada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });

        } else {
            $('.xdisponiblex').focus();
            $('.xdisponiblex').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Revisar Cantidades, por lo menos debe ser 1');
        }
        boton.find('i').removeClass('fa-spin');
        boton.attr('disabled', false);
    });
    //actualizar detalles devoluvión
    $('#modificarDetalleDev').on('click', '.editar', function () {
        let id_sal = $(this).attr('value');
        $.post("../datos/actualizar/form_up_detalles_devolucion.php", { id_sal: id_sal }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //agregar detalles devolucion
    $('#divModalForms').on('click', '#btnUpDetallesDevol', function () {
        if ($('#numLoteDev').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de lote');
        } else if ($('#id_proDev').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha ingresado un número de lote válido');
        } else if ($('#numCantDev').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número mayor a cero');
        } else {
            let max = parseInt($('#numCantDev').attr('max'));
            let min = parseInt($('#numCantDev').attr('min'));
            let valor = parseInt($('#numCantDev').val());
            if (valor < min || valor > max) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Cantidad debe estar entre ' + min + ' y ' + max);
            } else {
                let detalle = $('#formAddDetalleDevol').serialize();
                let masdet = $('#formDatosDevolucion').serialize();
                let datos = detalle + '&' + masdet
                $.ajax({
                    type: 'POST',
                    url: '../actualizar/up_detalle_devolucion_kardex.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableDetallesDevolucion';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Orden de entrada actualizada correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //borrar detalle de devolución
    $('#modificarDetalleDev').on('click', '.borrar', function () {
        let id_dev = $(this).attr('value');
        let tip = 'DetalleDevol';
        confdel(id_dev, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelDetalleDevol', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../eliminar/del_detalle_devolucion.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableDetallesDevolucion';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Detalle eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnCerrarDevolucion').on('click', function () {
        var table = $('#tableDetallesDevolucion').DataTable();
        let filas = table.rows().count();
        if (filas <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Imposible cerrar, no se ha agregado ningun elemento');
            return false;
        }
        let id_dev = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: '../actualizar/mod_estado_devolucion.php',
            data: { id_dev: id_dev },
            success: function (r) {
                if (r == '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Se ha cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#slctipoTraslado').on('change', function () {
        let op = $(this).val();
        $('<form action="traslados.php" method="post"><input type="hidden" name="t_traslado" value="' + op + '" /></form>').appendTo('body').submit();
    });
    $('#divModalForms').on('change', '#slcSedeSalida', function () {
        let sede = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'datos/listar/bodegas.php',
            data: { sede: sede },
            success: function (r) {
                $('#slcBodegaSalida').html(r);
            }
        });
        return false;
    });
    $('#divModalForms').on('change', '#slcSedeEntrada', function () {
        let sede = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'datos/listar/bodegas.php',
            data: { sede: sede },
            success: function (r) {
                $('#slcBodegaEntrada').html(r);
            }
        });
        return false;
    });
    //registrar traslado
    $('#divModalForms').on('click', '#btnTraslados', function () {
        if ($('#slcSedeSalida').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una sede de salida');
        } else if ($('#slcBodegaSalida').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una bodega de salida');
        } else if ($('#slcSedeEntrada').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una sede de entrada');
        } else if ($('#slcBodegaEntrada').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una bodega de entrada');
        } else if ($('#numActaRemTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de acta o remisión');
        } else if ($('#fecActRemTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de la acta o remisión para este registro');
        } else {
            if ($('#id_tipo_traslado').val() == '1' && $('#slcSedeSalida').val() == $('#slcSedeEntrada').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de sedes, la sede de salida y entrada deben ser diferentes');
                return false;
            } else if ($('#id_tipo_traslado').val() == '2' && $('#slcSedeSalida').val() != $('#slcSedeEntrada').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de Bodegas, la sede de salida y entrada deben ser iguales');
                return false;
            } else if ($('#slcBodegaEntrada').val() == $('#slcBodegaSalida').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de bodegas, la bodega de salida y entrada deben ser diferentes');
            } else {
                let traslado = $('#formAddTrasaldoAlmacen').serialize();
                $.ajax({
                    type: 'POST',
                    url: "registrar/new_traslado_almacen.php",
                    data: traslado,
                    success: function (r) {
                        if (r == 1) {
                            let id = 'tableTrasladosAlmacen';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#modificarSalidaXDev').html('');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Registrado correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    //Actualizar o modificar traslados
    $('#modificarTrasladosAlmacen').on('click', '.editar', function () {
        let id_tras = $(this).attr('value');
        $.post("datos/actualizar/form_up_traslado_almacen.php", { id_tras: id_tras }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //up traslado
    $('#divModalForms').on('click', '#btnUpTraslados', function () {
        if ($('#slcBodegaSalida').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una bodega de salida');
        } else if ($('#slcBodegaEntrada').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe Seleccionar una bodega de entrada');
        } else if ($('#numActaRemTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de acta o remisión');
        } else if ($('#fecActRemTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de la acta o remisión para este registro');
        } else {
            if ($('#id_tipo_traslado').val() == '1' && $('#slcSedeSalida').val() == $('#slcSedeEntrada').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de sedes, la sede de salida y entrada deben ser diferentes');
                return false;
            } else if ($('#id_tipo_traslado').val() == '2' && $('#slcSedeSalida').val() != $('#slcSedeEntrada').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de Bodegas, la sede de salida y entrada deben ser iguales');
                return false;
            } else if ($('#slcBodegaEntrada').val() == $('#slcBodegaSalida').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para traslados de bodegas, la bodega de salida y entrada deben ser diferentes');
            } else {
                let traslado = $('#formUpTrasaldoAlmacen').serialize();
                $.ajax({
                    type: 'POST',
                    url: "actualizar/up_traslado_almacen.php",
                    data: traslado,
                    success: function (r) {
                        if (r == 1) {
                            let id = 'tableTrasladosAlmacen';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#modificarSalidaXDev').html('');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Registrado correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    //borrar traslado
    $('#modificarTrasladosAlmacen').on('click', '.borrar', function () {
        let id_ta = $(this).attr('value');
        let tip = 'TraslAlmacen';
        confdel(id_ta, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelTraslAlmacen', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_traslado_almacen.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableTrasladosAlmacen';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Traslado eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#modificarTrasladosAlmacen').on('click', '.detalles', function () {
        let id_tr_al = $(this).attr('value');
        $('<form action="registrar/detalles_traslado_almacen.php" method="post"><input type="hidden" name="id_traslado" value="' + id_tr_al + '" /></form>').appendTo('body').submit();
    });
    //agregar detalles traslados
    $('#divModalForms').on('click', '#btnAddDetallesTrasl', function () {
        if ($('#numLoteSedeBodega').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de lote');
        } else if (parseInt($('#id_proTras').val()) == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha ingresado un número de lote válido');
        } else if ($('#numCantTras').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número mayor a cero');
        } else {
            let max = parseInt($('#numCantTras').attr('max'));
            let min = parseInt($('#numCantTras').attr('min'));
            let valor = parseInt($('#numCantTras').val());
            if (valor < min || valor > max) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Cantidad debe estar entre ' + min + ' y ' + max);
            } else {
                let detalle = $('#formAddDetalleTrasl').serialize();
                let masdet = $('#formDatosTraslado').serialize();
                let datos = detalle + '&' + masdet
                $.ajax({
                    type: 'POST',
                    url: '../registrar/new_detalle_traslado.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableDetallesTraslado';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Detalle agregado correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //actualizar detalles de traslado
    $('#tableDetallesTraslado').on('click', '.editar', function () {
        let id_trasl = $(this).attr('value');
        $.post("../datos/actualizar/form_up_detalles_traslado.php", { id_trasl: id_trasl }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //agregar detalles traslados
    $('#divModalForms').on('click', '#btnUpDetallesTrasl', function () {
        if ($('#numLoteSedeBodega').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de lote');
        } else if ($('#id_proTras').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha ingresado un número de lote válido');
        } else if ($('#numCantTras').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número mayor a cero');
        } else {
            let max = parseInt($('#numCantTras').attr('max'));
            let min = parseInt($('#numCantTras').attr('min'));
            let valor = parseInt($('#numCantTras').val());
            if (valor < min || valor > max) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Cantidad debe estar entre ' + min + ' y ' + max);
            } else {
                let detalle = $('#formUpDetalleTrasl').serialize();
                $.ajax({
                    type: 'POST',
                    url: '../actualizar/up_detalle_traslado.php',
                    data: detalle,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableDetallesTraslado';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Detalle actualizado correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //borrar detalles traslado
    $('#modificarDetalleTraslados').on('click', '.borrar', function () {
        let id_tras = $(this).attr('value');
        let tip = 'TraslSedes';
        confdel(id_tras, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelTraslSedes', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../eliminar/del_detalle_traslado.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableDetallesTraslado';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Detalle eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnCerrarTraslado').on('click', function () {
        var table = $('#tableDetallesTraslado').DataTable();
        let filas = table.rows().count();
        if (filas <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Imposible cerrar, no se ha agregado ningun elemento');
            return false;
        }
        let id_tras = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: '../actualizar/mod_estado_traslado.php',
            data: { id_tras: id_tras },
            success: function (r) {
                if (r == '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Se ha cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnAjustarCantidad').on('click', function () {
        if ($('#buscarArticuloAjIn').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un nombre de artículo');
        } else if ($('#id_prod_ajuste').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un nombre de artículo válido');
        } else if ($('#buscarLoteAjIn').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de lote');
        } else if ($('#id_Enlote_ajuste').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de lote válido');
        } else if ($('#existencia_lote').val() == '' || parseInt($('#existencia_lote').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número mayor o igual a cero');
        } else if (parseInt($('#tot_existe_lote').val()) == parseInt($('#existencia_lote').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada es igual a la existencia del lote');
        } else {
            let datos = $('#formAjustarCantidad').serialize() + '&existe_lote_ant=' + $('#tot_existe_lote').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_ajustar_cantidad.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Cantidad ajustada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#tableEntradasAlmacenPresDona').on('click', '.certificaPDF', function () {
        let id = $(this).attr('value');
        $('<form action="datos/soportes/certifica_entrada.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#selTipoBien').on('change', function () {
        let id = $(this).val();
        $('<form action="lista_pedidos.php" method="post"><input type="hidden" name="tipo_bien" value="' + id + '" /></form>').appendTo('body').submit();

    });
    $('#divModalForms').on('change', '#idArea', function () {
        let id = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'datos/listar/resp_bodega.php',
            data: { id: id },
            success: function (r) {
                $('#id_resposable').val(r);
            }
        });
    });
    $('#divForms').on('click', '#btnRegPedido', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#idAreaEntrega').val() == '0') {
            $('#idAreaEntrega').addClass('is-invalid');
            $('#idAreaEntrega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un área que entrega');
        } else if ($('#idAreaPide').val() == '0') {
            $('#idAreaPide').addClass('is-invalid');
            $('#idAreaPide').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un área que pide');
        } else if ($('#idAreaPide').val() == $('#idAreaEntrega').val()) {
            $('#idAreaPide').addClass('is-invalid');
            $('#idAreaPide').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('El área que pide y entrega deben ser diferentes');
        } else {
            let datos = $('#formRegPedido').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_pedido.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        let idT = "tablePedidos";
                        reloadtable(idT);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Encabezado de pedido registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#divForms').on('click', '#btnRegDetPedido', function () {
        if ($('#id_prod').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar elemento válido');
        } else if ($('#numCanProd').val() == '' || Number($('#numCanProd').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad válida');
        } else {
            let datos = $('#formRegDetPedido').serialize();
            let id_pdo = $('#id_pdo').val();
            datos = datos + '&id_pdo=' + id_pdo;
            $.ajax({
                type: 'POST',
                url: '../../registrar/new_detalle_pedido.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let idT = "tableDetallePedido";
                        reloadtable(idT);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Detalle de pedido registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#divForms').on('click', '#btnUpDetPedido', function () {
        if ($('#id_prod').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar elemento válido');
        } else if ($('#numCanProd').val() == '' || Number($('#numCanProd').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad válida');
        } else {
            let datos = $('#formUpDetPedido').serialize();
            let id_pdo = $('#id_pdo').val();
            datos = datos + '&id_pdo=' + id_pdo;
            $.ajax({
                type: 'POST',
                url: '../../actualizar/up_detalle_pedido.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let idT = "tableDetallePedido";
                        reloadtable(idT);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Detalle de pedido actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#divForms').on('click', '#btnUpPedido', function () {
        let datos = $('#formUpPedido').serialize();
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_pedido.php',
            data: datos,
            success: function (r) {
                if (r == '1') {
                    let idT = "tablePedidos";
                    reloadtable(idT);
                    $('#divModalForms').modal('hide');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Encabezado de pedido Actualizado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $('#accionesPedidos').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post('datos/actualizar/form_up_pedido.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#accionesPedidos').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'PdoAlmacen';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelPdoAlmacen', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_pedido.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tablePedidos';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Pedido eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#accionesPedidos').on('click', '.detalles', function () {
        let id = $(this).attr('value');
        $('<form action="datos/registrar/detalles_pedido.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#accionDetallePedido').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post('../../datos/actualizar/form_up_detalle_pedido.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    function ModCantidad(elemento) {
        if (elemento.hasClass('fa-box')) {
            elemento.removeClass('fa-box');
            elemento.addClass('fa-box-open');
            elemento.parent().removeClass('btn-outline-secondary');
            elemento.parent().addClass('btn-outline-success');
            elemento.parent().parent().parent().find('input').removeAttr('readonly');
        } else {
            elemento.removeClass('fa-box-open');
            elemento.addClass('fa-box');
            elemento.parent().removeClass('btn-outline-success');
            elemento.parent().addClass('btn-outline-secondary');
            elemento.parent().parent().parent().find('input').attr('readonly', 'true');
        }
    }
    $('#accionDetallePedido').on('click', '.editcantidad', function () {
        let elemento = $(this).find('span');
        ModCantidad(elemento);
        return false;
    });
    $('#tableDetallesDevolucion').on('click', '.editcantidad', function () {
        let elemento = $(this).find('span');
        ModCantidad(elemento);
        return false;
    });
    $('#accionDetallePedido').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'DetallesPedido';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelDetallesPedido', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../../eliminar/del_detalle_pedido.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableDetallePedido';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Detalle eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnCerrarPedido').on('click', function () {
        let filas = $('#tableDetallePedido').DataTable().rows().count();
        if (filas == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("No se puede cerrar un pedido sin detalles");
        } else {
            let id = $('#id_pdo').val();
            $(this).attr('disabled', 'true');
            $(this).find('span').addClass('spinner-border spinner-border-sm');
            var elemento = $(this);
            $.ajax({
                type: 'POST',
                url: '../../actualizar/up_cerrar_pedido.php',
                data: { id: id },
                success: function (r) {
                    if (r == '1') {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Pedido cerrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            elemento.attr('disabled', 'false');
            elemento.find('span').removeClass('spinner-border');
            elemento.find('span').removeClass('spinner-border-sm');
        }
        return false;
    });
    $('#btnEntregaPedido').on('click', function () {
        let procede = $(this).attr('value');
        var url, form;
        if (procede === 'FIANZA') {
            url = '../registrar/det_salida_fianza.php';
            form = 'formDetSalidaFianza';
        } else {
            url = '../../actualizar/up_pedido_traslado.php';
            form = 'formCantProdPedido';
        }
        var aprobar = 1;
        $('input[type=number]').each(function () {
            var maximos = $(this).attr('max').split('|');
            var min = parseInt($(this).attr('min'));
            var max = parseInt(maximos[0]);
            var maxms = parseInt(maximos[1]);
            var val = $(this).val().length ? parseInt($(this).val()) : 'NO';
            var id_prod = $(this).attr('prod');
            $(this).removeClass('border-danger');
            if (val == 'NO') {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max) + ' válido');
            } else if (val < min || val > max) {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max));
            } else if (val > maxms) {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (maxms));
            } else {
                var total = Number($('#' + id_prod).val());
                var suma = 0;
                $('input[prod="' + id_prod + '"]').each(function () {
                    var valor = Number($(this).val());
                    suma += valor;

                });
                if (suma > total) {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('La suma de los valores no puede ser mayor a ' + total);
                }
            }
            if (aprobar == 0) {
                return false;
            }
        });
        if (aprobar == 1) {
            $(this).attr('disabled', 'true');
            $(this).find('span').addClass('spinner-border spinner-border-sm');
            var elemento = $(this);
            let datos = $('#' + form).serialize();
            $.ajax({
                type: 'POST',
                url: url,
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Realizado correctamente");
                    } else {
                        elemento.attr('disabled', 'false');
                        elemento.find('span').removeClass('spinner-border');
                        elemento.find('span').removeClass('spinner-border-sm');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '.informe', function () {
        let id = $(this).attr('value');
        var ruta = '';
        if (id == '1') {
            ruta = window.urlin + '/almacen/informes/imp_control_existencia';
        }
        if (id == '2') {
            ruta = window.urlin + '/almacen/informes/imp_control_consumo';
        }
        if (id == '3') {
            ruta = window.urlin + '/almacen/informes/imp_existencia_producto';
        }
        if (id == '4') {
            ruta = window.urlin + '/almacen/informes/imp_inventario_fisico';
        }
        if (id == '5') {
            ruta = window.urlin + '/almacen/informes/imp_entrada_x_tercero';
        }
        if (id == '6') {
            ruta = window.urlin + '/almacen/informes/imp_traslado_multiple';
        }
        if (id == '7') {
            ruta = window.urlin + '/almacen/informes/imp_consec_traslado';
        }
        if (id == '8') {
            ruta = window.urlin + '/almacen/informes/imp_vencimiento';
        }
        $.post(ruta + '.php', function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnReporteGral', function () {
        let xls = ($('#areaImprimir').html());
        var encoded = window.btoa(xls);
        $('<form action="' + window.urlin + '/almacen/informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });
    $('#divModalForms').on('click', '#excelPlano', function () {
        let xls = ($('#tbPlano').html());
        var encoded = window.btoa(xls);
        $('<form action="' + window.urlin + '/almacen/informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });
    $('#divModalForms').on('click', '#btnExcelEntrada', function () {
        let xls = ($('#areaImprimir').html());
        var encoded = window.btoa(xls);
        $('<form action="informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });
    $('#btnImprimirPedido').on('click', function () {
        let id = $('#id_pdo').val();
        $.post('../../informes/imp_pedido.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#accionesPedidos').on('click', '.imprimir', function () {
        let id = $(this).attr('value');
        $.post('informes/imp_pedido.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#btnImprimirTraslado').on('click', function () {
        let id = $('#id_pdo').val();
        $.post('../../informes/imp_traslado.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#tableTrasladosAlmacen').on('click', '.btnImprimirTraslado', function () {
        let id = $(this).attr('value');
        $.post('informes/imp_traslado.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#btnImprimirConsumo').on('click', function () {
        let id = $('#id_pdo').val();
        $.post('../../informes/imp_consumo.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#tableListDevoluciones').on('click', '.btnImprimirConsumo', function () {
        let id = $(this).attr('value');
        var url;
        if ($('#slctipoSalida').val() == 7) {
            url = 'informes/imp_consumo.php';
        } else {
            url = 'informes/imp_salida';
        }
        $.post(url, { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#consumoXfechas', function () {
        let id = $('#id_pdo').val();
        let fecha1 = $('#fecha1').val();
        let fecha2 = $('#fecha2').val();
        $.post(window.urlin + '/almacen/informes/imp_consumo.php', { id: id, fecha1: fecha1, fecha2: fecha2 }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#kardeXfecha', function () {
        let articulo = $('#id_articulo').val();
        let bodega = $('#slcBodega').val();
        let fecha1 = $('#fecha11').val();
        let fecha2 = $('#fecha22').val();
        let id_mrc = $('#slcMarcaProd').val();
        $.post('informes/imp_kardex.php', { articulo: articulo, bodega: bodega, fecha1: fecha1, fecha2: fecha2, id_mrc: id_mrc }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificarEntradasAlmacenPresDon').on('click', '.imprimir', function () {
        let id = $(this).attr('value');
        $.post('informes/imp_entrada.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#tableEntradasAlmacenPresDona').on('click', '.recibir', function () {
        var id = $(this).attr('value');
        $(this).attr('value', '0');
        $(this).attr('disabled', 'disabled');
        $(this).attr('title', 'Recibiendo...');
        $(this).find('span').removeClass('fa-download');
        $(this).find('span').addClass('spinner-border spinner-border-sm');
        var elemento = $(this);
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/entradaxfarmacia.php',
            data: { id: id },
            success: function (r) {
                if (r == 'ok') {
                    let id = 'tableEntradasAlmacenPresDona';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Entrada recibida correctamente");
                } else {
                    elemento.attr('value', id);
                    elemento.attr('disabled', false);
                    elemento.attr('title', 'Recibir');
                    elemento.find('span').addClass('fa-download');
                    elemento.find('span').removeClass('spinner-border');
                    elemento.find('span').removeClass('spinner-border-sm');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $('#tableDetallePedido').on('click', '.editConsumo', function () {
        $('.editConsumo').attr('disabled', true);
        let id = $(this).attr('value');
        let elemeto = $(this).parent().parent().find('input');
        let valor = elemeto.val();
        let max = elemeto.attr('max');
        elemeto.removeClass('border-danger');
        if (Number(valor) > Number(max)) {
            elemeto.addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html("El valor no puede ser mayor a " + max);
        } else if (Number(valor) < 1) {
            elemeto.addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html("El valor no puede ser menor a 1");
        } else {
            let pedido = $('#id_pdo').val();
            $.ajax({
                type: 'POST',
                url: '../../actualizar/up_consumo_pedido.php',
                data: { pedido: pedido, id: id, valor: valor },
                success: function (r) {
                    if (r == 'ok') {
                        let table = 'tableDetallePedido';
                        reloadtable(table);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Consumo actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        $('.editConsumo').attr('disabled', false);
        return false;
    });
    $('#btnConsumirPedido').on('click', function () {
        var boton = $(this);
        var aprobar = 1;
        $('input[type=number]').each(function () {
            var min = parseInt($(this).attr('min'));
            var max = parseInt($(this).attr('max'));
            var val = $(this).val().length ? parseInt($(this).val()) : 'NO';
            $(this).removeClass('border-danger');
            if (val == 'NO') {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max) + ' válido');
            } else if (val < min || val > max) {
                aprobar = 0;
                $(this).focus();
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max));
            }
            if (aprobar == 0) {
                return false;
            }
        });
        if (aprobar == 1) {
            let datos = $('#formCantProdPedido').serialize();
            boton.attr('disabled', 'true');
            datos = datos + '&pedido=' + $('#id_pdo').val();
            $.ajax({
                type: 'POST',
                url: '../../actualizar/up_consumo_pedido_masivo.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        let table = 'tableDetallePedido';
                        reloadtable(table);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Consumos actualizado correctamente");
                    } else {
                        boton.prop('disabled', '');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaBodega').on('click', '.asignaResposable', function () {
        let id = $(this).attr('value');
        $.post('datos//registrar/form_add_responsable.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificaBodega').on('click', '.gestionCuentas', function () {
        let id = $(this).attr('value');
        $.post('datos//registrar/form_gestiona_cuentas.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('input', '#buscaUserResposable', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "datos/listar/usuarios.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $("#id_user_resp").val(ui.item.id);
            }
        });
    });
    $('#divModalForms').on('click', '#btnRegResposable', function () {
        if ($('#id_user_resp').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar un usuario");
        } else {
            let datos = $('#formRegResposable').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_responsable.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        let id = 'tableBodegas';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Responsable asignado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }
    });
    $('#divModalForms').on('click', '#btnActCtasContables', function () {
        var aprobar = 1;
        $('.is-invalid').removeClass('is-invalid');
        $('input[name^=numCuenta]').each(function () {
            let val = $(this).val();
            //console.log('Valor de la cuenta:', val);
            if (val == '') {
                aprobar = 0;
                $(this).addClass('is-invalid');
                $(this).focus();
                $('#divModalError').modal('show');
                $('#divMsgError').html('Cuenta no puede ser vacío');
                return false;
            }
        });

        if (aprobar == 1) {
            let datos = $('#formGesCuentas').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/cuentas_contables.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        let id = 'tableBodegas';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Cuentas modificadas correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnImprimir', function () {
        function imprSelec() {
            var div = $('#areaImprimir').html();
            var ventimp = window.open(' ', '');
            ventimp.document.write('<!DOCTYPE html><html><head><title>Imprimir</title></head><body>');
            ventimp.document.write('<div>' + div + '</div>');
            ventimp.document.write('</body></html>');
            ventimp.print();
            ventimp.close();
        }
        $('#divModalForms .collapse').addClass('show');
        imprSelec();
    });
    $('#divModalForms').on('click', '#btnGenInfExistencias', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#sede').val() == '0') {
            $('#sede').addClass('is-invalid');
            $('#sede').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una sede");
        } else if ($('#bodega').val() == '0') {
            $('#bodega').addClass('is-invalid');
            $('#bodega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una bodega");
        } else {
            let data = $('#formInfExiste').serialize();
            $.post(window.urlin + '/almacen/informes/imp_control_existencia.php', data, function (he) {
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $('#divModalForms').on('click', '#btnGenInvFisico', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#sedeif').val() == '0') {
            $('#sedeif').addClass('is-invalid');
            $('#sedeif').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una sede");
        } else if ($('#bodega').val() == '0') {
            $('#bodega').addClass('is-invalid');
            $('#bodega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una bodega");
        } else {
            let data = $('#formInvFisico').serialize();
            $.post(window.urlin + '/almacen/informes/imp_inventario_fisico.php', data, function (he) {
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $('#divModalForms').on('click', '#btnGenInfVence', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#sedevence').val() == '0') {
            $('#sedevence').addClass('is-invalid');
            $('#sedevence').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una sede");
        } else if ($('#bodega').val() == '0') {
            $('#bodega').addClass('is-invalid');
            $('#bodega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe seleccionar una bodega");
        } else {
            let data = $('#formInfExiste').serialize();
            $.post(window.urlin + '/almacen/informes/imp_vencimiento.php', data, function (he) {
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $('#divModalForms').on('change', '#sede', function () {
        let sede = $(this).val();
        $.post(window.urlin + '/almacen/informes/imp_control_existencia.php', { sede: sede }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('change', '#sedeif', function () {
        let sede = $(this).val();
        $.post(window.urlin + '/almacen/informes/imp_inventario_fisico.php', { sede: sede }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('change', '#sedevence', function () {
        let sede = $(this).val();
        $.post(window.urlin + '/almacen/informes/imp_vencimiento.php', { sede: sede }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#listInfAlmacen').on('click', function () {
        $.post(window.urlin + '/almacen/informes/listado.php', function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#slcSede').on('change', function () {
        let sede = $(this).val();
        let bodega = $('#slcBodega').val();
        let tipo = $('#slctipoBusqueda').val();
        let articulo = $('#id_articulo').val();
        let id_marca = $('#slcMarcaXprod').val();
        let describe = $('#buscarArticulo').val();
        $('<form action="kardex.php" method="post">' +
            '<input type="hidden" name="sede" value="' + sede + '" />' +
            '<input type="hidden" name="bodega" value="' + bodega + '" />' +
            '<input type="hidden" name="tipo" value="' + tipo + '" />' +
            '<input type="hidden" name="articulo" value="' + articulo + '" />' +
            '<input type="hidden" name="id_marca" value="' + id_marca + '" />' +
            '<input type="hidden" name="describe" value="' + describe + '" />' +
            '</form>').appendTo('body').submit();
    });
    $('#divModalForms').on('click', '#btnFiltraConsumo', function () {
        let inicia = $("#fecInicia").val();
        let fin = $("#fecFin").val();
        let check = $('input[name=consolidado]:checked').val();
        $.post(window.urlin + '/almacen/informes/imp_control_consumo.php', { inicia: inicia, fin: fin, check: check }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnLisEntradaXTercero', function () {
        let datos = $('#formMoVXTercero').serialize();
        $.post(window.urlin + '/almacen/informes/imp_entrada_x_tercero.php', datos, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('change', '#slcTipoMv', function () {
        let tipo = $('#slcTipoMv').val();
        $.ajax({
            type: 'POST',
            url: window.urlin + '/almacen/datos/listar/tipo_mvto.php',
            data: { tipo: tipo },
            success: function (r) {
                $('#slcMovimiento').html(r)
            }
        });
    });
    $('#divModalForms').on('click', '#btnListTraslMult', function () {
        let inicia = $("#idInicia").val();
        let fin = $("#idFinal").val();
        let fec_inicia = $("#fecInicia").val();
        let fec_final = $("#fecFinal").val();
        let tipo = $("input[name='categoria']:checked").val();
        if (inicia > fin && tipo == 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El ID inicial debe ser menos que el ID Final');
        }
        $.post(window.urlin + '/almacen/informes/imp_traslado_multiple.php', {
            inicia: inicia, fin: fin, fec_inicia: fec_inicia, fec_final: fec_final, tipo: tipo
        }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#filtraBusqueda', function () {
        let id = $("#id_prod").val();
        let nombre = $("#buscaBienAlmacen").val();
        $.post(window.urlin + '/almacen/informes/imp_existencia_producto.php', { id: id, nombre: nombre }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('change', '#slcVigenia', function () {
        let vigencia = $("#slcVigenia").val();
        $.post(window.urlin + '/almacen/informes/imp_consec_traslado.php', { vigencia: vigencia }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#btnCerrarConsumo').on('click', function () {
        let id_pdo = $('#id_pdo').val();
        $.ajax({
            type: 'POST',
            url: '../../actualizar/cerrar_consumo.php',
            data: { id_pdo: id_pdo },
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Consumo cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                    $('#btnCerrarConsumo').html('Cerrado');
                    $('#btnCerrarConsumo').attr('disabled', false);
                }
            }
        });
    });
    $('#btnCerrarSalida').on('click', function () {
        let id_salida = $('#id_dev_det').val();
        $.ajax({
            type: 'POST',
            url: '../actualizar/cerrar_salida.php',
            data: { id_salida: id_salida },
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Salida cerrada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                    $('#btnCerrarConsumo').html('Cerrado');
                    $('#btnCerrarConsumo').attr('disabled', true);
                }
            }
        });
    });
    $('#accionesPedidos').on('click', '.anular', function () {
        let idPedido = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_pedido.php',
            data: { idPedido: idPedido },
            success: function (r) {
                if (r == 1) {
                    let id = 'tablePedidos';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Pedido anulado correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $('#divModalForms').on('click', '#btnRegMarca', function () {
        if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El nombre de la marca no puede estar vacio');
        } else {
            let datos = $('#formRegMarca').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_marca.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        let idT = "tableMarcas";
                        reloadtable(idT);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Marca registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaLotes').on('dblclick', '.insertInput', function () {
        let padre = $(this).parent();
        padre.addClass('py-0');
        padre.find('div').remove();
        padre.find('.ajustar').attr('type', 'number');
    });
    $('#btnAjusteLotes').on('click', function () {
        $('.form-control').removeClass('border-danger');
        if ($('.ajustar').length) {
            //codicional para validar que las inputs con name="ajuste[] no este vacias
            let aprobar = 1;
            let total = 0;
            $('.ajustar').each(function () {
                if ($(this).val() < 0 || $(this).val() == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El valor no puede estar vacio o ser menor a 0');
                    return false;
                } else {
                    total += Number($(this).val());
                }
            });
            if (aprobar == 1) {
                let total2 = $('#total').val();
                if (total == total2) {
                    let datos = $('#formAjusteLotes').serialize();
                    $.ajax({
                        type: 'POST',
                        url: 'actualizar/up_lotes.php',
                        data: datos,
                        success: function (r) {
                            if (r == 'ok') {
                                var id_prod = $('#id_articulo').val();
                                var id_bodega = $('#slcBodega').val();
                                CreaDataTable(id_prod, id_bodega);
                                $('#divModalDone').modal('show');
                                $('#divMsgDone').html('Lotes actualizados correctamente');
                            } else {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html(r);
                            }
                        }
                    });
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Revisar los cambios, la suma total debe ser igual a  ' + total2);
                }
            }
        } else {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe elegir un artículo o producto');
        }
    });
    $('#divModalForms').on('click', '#btnRegBodega', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcIdSede').val() == '0') {
            $('#slcIdSede').addClass('is-invalid');
            $('#slcIdSede').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una sede');
        } else if ($('#txtNewBodega').val() == '') {
            $('#txtNewBodega').addClass('is-invalid');
            $('#txtNewBodega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar el nombre de la bodega');
        } else if ($('#id_user_resp').val() == '0') {
            $('#buscaUserResposable').addClass('is-invalid');
            $('#buscaUserResposable').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un responsable');
        } else {
            let datos = $('#formRegBodega').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_bodega.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        var id = 'tableBodegas';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Bodega registrada correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#divModalForms').on('click', '#btnUpBodega', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcIdSede').val() == '0') {
            $('#slcIdSede').addClass('is-invalid');
            $('#slcIdSede').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una sede');
        } else if ($('#txtNewBodega').val() == '') {
            $('#txtNewBodega').addClass('is-invalid');
            $('#txtNewBodega').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar el nombre de la bodega');
        } else {
            let datos = $('#formUpBodega').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_bodega.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        var id = 'tableBodegas';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Bodega actualizad correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaBodega').on('click', '.editaBodega', function () {
        let id = $(this).attr('value');
        $.post('datos/actualizar/form_up_bodega.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
})(jQuery);