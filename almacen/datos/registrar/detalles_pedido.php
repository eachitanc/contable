<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_pdo = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pedidos_almacen`.`id_pedido`
                , `seg_pedidos_almacen`.`id_bodega`
                , `seg_pedidos_almacen`.`bod_entrega`
                , `seg_bodega_almacen`.`nombre` AS `area`
                , `seg_pedidos_almacen`.`estado`
                , CONCAT_WS(' ',`seg_usuarios`.`nombre1`, `seg_usuarios`.`nombre2`, `seg_usuarios`.`apellido1`, `seg_usuarios`.`apellido2`) AS `responsable`
                , `seg_pedidos_almacen`.`fec_reg`
                , `seg_pedidos_almacen`.`id_user_reg`
            FROM
                `seg_pedidos_almacen`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_usuarios` 
                    ON (`seg_pedidos_almacen`.`id_user_reg` = `seg_usuarios`.`id_usuario`)
            WHERE `seg_pedidos_almacen`.`id_pedido` = $id_pdo";
    $rs = $cmd->query($sql);
    $pedido = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$bg_sale = $pedido['bod_entrega'];
$bg_entra = $pedido['id_bodega'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `id_bodega` FROM `seg_bodega_almacen` WHERE `id_bodega` IN ($bg_sale,$bg_entra)";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$sede_sale = $sedes[0]['id_sede'];
$sede_entra = $sedes[1]['id_sede'];
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_bodega`
                , `id_usuario`
            FROM
                `seg_responsable_bodega`
            WHERE (`id_usuario` = $id_user AND `estado` = 1)";
    $rs = $cmd->query($sql);
    $respon = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `estado`, `id_pedido`, `id_entrada`, `id_user_reg`, `bod_entrega` FROM `seg_pedidos_almacen` WHERE `id_pedido` = $id_pdo";
    $rs = $cmd->query($sql);
    $pide = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$bodega = $pide['bod_entrega'];
$key  = array_search($bodega, array_column($respon, 'id_bodega'));
if ($key !== false) {
    $perm = true;
} else {
    $perm = false;
}
$usuario = $id_user == $pide['id_user_reg'] ? true : false;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    DETALLES DE PEDIDO.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="text-right mb-3">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../../lista_pedidos.php">Regresar</a>
                                <?php
                                if ($pedido['estado'] > 0 && $pedido['estado'] < 3 && $usuario) {
                                ?>
                                    <button class="btn btn-info btn-sm" id="btnCerrarPedido"><span></span>Cerrar Pedido</button>
                                <?php
                                }
                                if ($pedido['estado'] == 3 && ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3 || $perm)) {
                                ?>
                                    <button class="btn btn-success btn-sm" id="btnEntregaPedido"><span></span> Entregar Pedido</button>
                                    <button class="btn btn-info btn-sm" id="btnImprimirPedido"><i class="fas fa-print"></i> Pedido</button>
                                    <?php
                                }
                                if ($pedido['estado'] == 4) {
                                    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
                                    ?>
                                        <button class="btn btn-info btn-sm" id="btnImprimirPedido"><i class="fas fa-print"></i> Pedido</button>
                                        <button class="btn btn-warning btn-sm" id="btnImprimirTraslado"><i class="fas fa-print"></i> Traslado</button>
                                        <button class="btn btn-success btn-sm" id="btnImprimirConsumo"><i class="fas fa-print"></i> Consumo</button>
                                    <?php
                                    } else {
                                    ?>
                                        <button class="btn btn-info btn-sm" id="btnImprimirPedido"><i class="fas fa-print"></i> Pedido</button>
                                        <button class="btn btn-success btn-sm" id="btnImprimirConsumo"><i class="fas fa-print"></i> Consumo</button>
                                        <button class="btn btn-secondary btn-sm" id="btnCerrarConsumo"><i class="fas fa-door-closed"></i> Cerrar Consumo</button>
                                    <?php
                                    }
                                }
                                if ($pedido['estado'] == 4 &&  $usuario) {
                                    //btnConsumirPedido
                                    ?>
                                    <button class="btn btn-info btn-sm" id="btnConsumirPedido"><i class="fas fa-arrow-alt-circle-down"></i> Consumir</button>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="shadow detalles-empleado mb-4">
                                <div class="row">
                                    <div class="div-mostrar bor-top-left col-md-4">
                                        <label class="lbl-mostrar">ÁREA</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($pedido['area']) ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-4">
                                        <label class="lbl-mostrar">RESPONSABLE</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($pedido['responsable']) ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-2">
                                        <label class="lbl-mostrar">ESTADO</label>
                                        <div class="div-cont text-center">
                                            <?php
                                            if ($pedido['estado'] == 0) {
                                                $estado = '<span class="badge badge-pill badge-secondary">ANULADO</span>';
                                            } else if ($pedido['estado'] == 1) {
                                                $estado = '<span class="badge badge-pill badge-info">INICIAL</span>';
                                            } else if ($pedido['estado'] == 2) {
                                                $estado = '<span class="badge badge-pill badge-primary">SIN ENVIAR</span>';
                                            } else if ($pedido['estado'] == 3) {
                                                $estado = '<span class="badge badge-pill badge-warning">PENDIENTE</span>';
                                            } else if ($pedido['estado'] == 4) {
                                                $estado = '<span class="badge badge-pill badge-success">ENTREGADO</span>';
                                            }
                                            echo $estado;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="div-mostrar bor-top-right col-md-2">
                                        <label class="lbl-mostrar">FECHA SOLICITUD</label>
                                        <div class="div-cont"><?php echo date('Y-m-d', strtotime($pedido['fec_reg'])) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($pedido['estado'] > 0 && $pedido['estado'] < 3 && $usuario) {
                            ?>
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                            <?php
                            }
                            ?>
                            <input type="hidden" id="id_pdo" value="<?php echo $id_pdo ?>">
                            <form id="formCantProdPedido">
                                <input type="hidden" name="id_pedido" value="<?php echo $id_pdo ?>">
                                <input type="hidden" name="id_sede_entra" value="<?php echo $sede_entra ?>">
                                <input type="hidden" name="id_bodega_entra" value="<?php echo $bg_entra ?>">
                                <input type="hidden" name="id_sede_sale" value="<?php echo $sede_sale ?>">
                                <input type="hidden" name="id_bodega_sale" value="<?php echo $bg_sale ?>">
                                <table id="tableDetallePedido" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>ID</th>
                                            <th>Bien o servicio</th>
                                            <th>lote</th>
                                            <th>Vence</th>
                                            <th>Cantidad</th>
                                            <th class="w-15">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="accionDetallePedido">
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgError">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalConfDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body" id="divMsgConfdel">

                    </div>
                    <div class="modal-footer" id="divBtnsModalDel">
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgDone">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalReg" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalReg" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divFormsReg">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>