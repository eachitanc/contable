<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *  FROM seg_empleado";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `seg_salarios_basico`.`id_empleado`
                , `seg_salarios_basico`.`id_salario`
                , `seg_salarios_basico`.`vigencia`
                , `seg_salarios_basico`.`salario_basico`
            FROM (SELECT
                MAX(`id_salario`) AS `id_salario`, `id_empleado`
                FROM
                    `seg_salarios_basico`
                WHERE `vigencia` <= '$vigencia'
                GROUP BY `id_empleado`) AS `t`
            INNER JOIN `seg_salarios_basico`
                ON (`seg_salarios_basico`.`id_salario` = `t`.`id_salario`)";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE EMPLEADOS
                                </div>
                                <?php if ((intval($permisos['registrar'])) === 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                } ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <table id="tableListEmpleados" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Doc.</th>
                                            <th>Apellidos</th>
                                            <th>Nombres</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Salario</th>
                                            <th>Estado</th>
                                            <th>Acción</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarEmpleados">
                                        <?php
                                        $sal_bas = 0;
                                        foreach ($obj as $o) {
                                            $ide = $o['no_documento'];
                                        ?>
                                            <tr id="filaempl">
                                                <td><?php echo $o['no_documento'] ?></td>
                                                <td><?php echo mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2']) ?></td>
                                                <td><?php echo mb_strtoupper($o['nombre1'] . ' ' . $o['nombre2']) ?></td>
                                                <td><?php echo $o['correo'] ?></td>
                                                <td><?php echo $o['telefono'] ?></td>
                                                <td>
                                                    <?php
                                                    $emplkey = array_search($ide, array_column($salarios, 'id_empleado'));
                                                    if ($emplkey !== "") {
                                                        foreach ($salarios as $sa) {
                                                            if ($o['id_empleado'] === $sa['id_empleado']) {
                                                                echo pesos($sa['salario_basico']);
                                                                $sal_bas = $sa['salario_basico'];
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center" id="tdEstado">
                                                    <?php
                                                    if ((intval($permisos['editar'])) === 1) {
                                                        if ($o['estado'] === '1') {
                                                    ?>
                                                            <button class="btn-estado" value="<?php echo $o['id_empleado'] ?>">
                                                                <div id="divIconoshow<?php echo $o['id_empleado'] ?>">
                                                                    <i class="fas fa-toggle-on fa-lg" style="color:#37E146;"></i>
                                                                </div>
                                                                <div id="divIcono<?php echo $o['id_empleado'] ?>">

                                                                </div>
                                                            </button>
                                                        <?php } else {
                                                        ?>
                                                            <button class="btn-estado" value="<?php echo $o['id_empleado'] ?>">
                                                                <div id="divIconoshow<?php echo $o['id_empleado'] ?>">
                                                                    <i class="fas fa-toggle-off fa-lg" style="color:gray;"></i>
                                                                </div>
                                                                <div id="divIcono<?php echo $o['id_empleado'] ?>">

                                                                </div>
                                                            </button>
                                                    <?php
                                                        }
                                                    } else {
                                                        $es = $o['estado'] == '1' ? 'ACTIVO' : 'INACTIVO';
                                                        echo $es;
                                                    }
                                                    ?>

                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        <div>
                                                            <?php if (intval($permisos['editar']) === 1) { ?>
                                                                <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar">
                                                                    <span class="fas fa-pencil-alt fa-lg"></span>
                                                                </button>
                                                                <?php }
                                                            if (intval($sal_bas) > 0) {
                                                                if (intval($permisos['registrar']) === 1) { ?>
                                                                    <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-success btn-sm btn-circle horas" title="+ Horas extras">
                                                                        <span class="fas fa-clock fa-lg"></span>
                                                                    </button>
                                                                    <?php if ($_SESSION['caracter'] == '1') { ?>
                                                                        <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-info btn-sm btn-circle viaticos" title="+ Viáticos">
                                                                            <span class="fas fa-suitcase fa-lg"></span>
                                                                        </button>
                                                                <?php
                                                                    }
                                                                }
                                                            }
                                                            if (intval($permisos['borrar']) === 1) {
                                                                ?>
                                                                <button class="btn btn-outline-danger btn-sm btn-circle eliminar" value="<?php echo $o['id_empleado'] ?>" title="Eliminar">
                                                                    <span class="fas fa-trash-alt fa-lg"></span>
                                                                </button>
                                                            <?php }
                                                            if (intval($sal_bas) > 0) {
                                                            ?>
                                                                <button value="<?php echo $o['id_empleado'] ?>" class="btn btn-outline-warning btn-sm btn-circle detalles" title="Detalles">
                                                                    <span class="far fa-eye fa-lg"></span>
                                                                </button>
                                                            <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>No. Doc.</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Salario</th>
                                            <th>Estado</th>
                                            <th>Opciones</th>

                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalConfirmarDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body" id="divConfirmdel">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" id="btnConfirDelEmpleado">Aceptar</button>
                        <button type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalHoExitoDelempl" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgExitoDelEmpl">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" href="listempleados.php"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>