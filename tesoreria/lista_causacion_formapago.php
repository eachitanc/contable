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
$id_doc = $_POST['id_doc'] ?? '';
$id_cop = $_POST['id_cop'] ?? '';
$valor_pago = $_POST['valor'] ?? 0;
$valor_descuento = 0;
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
    `seg_tes_detalle_pago`.`id_detalle_pago`
    ,`seg_bancos`.`nom_banco`
    , `seg_tes_cuentas`.`nombre`
    , `seg_tes_forma_pago`.`forma_pago`
    , `seg_tes_detalle_pago`.`documento`
    , `seg_tes_detalle_pago`.`valor`
    FROM
    `seg_tes_detalle_pago`
    INNER JOIN `seg_tes_forma_pago` 
        ON (`seg_tes_detalle_pago`.`id_forma_pago` = `seg_tes_forma_pago`.`id_forma_pago`)
    INNER JOIN `seg_tes_cuentas` 
        ON (`seg_tes_detalle_pago`.`id_tes_cuenta` = `seg_tes_cuentas`.`id_tes_cuenta`)
    INNER JOIN `seg_bancos` 
        ON (`seg_tes_cuentas`.`id_banco` = `seg_bancos`.`id_banco`)
    WHERE (`seg_tes_detalle_pago`.`id_ctb_doc` =$id_doc);";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar id bancos de seg_bancos
try {
    $sql = "SELECT `id_banco`, `nom_banco` FROM `seg_bancos` ORDER BY `nom_banco` ASC";
    $rs = $cmd->query($sql);
    $bancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar forma de pago de seg_tes_forma_pago
try {
    $sql = "SELECT `id_forma_pago`, `forma_pago` FROM `seg_tes_forma_pago` ORDER BY `forma_pago` ASC";
    $rs = $cmd->query($sql);
    $formas_pago = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los documentos que estan relacionados con el pago
// Consultar el valor a de los descuentos realizados a la cuenta de seg_ctb_causa_retencion
try {
    $sql = "SELECT
    `id_ctb_cop`
    FROM
    `seg_pto_mvto`
    WHERE (`id_ctb_doc` =$id_doc)
    GROUP BY `id_ctb_cop`;";
    $rs = $cmd->query($sql);
    $des_documentos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar el valor a de los descuentos realizados a la cuenta de seg_ctb_causa_retencion de acuerdo a los documentos relacionados
// recorro los documentos relacionados
foreach ($des_documentos as $des) {
    try {
        $sql = "SELECT SUM(`valor_retencion`) AS `valor` FROM `seg_ctb_causa_retencion` WHERE `id_ctb_doc` = {$des['id_ctb_cop']}";
        $rs = $cmd->query($sql);
        $descuentos = $rs->fetch();
        $valor_descuento = $valor_descuento + $descuentos['valor'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// consultar el valor registrado en seg_test_detalle_pago para el id_ctb_doc
try {
    $sql = "SELECT SUM(`valor`) AS `valor` FROM `seg_tes_detalle_pago` WHERE `id_ctb_doc` = $id_doc";
    $rs = $cmd->query($sql);
    $pagos = $rs->fetch();
    $valor_programado = $pagos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$valor_pagar = $valor_pago - $valor_descuento - $valor_programado;

?>
<script>
    $('#tableCausacionPagos').DataTable({
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
    $('#tableCausacionPagos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CUENTAS BANCARIAS Y FORMA DE PAGO </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <form id="formAddFormaPago">
                <div class="row">
                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">BANCO:</label></div>
                    </div>
                    <div class="col-4">
                        <div class="col"><label for="numDoc" class="small">CUENTA:</label></div>
                    </div>
                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">FORMA DE PAGO:</label></div>
                    </div>
                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">DOCUMENTO:</label></div>
                    </div>
                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">VALOR:</label></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="col">
                            <select name="banco" id="banco" class="form-control form-control-sm" required onclick="mostrarCuentas(value);">
                                <option value="0">...Seleccione...</option>
                                <?php foreach ($bancos as $banco) : ?>
                                    <option value="<?php echo $banco['id_banco']; ?>"><?php echo $banco['nom_banco']; ?></option>
                                <?php endforeach; ?>
                                <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $id_doc; ?>">
                                <input type="hidden" name="id_pto_cop" id="id_pto_cop" value="<?php echo $id_cop; ?>">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="col" id="divBanco"><input type="text" name="cuenta" id="cuenta" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-2">
                        <div class="col" id="divForma">
                            <select name="forma_pago_det" id="forma_pago_det" class="form-control form-control-sm" required onchange="buscarCheque(value);">
                                <option value="0">...Seleccione...</option>
                                <?php foreach ($formas_pago as $forma_pago) : ?>
                                    <option value="<?php echo $forma_pago['id_forma_pago']; ?>"><?php echo $forma_pago['forma_pago']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="col" id="divCosto"><input type="text" name="documento" id="documento" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-2">
                        <div class="btn-group"><input type="text" name="valor_pag" id="valor_pag" class="form-control form-control-sm" max="<?php echo $valor_pagar; ?>" value="<?php echo $valor_pagar; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="valorMovTeroreria('');">
                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">+</button>
                        </div>
                    </div>
                </div>

            </form> <br>
            <table id="tableCausacionPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-15">Banco</th>
                        <th class="w-30">Cuenta</th>
                        <th class="w-5">Forma de pago</th>
                        <th class="w-5">Documento</th>
                        <th class="w-10">Valor</th>
                        <th class="w-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($rubros as $ce) {
                            //$id_doc = $ce['id_ctb_doc'];
                            $id = $ce['id_detalle_pago'];
                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id_doc . '" onclick="eliminarFormaPago(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                            $valor = number_format($ce['valor'], 2, '.', ',');
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td><?php echo $ce['nom_banco']; ?></td>
                                <td><?php echo $ce['nombre']; ?></td>
                                <td> <?php echo $ce['forma_pago']; ?></td>
                                <td> <?php echo $ce['documento']; ?></td>
                                <td> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td> <?php echo $editar .  $acciones; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div class="text-right pt-3">
                <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</a>


            </div>

        </div>


    </div>
    <?php
