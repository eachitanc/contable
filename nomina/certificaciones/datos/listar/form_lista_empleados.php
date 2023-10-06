<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , `seg_empleado`.`no_documento`
                , CONCAT_WS(' ', `seg_empleado`.`nombre1`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`) AS `nombre`
                , SUM(`seg_liq_dias_lab`.`cant_dias`) AS `dias_lab`
                , `seg_terceros`.`id_tercero_api`
                , `seg_liq_dias_lab`.`anio`
            FROM
                `seg_liq_dias_lab`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_dias_lab`.`id_empleado` = `seg_empleado`.`id_empleado`)
                INNER JOIN `seg_terceros` 
                ON (`seg_terceros`.`no_doc` = `seg_empleado`.`no_documento`)
            WHERE `seg_liq_dias_lab`.`anio` = '$vigencia' AND `seg_liq_dias_lab`.`cant_dias` > 0
            GROUP BY `seg_empleado`.`id_empleado`
            ORDER BY `seg_empleado`.`no_documento`";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<script>
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
    setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    $('#listaEmpleadosCertificar').DataTable({
        language: setIdioma,
        dom: setdom,
        "pageLength": 100,
        "order": [
            [0, "desc"]
        ]
    });
    $('#listaEmpleadosCertificar').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GENERAR CERTIFICADOS FORMULARIO 220</h5>
        </div>
        <form id="formGenForm220">
            <div class="pt-2 px-2">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecInicia" class="small">Inicia</label>
                        <input type="date" class="form-control form-control-sm" id="fecInicia" name="fecInicia">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecFin" class="small">Termina</label>
                        <input type="date" class="form-control form-control-sm" id="fecFin" name="fecFin">
                    </div>
                </div>
                <table id="listaEmpleadosCertificar" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                    <thead>
                        <tr>
                            <th>
                                <div class="text-center"><input type="checkbox" id="selectAll" class="check" title="Desmarcar todos" checked></div>
                            </th>
                            <th>No. Doc.</th>
                            <th>Nombre Completo</th>
                        </tr>
                    </thead>
                    <tbody class="text-left">
                        <?php
                        foreach ($empleados as $empleado) {
                            $id_empleado = $empleado['id_empleado'];
                            $no_documento = $empleado['no_documento'];
                            $nombre = $empleado['nombre'];
                            $dias_lab = $empleado['dias_lab'];
                            $id_tercero_api = $empleado['id_tercero_api'];
                            $anio = $empleado['anio'];
                            echo '<tr>';
                            echo '<td><div class="text-center listado"><input type="checkbox" class="check" name="empleados[' . $id_empleado . ']" value="' . $dias_lab . '" checked></div></td>';
                            echo '<td>' . $no_documento . '</td>';
                            echo '<td>' . $nombre . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnCertificarForm220">Certificar</button>
                    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>