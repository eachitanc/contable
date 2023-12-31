<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$vigencia = $_SESSION['vigencia'];
$id_cop_add = $_POST['id_cop_add'] ?? 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos FROM seg_pto_presupuestos WHERE id_pto_tipo = 2 AND vigencia = '$_SESSION[vigencia]'";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
        seg_ctb_doc.id_ctb_doc
        , seg_ctb_doc.id_manu as causacion 
        , seg_ctb_doc.id_tercero
        , seg_ctb_doc.fecha
        , seg_pto_documento.id_manu as registro
        ,  contratacion.num_contrato
        , SUM(seg_pto_mvto.valor)  AS valor 
        , IFNULL(pagado.pagos,0) AS val_pagado
    FROM	
        seg_ctb_doc
        INNER JOIN seg_pto_mvto ON (seg_ctb_doc.id_ctb_doc = seg_pto_mvto.id_ctb_doc)
        INNER JOIN seg_pto_documento ON (seg_pto_mvto.id_pto_doc = seg_pto_documento.id_pto_doc)
        LEFT JOIN (
        SELECT 
            SUM(seg_pto_mvto.valor) AS pagos 
            ,seg_pto_mvto.id_ctb_cop
        FROM seg_pto_mvto 
        INNER JOIN seg_ctb_doc ON (seg_ctb_doc.id_ctb_doc = seg_pto_mvto.id_ctb_cop)
        WHERE seg_pto_mvto.tipo_mov ='PAG' AND seg_pto_mvto.estado <5
        GROUP BY seg_ctb_doc.id_ctb_doc
        ) AS pagado ON (seg_ctb_doc.id_ctb_doc = pagado.id_ctb_cop)
        LEFT JOIN (
            SELECT DISTINCT
            seg_contrato_compra.num_contrato
            ,seg_pto_mvto.id_ctb_doc
        FROM
        seg_pto_mvto
        INNER JOIN seg_adquisiciones ON (seg_pto_mvto.id_auto_dep = seg_adquisiciones.id_cdp)
        INNER JOIN seg_contrato_compra ON (seg_contrato_compra.id_compra = seg_adquisiciones.id_adquisicion)
        ) AS contratacion ON (seg_ctb_doc.id_ctb_doc = contratacion.id_ctb_doc)
    WHERE seg_pto_mvto.tipo_mov ='COP' AND seg_ctb_doc.estado =1 AND seg_ctb_doc.vigencia =$vigencia
    GROUP BY seg_ctb_doc.id_ctb_doc;";
    $sql2 = $sql;
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableObligacionesPago').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
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
            },
        },
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableObligacionesPago').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REGISTROS PARA PAGOS DEL TERCERO OTROS SI </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableObligacionesPago" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 13%;">Causación</th>
                        <th style="width: 13%;">Rp</th>
                        <th style="width: 13%;">Num Contrato</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 10%;">Cc / Nit</th>
                        <th style="width: 20%;">Terceros</th>
                        <th style="width: 15%;">Valor</th>

                        <th style="width: 5%;">Acciones</th>

                    </tr>
                </thead>
                <tbody>
                    <?php

                    $id_t = [];
                    foreach ($listado as $rp) {
                        if ($rp['id_tercero'] !== null) {
                            $id_t[] = $rp['id_tercero'];
                        }
                    }
                    $payload = json_encode($id_t);
                    //API URL
                    $url = $api . 'terceros/datos/res/lista/terceros';
                    $ch = curl_init($url);
                    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $terceros = json_decode($result, true);


                    foreach ($listado as $ce) {

                        $id_doc = $ce['id_ctb_doc'];
                        $fecha = date('Y-m-d', strtotime($ce['fecha']));
                        // Consulta terceros en la api

                        $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero'));
                        $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
                        $ccnit = $terceros[$key]['cc_nit'];

                        // fin api terceros

                        $saldo_rp = $ce['valor'] - $ce['val_pagado'];

                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="cargarListaDetallePago(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }

                        if ($saldo_rp > 0) {
                    ?>
                            <tr>
                                <td class="text-left"><?php echo $ce['causacion']; ?></td>
                                <td class="text-left"><?php echo $ce['registro'] ?></td>
                                <td class="text-left"><?php echo $ce['num_contrato']   ?></td>
                                <td class="text-left"><?php echo $fecha; ?></td>
                                <td class="text-left"><?php echo $ccnit; ?></td>
                                <td class="text-left"><?php echo $tercero; ?></td>
                                <td class="text-right"> <?php echo number_format($saldo_rp, 2, ',', '.'); ?></td>

                                <td class="text-center"> <?php echo $editar; ?></td>
                            </tr>
                    <?php
                        }
                    }

                    ?>

                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Procesar lote</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
$cmd = null;
