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
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
            seg_adquisiciones.id_adquisicion
            , seg_novedad_contrato_adi_pror.id_nov_con
            , seg_novedad_contrato_adi_pror.fec_adcion
            , seg_adquisiciones.id_tercero
            , seg_novedad_contrato_adi_pror.val_adicion
            , seg_novedad_contrato_adi_pror.cdp
            , seg_contrato_compra.num_contrato
        FROM
            seg_contrato_compra
            INNER JOIN seg_adquisiciones 
                ON (seg_contrato_compra.id_compra = seg_adquisiciones.id_adquisicion)
            INNER JOIN seg_novedad_contrato_adi_pror 
                ON (seg_novedad_contrato_adi_pror.id_adq = seg_contrato_compra.id_contrato_compra)
        WHERE (seg_novedad_contrato_adi_pror.cdp IS NULL); ";
    $rs = $cmd->query($sql);
    $solicitudes = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los id de terceros creado en la tabla seg_ctb_doc
try {
    $sql = "SELECT DISTINCT
    `id_tercero_api`
    FROM
    `seg_terceros`;";
    $res = $cmd->query($sql);
    $id_terceros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($id_terceros as $ter) {
    $id_t[] = $ter['id_tercero_api'];
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
?>
<script>
    $('#tableContrtacionCdp').DataTable({
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
    $('#tableContrtacionCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE SOLICITUDES PARA CDP DE OTRO SI</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionCdp" class="table table-striped table-bordered  table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-10">Numero ADQ</th>
                        <th class="w-10">Numero contrato</th>
                        <th class="w-10">Fecha adición</th>
                        <th class="w-10">CC / Nit</th>
                        <th class="w-40">Tercero</th>
                        <th class="w-15">Valor</th>
                        <th class="w-10">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($solicitudes as $ce) {
                        // Consulto el tercero_api en la tabal seg_teceros
                        try {
                            $sql = "SELECT
                                        `id_tercero_api`
                                    FROM
                                        `seg_terceros`
                                    WHERE `id_tercero` ={$ce['id_tercero']};";
                            $res = $cmd->query($sql);
                            $ccnit = $res->fetch();
                            $id_tercero = $ccnit['id_tercero_api'];
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                        }
                        // Consulto el api de terceros para obtener los datos
                        $key = array_search($id_tercero, array_column($terceros, 'id_tercero'));
                        $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
                        $ccnit = $terceros[$key]['cc_nit'];

                        $id_doc = $ce['id_nov_con'];
                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="mostrarListaOtrosi(' . $id_doc . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }
                    ?> <tr>
                            <!--td class="text-center"><input type="checkbox" value="" id="defaultCheck1"></td-->
                            <td class="text-left"><?php echo $ce['id_adquisicion'] ?></td>
                            <td class="text-left"><?php echo $ce['num_contrato'] ?></td>
                            <td class="text-left"><?php echo $ce['fec_adcion'] ?></td>
                            <td class="text-left"><?php echo  $ccnit  ?></td>
                            <td class="text-left"><?php echo $tercero  ?></td>
                            <td class="text-right">$ <?php echo number_format($ce['val_adicion'], 2, '.', ',') ?></td>
                            <td class="text-center"> <?php echo $editar ?></td>
                        </tr>

                    <?php
                        $tercero = null;
                        $ccnit = null;
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
