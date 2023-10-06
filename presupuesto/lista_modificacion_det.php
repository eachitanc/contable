<?php
session_start();
header("Pragma: no-cache");
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
$id_pto_mod = $_POST['id_mod'];
// Consulto los datos generales del nuevo registro presupuesal
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos,fecha, id_manu,objeto,tipo_doc FROM seg_pto_documento WHERE id_pto_doc=$id_pto_mod";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el id de presupuesto de ingresos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos FROM seg_pto_presupuestos WHERE id_pto_tipo=1 AND vigencia =$_SESSION[vigencia]";
    $rs = $cmd->query($sql);
    $ptoingreso = $rs->fetch();
    $id_ingreso = $ptoingreso['id_pto_presupuestos'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el id del presupuesto de gastos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos FROM seg_pto_presupuestos WHERE id_pto_tipo=2 AND vigencia =$_SESSION[vigencia]";
    $rs = $cmd->query($sql);
    $ptogastos = $rs->fetch();
    $id_gastos = $ptogastos['id_pto_presupuestos'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// consulto sumas de valor tabla seg_pto_mvto 
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc =  $id_pto_mod AND estado =1 GROUP BY id_pto_doc";
    $rs = $cmd->query($sql);
    $datos2 = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc =  $id_pto_mod AND estado =0 GROUP BY id_pto_doc";
    $rs = $cmd->query($sql);
    $datos3 = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$dif = ($datos2['valorsum'] - $datos3['valorsum']);
$dif = abs($dif);
$fecha = date('Y-m-d', strtotime($datos['fecha']));
$consulta = $sql;
// muestro opciones de presupuesto segun el tipo de documento
if ($datos['tipo_doc'] == 'ADI' || $datos['tipo_doc'] == 'RED') {
    $menu = '<label id="btnIngresos" class="btn btn-info active">
            <input type="radio" name="options" id="option1" autocomplete="off" checked> Ingresos
            </label>
            <label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"> Gastos &nbsp;
            </label>';
    $etiqueta1 = 'Ingresos';
    $etiqueta2 = 'Gastos';
} else if ($datos['tipo_doc'] == 'APL') {
    $menu = '<label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"  checked> Gastos &nbsp;
            </label>';
    $etiqueta1 = 'Desaplazamientos';
    $etiqueta2 = 'Aplazamientos';
} else if ($datos['tipo_doc'] == 'DES') {
    $menu = '<label class="btn btn-info">
                <input type="radio" name="options" id="option2" autocomplete="off"  checked> Gastos &nbsp;
                </label>';
    $etiqueta2 = 'Desaplazamientos';
    $etiqueta1 = 'Aplazamientos';
} else {
    $menu = '<label id="btnIngresos" class="btn btn-info active">
            <input type="radio" name="options" id="option1" autocomplete="off" checked> &nbsp &nbsp Contracréditos &nbsp &nbsp
            </label>
            <label class="btn btn-info">
            <input type="radio" name="options" id="option2" autocomplete="off"> Créditos&nbsp;
            </label>';
    $etiqueta1 = 'Contracréditos';
    $etiqueta2 = 'Créditos';
}
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLE DOCUMENTO DE MODIFICACION PRESUPUESTAL
                                </div>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div class="right-block">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">NUMERO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datos['id_manu']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $fecha; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datos['objeto']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">ACCIONES:</label></div>
                                        </div>
                                        <div class="col-10">
                                            <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                                                <?php echo $menu; ?>
                                            </div>
                                        </div>
                                    </div>



                                </div>
                            </div>
                            <br>
                            <table id="tableModDetalle" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 58%;">Codigo</th>
                                        <th style="width: 15%;" Class="text-center"><?php echo $etiqueta1; ?></th>
                                        <th style="width: 15%;" Class="text-center"><?php echo $etiqueta2; ?></th>
                                        <th style="width: 12%;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarModDetalle">

                                </tbody>

                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                                <input type="hidden" id="pto_gastos" name="pto_gastos" value="<?php echo $id_gastos; ?>">
                                <input type="hidden" id="pto_ingresos" name="pto_ingresos" value="<?php echo $id_ingreso; ?>">


                                <form id="formAddModDetalle">
                                    <tr>
                                        <th colspan='2'>
                                            <input type="text" name="rubroCod" id="rubroCod" class="form-control form-control-sm" value="" required>
                                            <input type="hidden" name="id_rubroCod" id="id_rubroCod" class="form-control form-control-sm" value="">
                                            <input type="hidden" name="tipoRubro" id="tipoRubro" value="">

                                        </th>
                                        <th>
                                            <input type="text" name="valorDeb" id="valorDeb" class="form-control form-control-sm " size="6" value="0" style="text-align: right;" onkeyup="valorMiles(id)" required ondblclick="valorDif()" onchange="consultaSaldoRubro(<?php echo $_SESSION['vigencia']; ?>)">
                                        </th>
                                        <th class="text-center">
                                            <input type="hidden" name="id_pto_mod" id="id_pto_mod" value="<?php echo $id_pto_mod; ?>">
                                            <input type="hidden" name="tipo_doc" id="tipo_doc" value="<?php echo $datos['tipo_doc'] ?>">
                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Ver historial del rubro" onclick="verHistorial(<?php echo $_SESSION['vigencia']; ?>)"><span class="far fa-list-alt fa-lg"></span></a>
                                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMovDetalle">Agregar</button>
                                        </th>
                                    </tr>
                                </form>

                                <tfoot>

                                    <tr>
                                        <th>Total</th>
                                        <th>
                                            <div class="text-right">
                                                <input type="text" id="suma1" value="<?php echo number_format($datos2['valorsum'], 2, ".", ","); ?>" size="12" style="text-align:right;border: 0;background-color: #16a085;">
                                            </div>
                                        </th>
                                        <th>
                                            <div class="text-right">
                                                <input type="text" id="suma2" value="<?php echo number_format($datos3['valorsum'], 2, ".", ","); ?>" size="12" style="text-align:right;border: 0;background-color: #16a085;">
                                                <input type="hidden" id="dif" value="<?php echo $dif; ?>">
                                                <input type="hidden" id="id_pto_ppto" value="<?php echo $datos['id_pto_presupuestos']; ?>">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="text-center pt-4">
                                <a onclick="terminarDetalleMod('<?php echo $datos['tipo_doc']; ?>')" class="btn btn-danger" style="width: 7rem;" href="#"> TERMINAR</a>

                            </div>
                        </div>

                    </div>
                </div>
                <div>

                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>


    <?php include '../scripts.php' ?>
</body>

</html>