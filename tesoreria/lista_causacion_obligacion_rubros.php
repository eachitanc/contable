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
$id_pto_doc = $_POST['id_cop'] ?? '';
$id_pag_doc = $_POST['id_doc'] ?? '';
// Consulta tipo de presupuesto
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    `seg_pto_mvto`.`id_pto_mvto`
    ,`seg_pto_mvto`.`id_pto_doc`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_mvto`.`id_tercero_api`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_mvto`.`valor`
    , `seg_pto_mvto`.`id_ctb_doc`
    , `seg_pto_cargue`.`vigencia`
    FROM
    `seg_pto_mvto`
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
    WHERE (`seg_pto_mvto`.`id_ctb_doc` ='$id_pto_doc'
    AND `seg_pto_mvto`.`tipo_mov` = 'COP'
    AND `seg_pto_cargue`.`vigencia` ='$_SESSION[vigencia]');";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableContrtacionRp').DataTable({
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
    $('#tableContrtacionRpRubros').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REGISTROS PRESUPUESTALES PARA PAGO <?php echo '' ?></h5>
        </div>
        <div class="pb-3"></div>
        <input type="hidden" name="id_pto_rp" id="id_pto_rp" value="<?php echo $id_pto_doc; ?>">
        <form id="rubrosPagar">
            <div class="px-3">
                <table id="tableContrtacionRpRubros" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Rubro</th>
                            <th style="width: 20%;">Valor Rp</th>
                            <th style="width: 20%;">Valor Cxp</th>
                            <th style="width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($rubros as $ce) {
                            $id_doc = $ce['id_ctb_doc'];
                            $id_pto_mvto = $ce['id_pto_mvto'];
                            // Consultar el valor del registro COP OBLIGADO de la tabla seg_pto_mvto
                            $sql = "SELECT sum(valor) as saldo FROM seg_pto_mvto WHERE rubro = '$ce[rubro]' AND tipo_mov = 'PAG' AND id_ctb_cop = $id_doc";
                            $rs = $cmd->query($sql);
                            $saldo = $rs->fetch();
                            $pagado = $saldo['saldo'];
                            if ($ce['id_tercero_api' != '']) {
                                $tercero = "GROUP BY id_tercero_api";
                            } else {
                                $tercero = "";
                            }
                            $sq3 = "SELECT sum(valor) as comprom FROM seg_pto_mvto WHERE rubro = '$ce[rubro]' AND tipo_mov = 'COP' AND id_ctb_doc = $id_doc " . $tercero;
                            $rs3 = $cmd->query($sq3);
                            $com = $rs3->fetch();
                            $obligado = $com['comprom'];
                            $valor =  $obligado - $pagado;
                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id_doc . '"  class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-print fa-lg"></span></a>';
                                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                                $borrar = '<a value="' . $id_doc . '" onclick="eliminarImputacionPag(' . $id_doc . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                            } else {
                                $editar = null;
                                $detalles = null;
                            }
                            $valor_obl = number_format($obligado, 2, '.', ',');
                        ?>
                            <tr>
                                <td class="text-left"><?php echo $ce['rubro'] . ' - ' . $ce['nom_rubro'] . ' ' . $obligado . ' ' . $pagado; ?></td>
                                <td class="text-right"><?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td class="text-right"><input type="text" name="rub_<?php echo $id_pto_mvto; ?>" id="rub_<?php echo  $id_pto_mvto; ?>" class="form-control form-control-sm" value="<?php echo $ce['valor']; ?>" style="text-align: right;" required onkeyup="valorMiles(id)" max="<?php echo $valor; ?>" onchange="validarValorMaximo(id)"></td>
                                <td class="text-center"> <?php echo $editar  .  $acciones; ?></td>
                            </tr>
                        <?php
                        }
                        ?>

                    </tbody>
                </table>
            </div>
            <div class="text-right pt-3">
                <a type="button" class="btn btn-primary btn-sm" onclick="rubrosaPagar(<?php echo $id_doc; ?>);"> Aceptar</a>
                <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancelar</a>


            </div>
        </form>
    </div>


</div>
<?php
$cmd = null;
