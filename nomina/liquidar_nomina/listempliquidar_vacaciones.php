<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_vacaciones`.`id_vac`
                , `seg_vacaciones`.`fec_inicial`
                , `seg_vacaciones`.`fec_fin`
                , `seg_vacaciones`.`dias_inactivo`
                , `seg_vacaciones`.`dias_habiles`
                , `seg_vacaciones`.`estado`
                , `seg_vacaciones`.`dias_liquidar`
                , `seg_empleado`.`id_empleado`
                , `seg_empleado`.`no_documento`
                , `seg_empleado`.`nombre1`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`
            FROM
                `seg_vacaciones`
                INNER JOIN `seg_empleado` 
                    ON (`seg_vacaciones`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_vacaciones`.`estado` = 1)";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$carcater_empresa = $_SESSION['caracter'] == 2 ? $_SESSION['caracter'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <span class="fas fa-users fa-lg" style="color:#1D80F7"></span>
                                    LISTA DE EMPLEADOS A LIQUIDAR VACACIONES.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="">
                                <form id="formLiqVacs">
                                    <input type="hidden" id="caracter_empresa" value="<?php echo $carcater_empresa ?>">
                                    <table id="tableLiqVacs" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center centro-vertical" rowspan="2"><br><input id="selectAll" type="checkbox" checked></th>
                                                <th class="text-center centro-vertical" rowspan="2">No. Doc.</th>
                                                <th class="text-center centro-vertical" rowspan="2">Nombre Completo</th>
                                                <th class="text-center centro-vertical" rowspan="2">Inicia</th>
                                                <th class="text-center centro-vertical" rowspan="2">Termina</th>
                                                <th class="text-center centro-vertical" colspan="3">Dias</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center centro-vertical">Háb.</th>
                                                <th class="text-center centro-vertical">Inact.</th>
                                                <th class="text-center centro-vertical">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($obj as $o) {
                                            ?>
                                                <tr id="filaempl">
                                                    <td>
                                                        <div class="center-block listado">
                                                            <input clase="setAll" type="checkbox" name="empleado[<?php echo $o['id_empleado'] ?>]" checked value="<?php echo $o['id_vac'] ?>">
                                                        </div>
                                                    </td>
                                                    <td><?php echo $o['no_documento'] ?></td>
                                                    <td><?php echo trim(mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2'])) ?></td>
                                                    <td><?php echo $o['fec_inicial'] ?></td>
                                                    <td><?php echo $o['fec_fin'] ?></td>
                                                    <td><?php echo $o['dias_habiles'] ?></td>
                                                    <td><?php echo $o['dias_inactivo'] ?></td>
                                                    <td><?php echo $o['dias_liquidar'] ?></td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="center-block py-2">
                                <div class="form-group">
                                    <button class="btn btn-info" id="btnLiqVacaciones">LIQUIDAR VACACIONES</button>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> CANCELAR</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <span class="fas fa-exclamation-circle fa-lg" style="color:red"></span>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgError">
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalExito" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <span class="fas fa-check-circle fa-lg" style="color:#2FDA49"></span>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body" id="divMsgExito">
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalEspera" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <span class="fas fa-check-circle fa-lg" style="color:#2FDA49"></span>
                            Liquidando...
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgExito">
                        <div class="spinner-grow text-warning" role="status">
                            <span class="sr-only">Liquidando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>