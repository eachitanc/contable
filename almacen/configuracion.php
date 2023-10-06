<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('7', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$rol = $_SESSION['rol'];
$user = $_SESSION['id_user'];
if ($rol == 1 || $rol == 3) {
    $condicion = ' ORDER BY `seg_sedes_empresa`.`nombre`,`seg_bodega_almacen`.`nombre` ASC';
} else {
    $condicion = ' AND`seg_responsable_bodega`.`id_usuario` = ' . $user . ' ORDER BY `seg_sedes_empresa`.`nombre`,`seg_bodega_almacen`.`nombre` ASC';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_sedes_empresa`.`id_sede`
                , `seg_sedes_empresa`.`nombre` AS `nombre_sede`
                , `seg_responsable_bodega`.`id_bodega`
                , `seg_bodega_almacen`.`nombre` AS `nombre_bodega`
                , `seg_responsable_bodega`.`id_resp`
                , `seg_responsable_bodega`.`id_usuario`
            FROM
                `seg_responsable_bodega`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_responsable_bodega`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
            WHERE `seg_responsable_bodega`.`id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega`  GROUP BY `id_bodega`) " . $condicion;
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$contador = 1;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-cogs fa-lg" style="color:#1D80F7"></i>
                                    CONFIGURACIONES ALMACÉN.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configone" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-address-book fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. BODEGAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configone" class="collapse" aria-labelledby="headingOne">
                                        <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                        <div class="card-body">
                                            <table id="tableBodegas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Sede</th>
                                                        <th>Bodega</th>
                                                        <th>Responsable</th>
                                                        <th>Fecha Inicia</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaBodega">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingTwo">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configtwo" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fab fa-pied-piper-square fa-lg" style="color: #DC7633;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. MARCAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configtwo" class="collapse" aria-labelledby="headingTwo">
                                        <div class="card-body">
                                            <table id="tableMarcas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Marca</th>
                                                        <th>Fecha Registro</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingTres">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configTres" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-signature fa-lg" style="color: #F1948A;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. LOTES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configTres" class="collapse" aria-labelledby="headingTres">
                                        <div class="card-body">
                                            <div class="form-row">
                                                <input type="hidden" id="slcBodega" value="1">
                                                <div class="form-group col-md-11 text-center">
                                                    <label for="article" class="small text-left">Buscar artículo</label>
                                                    <input id="article" class="form-control form-control-sm searchArticle">
                                                    <input id="id_articulo" type="hidden" value="0" class="valArt">
                                                </div>
                                                <div class="form-group col-md-1 text-center">
                                                    <label for="listaLotes" class="small">&nbsp;</label>
                                                    <div>
                                                        <button id="listaLotes" class="btn btn-outline-primary btn-sm" title="Listar Lotes"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right mb-2">
                                                <button id="btnAjusteLotes" class="btn btn-outline-success btn-sm" title="Realizar ajuste"><i class="fas fa-tools" aria-hidden="true"></i> Ajustar Lotes</button>
                                            </div>
                                            <form id="formAjusteLotes">
                                                <table id="tableLotes" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>ID</th>
                                                            <th>Lote</th>
                                                            <th>Marca</th>
                                                            <th>Invima</th>
                                                            <th>Vencimiento</th>
                                                            <th>Cantidad</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificaLotes">
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingCuatro">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configCuatro" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-compress-arrows-alt fa-lg" style="color: #27AE60;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. UNIFICAR ARTÍCULOS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configCuatro" class="collapse" aria-labelledby="headingCuatro">
                                        <div class="card-body">
                                            <div class="form-row">
                                                <div class="form-group col-md-5 text-center">
                                                    <label for="articulo1" class="small text-left">Buscar artículo principal</label>
                                                    <div class="input-group input-group-sm">
                                                        <input id="articulo1" class="form-control searchArticle">
                                                        <input id="idArtc1" type="hidden" value="0" class="valArt">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text" id="basic-addon2"> </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-5 text-center">
                                                    <label for="articulo2" class="small text-left">Buscar artículo a unificar</label>
                                                    <div class="input-group input-group-sm">
                                                        <input id="articulo2" class="form-control form-control-sm searchArticle">
                                                        <input id="idArtc2" type="hidden" value="0" class="valArt">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text" id="basic-addon2"> </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2 text-center">
                                                    <label class="small">&nbsp;</label>
                                                    <div>
                                                        <button id="btnUnificaArtc" class="btn btn-outline-primary btn-sm btn-block" title="Unificar productos"><i class="far fa-object-group mr-2" aria-hidden="true"></i>Unificar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingCinco">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configCinco" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-share-square fa-lg" style="color: #8E44AD;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. TRANSFORMAR ARTÍCULOS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configCinco" class="collapse" aria-labelledby="headingCinco">
                                        <div class="card-body">
                                            <form id="formExisteTransform">
                                                <div class="form-row">
                                                    <div class="form-group col-md-4 text-center">
                                                        <label for="articulo3" class="small text-left">Buscar artículo a transformar</label>
                                                        <div class="input-group input-group-sm">
                                                            <input id="articulo3" type="text" class="form-control searchArticle">
                                                            <input id="idArtc3" name="idArtc3" type="hidden" value="0" class="valArt">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text" id="basic-addon3"> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-4 text-center">
                                                        <label for="articulo4" class="small text-left">Buscar artículo transformado</label>
                                                        <div class="input-group input-group-sm">
                                                            <input id="articulo4" class="form-control form-control-sm searchArticle" style="width: 60%;">
                                                            <input id="numArt4" name="numArt4" type="number" class="form-control" placeholder="Cantidad" title="Indicar la cantidad de unidades a la que se transforma una sola unidad del producto anterior">
                                                            <input id="idArtc4" name="idArtc4" type="hidden" value="0" class="valArt">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text" id="basic-addon4"> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-2 text-center">
                                                        <label class="small">Tipo</label>
                                                        <div class="form-control form-control-sm" id="tipoT">
                                                            <div class="form-check form-check-inline" title="Varios a uno">
                                                                <input class="form-check-input" type="radio" name="radTransfor" id="unir" value="1">
                                                                <label class="form-check-label text-secondary" for="unir">UNIR</label>
                                                            </div>
                                                            <div class="form-check form-check-inline" title="Uno a Varios">
                                                                <input class="form-check-input" type="radio" name="radTransfor" id="dividir" value="2">
                                                                <label class="form-check-label text-secondary" for="dividir">DIVIDIR</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-2 text-center">
                                                        <label class="small">&nbsp;</label>
                                                        <div>
                                                            <button id="btnTransformaArt" class="btn btn-outline-success btn-sm btn-block" title="Transformar Artículo"><i class="fas fa-sync-alt mr-2" aria-hidden="true"></i>Transformar</button>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="form-row">
                                                    <div class="col-md-8">
                                                        <div id="existencias">

                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
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
                    <div class="modal-body text-center" id="divMsgConfdel">

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
    <?php include '../scripts.php' ?>
</body>

</html>