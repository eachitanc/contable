<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$id_pd = isset($_POST['id_pd']) ? $_POST['id_pd'] : exit('Acción no permitida');
$tipo = $_POST['tipo'];
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api`,`acta_remision`,`fec_entrada`,`estado`,`observacion` FROM `seg_entrada_almacen` WHERE `id_entrada` = '$id_pd'";
    $rs = $cmd->query($sql);
    $entraxpresta = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `descripcion` FROM  `seg_tipo_entrada` WHERE `id_entrada` = $tipo";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$tipol = $tentradas['descripcion'];
$id_ta = $entraxpresta['id_tercero_api'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ta;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR ENTRADA POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formRegEntraPrestDona">
                <input name="id_prdo" hidden value="<?php echo $id_pd ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="ccnit" class="small">TERCERO</label>
                        <input type="text" id="compleTerecero" class="form-control form-control-sm" value="<?php echo $tercero ?>">
                        <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="<?php echo $id_ta ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numActaRem" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRem" name="numActaRem" class="form-control form-control-sm" value="<?php echo $entraxpresta['acta_remision'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm" value="<?php echo $entraxpresta['fec_entrada'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txtObservaEntrada" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaEntrada" name="txtObservaEntrada" rows="3"><?php echo $entraxpresta['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="upEntraXPrestDona" type="button" class="btn btn-primary btn-sm">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>