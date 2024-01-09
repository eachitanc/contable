<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_cdp = $_POST['id_cdp'] ?? 0;
$automatico = '';
// Consulto los datos generales del nuevo registro presupuesal
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT id_pto_presupuestos,fecha, id_manu,objeto,id_auto FROM seg_pto_documento WHERE id_pto_doc=$id_pto_cdp";
    $rs = $cmd->query($sql);
    $datosCdp = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT consecutivo FROM seg_fin_maestro_doc WHERE tipo_doc ='CRP' AND estado=0";
    $res = $cmd->query($sql);
    $ducumento = $res->fetch();
    if ($ducumento['consecutivo'] == 1) {
        $automatico = 'readonly';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulta si el cdp tiene relacionado numero de contrato en adquisiciones
try {
    $sql = "SELECT
                `seg_contrato_compra`.`num_contrato`
            FROM
                `seg_contrato_compra`
                INNER JOIN `seg_adquisiciones` 
                    ON (`seg_contrato_compra`.`id_compra` = `seg_adquisiciones`.`id_adquisicion`)
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_documento`.`id_pto_doc` = `seg_adquisiciones`.`id_cdp`)
            WHERE (`seg_pto_documento`.`id_pto_doc` =$id_pto_cdp);";
    $res = $cmd->query($sql);
    $contrato = $res->fetch();
    $num_contrato = $contrato['num_contrato'] ?? '';
    if ($ducumento['consecutivo'] == 1) {
        $automatico = 'readonly';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto si el Cdp esta relacionado en el campo cdp de la tabla seg_novedad_contrato_adi_pror
$id_t = [];
$tercero = '';
$id_tercero = '';
try {
    $sql = "SELECT
                seg_adquisiciones.id_tercero
            FROM
                seg_contrato_compra
                INNER JOIN seg_adquisiciones 
                    ON (seg_contrato_compra.id_compra = seg_adquisiciones.id_adquisicion)
                INNER JOIN seg_novedad_contrato_adi_pror 
                    ON (seg_novedad_contrato_adi_pror.id_adq = seg_contrato_compra.id_contrato_compra)
            WHERE (seg_novedad_contrato_adi_pror.cdp =$id_pto_cdp) ;";
    $res = $cmd->query($sql);
    $novedad = $res->fetch();
    if ($novedad['id_tercero'] > 0) {
        $automatico = 'readonly';
        try {
            $sql = "SELECT
                        `id_tercero_api`
                    FROM
                        `seg_terceros`
                    WHERE `id_tercero` ={$novedad['id_tercero']};";
            $res = $cmd->query($sql);
            $ccnit = $res->fetch();
            $id_t[] = $ccnit['id_tercero_api'];
            if ($ducumento['consecutivo'] == 1) {
                $automatico = 'readonly';
            }
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
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
        $key = array_search($novedad['id_tercero'], array_column($terceros, 'id_tercero'));
        $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
        $ccnit = $terceros[$key]['cc_nit'];
        $id_tercero = $terceros[$key]['id_tercero'];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT COUNT(`id_pto_mvto`) AS liquidado FROM `seg_pto_mvto` WHERE `id_auto_dep` = $id_pto_cdp AND tipo_mov = 'LRP';";
    $rs = $cmd->query($sql);
    $cantidad = $rs->fetch();
    $con_liquidacion = $cantidad['liquidado'];
    if ($con_liquidacion > 0) {
        $automatico = '';
        $tercero = '';
        $id_tercero = '';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha_cierre =  date("Y-m-d", strtotime($datosCdp['fecha']));
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha = date('Y-m-d'); //, strtotime($datosCdp['fecha']));

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
                                    DETALLE CERTIFICADO DE REGISTRO PRESUPUESTAL <?php echo ''; ?>
                                </div>

                            </div>
                        </div>
                        <form id="formRegistroCrp">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO CRP:</label></div>
                                            </div>
                                            <div class="col-2"><input type="number" name="numCdp" id="numCdp" class="form-control form-control-sm" value="" onchange="buscarCdp(value,'CRP')" readonly required>
                                                <input type="hidden" id="id_pto_ppto" name="id_pto_presupuestos" value="<?php echo $datosCdp['id_pto_presupuestos']; ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-2"><input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('CRP');"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">TERCERO:</label></div>
                                            </div>
                                            <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $id_tercero; ?>">
                                            <div class="col-6"><input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required <?php echo $automatico; ?>>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"><textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"><?php echo $datosCdp['objeto']; ?></textarea></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NO CONTRATO:</label></div>
                                            </div>
                                            <div class="col-2"><input type="text" name="contrato" id="contrato" class="form-control form-control-sm" value="<?php echo $num_contrato; ?>"></div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <table id="tableEjecCrpNuevo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecCrpNuevo">
                                    </tbody>
                                    <input type="hidden" id="id_pto_cdp" name="id_pto_cdp" value="<?php echo $id_pto_cdp; ?>">
                                    <input type="hidden" id="id_pto_crp" value="<?php echo '' ?>">
                                    <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th>
                                                <div class="text-right"></div>
                                            </th>
                                            <th> <button type="submit" class="btn btn-primary btn-sm" id="registrarMovDetalle">Registrar</button></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="text-center pt-4">
                                    <a value="" type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoCrp('');" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                                    <a onclick="cambiaListado(1)" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> VOLVER</a>

                                </div>
                            </div>
                        </form>
                        <input type="hidden" name="id_pto_save" id="id_pto_save" value="">

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
    <script type="text/javascript">
        window.onload = function() {
            buscarConsecutivo('CRP');
        }
    </script>
</body>

</html>