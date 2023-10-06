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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `seg_tes_causa_arqueo`.`id_causa_arqueo`
                ,`seg_ctb_doc`.`id_ctb_doc`
                ,`seg_ctb_doc`.`id_manu`
                , `seg_ctb_doc`.`fecha`
                , `seg_ctb_doc`.`id_tercero`
                , `seg_ctb_doc`.`detalle`
                , SUM(`seg_tes_causa_arqueo`.`valor_arq`) as valor
            FROM
                `seg_tes_causa_arqueo`
                INNER JOIN `seg_ctb_doc` 
                    ON (`seg_tes_causa_arqueo`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                    WHERE (`seg_tes_causa_arqueo`.`estado` =0) 
            GROUP BY `seg_ctb_doc`.`id_manu`;";
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
            <h5 style="color: white;">LISTA DE ARQUEOS DE CAJA PENDIENTE CONSIGNACION</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableObligacionesPago" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 8%;">Num </th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 35%;">Tercero</th>
                        <th style="width: 15%;">Doc</th>
                        <th style="width: 20%;">Valor</th>
                        <th style="width: 10%;">Acciones</th>

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

                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="cargarListaArqueoConsignacion(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }
                    ?>
                        <tr>
                            <td class="text-left"><?php echo $ce['id_manu']  ?></td>
                            <td class="text-left"><?php echo $fecha;  ?></td>
                            <td class="text-left"><?php echo $tercero;   ?></td>
                            <td class="text-left"><?php echo $ccnit; ?></td>
                            <td class="text-right"><?php echo number_format($ce['valor'], 2, ',', '.') ?></td>
                            <td class=" text-center"> <?php echo $editar .  $acciones; ?></td>
                        </tr>
                    <?php
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
