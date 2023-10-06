<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$anio = $_SESSION['vigencia'];
$data = json_decode(file_get_contents('php://input'), true);
$id_nomina = isset($data['id']) ? $data['id'] : exit('Acción no permitida');

include '../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_nomina`, `mes`, `fec_reg`
            FROM
                `seg_nominas`
            WHERE (`id_nomina` = $id_nomina) LIMIT 1";
    $rs = $cmd->query($sql);
    $data_nomina = $rs->fetch();
    $mes = $data_nomina['mes'];
    $fec_liq = date('Y-m-d', strtotime($data_nomina['fec_reg']));
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$dia = '01';
switch ($mes) {
    case '01':
    case '03':
    case '05':
    case '07':
    case '08':
    case '10':
    case '12':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-31';
        break;
    case '02':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        if (date('L', strtotime("$anio-01-01")) === '1') {
            $bis = '29';
        } else {
            $bis = '28';
        }
        $fec_f = $anio . '-' . $mes . '-' . $bis;
        break;
    case '04':
    case '06':
    case '09':
    case '11':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-30';
        break;
    default:
        echo 'Error Fatal';
        exit();
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_valxvig, id_concepto, valor,concepto
            FROM
                seg_valxvigencia
            INNER JOIN con_vigencias 
                ON (seg_valxvigencia.id_vigencia = con_vigencias.id_vigencia)
            INNER JOIN seg_conceptosxvigencia 
                ON (seg_valxvigencia.id_concepto = seg_conceptosxvigencia.id_concp)
            WHERE anio = '$anio' AND id_concepto = '4'";
    $rs = $cmd->query($sql);
    $concec = $rs->fetch();
    $iNonce = intval($concec['valor']);
    $idiNonce = $concec['id_valxvig'];
    $sql = "UPDATE seg_valxvigencia SET valor = '$iNonce'+1 WHERE id_valxvig = '$idiNonce'";
    $rs = $cmd->query($sql);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$prima = array();
if ($mes === '06' || $mes === '12') {
    $periodo = $mes == '06' ? '1' : '2';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT id_empleado, cant_dias, val_liq_ps, periodo, anio
                FROM
                    seg_liq_prima
                WHERE periodo = '$periodo' AND anio = '$anio'";
        $rs = $cmd->query($sql);
        $prima = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT fech_inicio, fec_retiro, mes, correo, telefono, codigo_netc, seg_tipo_empleado.codigo AS tip_emp, seg_subtipo_empl.codigo AS subt_emp,alto_riesgo_pension, seg_tipos_documento.codigo AS tip_doc, codigo_ne, no_documento, apellido1, apellido2, nombre1, nombre2, codigo_pais, codigo_dpto, nombre_dpto, codigo_municipio, nom_municipio, direccion, salario_integral, seg_tipo_contrato.codigo AS tip_contrato, salario_basico, seg_empleado.id_empleado
            FROM
                seg_empleado
            INNER JOIN seg_tipos_documento 
                ON (seg_empleado.tipo_doc = seg_tipos_documento.id_tipodoc)
            INNER JOIN seg_tipo_empleado 
                ON (seg_empleado.tipo_empleado = seg_tipo_empleado.id_tip_empl)
            INNER JOIN seg_subtipo_empl 
                ON (seg_empleado.subtipo_empleado = seg_subtipo_empl.id_sub_emp)
            INNER JOIN seg_pais 
                ON (seg_empleado.pais = seg_pais.id_pais)
            INNER JOIN seg_departamento 
                ON (seg_departamento.id_pais = seg_pais.id_pais) AND (seg_empleado.departamento = seg_departamento.id_dpto)
            INNER JOIN seg_municipios 
                ON (seg_municipios.id_departamento = seg_departamento.id_dpto) AND (seg_empleado.municipio = seg_municipios.id_municipio)
            INNER JOIN seg_tipo_contrato 
                ON (seg_empleado.tipo_contrato = seg_tipo_contrato.id_tip_contrato)
            INNER JOIN seg_salarios_basico 
                ON (seg_salarios_basico.id_empleado = seg_empleado.id_empleado)
            INNER JOIN seg_liq_salario 
                ON (seg_liq_salario.id_empleado = seg_empleado.id_empleado)
            WHERE `seg_liq_salario`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                seg_empresas
            INNER JOIN seg_departamento 
                ON (seg_empresas.id_dpto = seg_departamento.id_dpto)
            INNER JOIN seg_municipios 
                ON (seg_municipios.id_departamento = seg_departamento.id_dpto) AND (seg_empresas.id_ciudad = seg_municipios.id_municipio)
            INNER JOIN seg_pais 
                ON (seg_departamento.id_pais = seg_pais.id_pais) AND (seg_empresas.id_pais = seg_pais.id_pais)";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT seg_empleado.id_empleado,forma_pago,  seg_metodo_pago.codigo, nom_banco, seg_tipo_cta.tipo_cta, cuenta_bancaria
            FROM
                seg_liq_salario
            INNER JOIN seg_metodo_pago 
                ON (seg_liq_salario.metodo_pago = seg_metodo_pago.id_metodo_pago)
            INNER JOIN seg_empleado 
                ON (seg_liq_salario.id_empleado = seg_empleado.id_empleado)
            INNER JOIN seg_bancos 
                ON (seg_empleado.id_banco = seg_bancos.id_banco)
            INNER JOIN seg_tipo_cta 
                ON (seg_empleado.tipo_cta = seg_tipo_cta.id_tipo_cta)
            WHERE mes = '$mes' AND anio = '$anio' AND estado='1'";
    $rs = $cmd->query($sql);
    $bancaria = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM seg_liq_dlab_auxt
            WHERE mes_liq = '$mes' AND anio_liq = '$anio'";
    $rs = $cmd->query($sql);
    $liqdialab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, id_tipo, seg_liq_incap.fec_inicio, seg_liq_incap.fec_fin, mes, anios, dias_liq, pago_empresa, pago_eps, pago_arl
            FROM
                seg_liq_incap
            INNER JOIN seg_incapacidad 
                ON (seg_liq_incap.id_incapacidad = seg_incapacidad.id_incapacidad)
            WHERE mes = '$mes' AND anios = '$anio'";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_lic, anio_lic, seg_liq_licmp.fec_inicio, seg_liq_licmp.fec_fin, dias_liqs, val_liq 
            FROM
                seg_liq_licmp
            INNER JOIN seg_licenciasmp 
                ON (seg_liq_licmp.id_licmp = seg_licenciasmp.id_licmp)
            WHERE mes_lic = '$mes' AND anio_lic ='$anio'";
    $rs = $cmd->query($sql);
    $lic = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_vac, anio_vac, dias_liqs, val_liq
            FROM
                seg_liq_vac
            INNER JOIN seg_vacaciones
                ON (seg_liq_vac.id_vac = seg_vacaciones.id_vac)
            WHERE mes_vac = '$mes' AND anio_vac = '$anio'";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                id_liqpresoc, id_empleado, id_contrato, val_vacacion, val_cesantia, val_interes_cesantia, val_prima,mes_prestaciones, anio_prestaciones, anio_prestaciones
            FROM
                seg_liq_prestaciones_sociales
            WHERE mes_prestaciones = '$mes' AND anio_prestaciones = '$anio'";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, cant_dias
            FROM seg_liq_dias_lab
            WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $diaslaborados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                seg_liq_segsocial_empdo
            WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, descripcion_lib, val_mes_lib
            FROM
                seg_liq_libranza
            INNER JOIN seg_libranzas 
                ON (seg_liq_libranza.id_libranza = seg_libranzas.id_libranza)
            WHERE mes_lib = '$mes' AND anio_lib = '$anio' and estado = 1";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_mes_embargo
            FROM
                seg_liq_embargo
            INNER JOIN seg_embargos
                ON (seg_liq_embargo.id_embargo = seg_embargos.id_embargo)
            WHERE mes_embargo = '$mes' AND anio_embargo = '$anio'";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_aporte, porcentaje_cuota
            FROM
                seg_liq_sindicato_aportes
            INNER JOIN seg_cuota_sindical
                ON (seg_liq_sindicato_aportes.id_cuota_sindical = seg_cuota_sindical.id_cuota_sindical)
            WHERE mes_aporte = '$mes' AND anio_aporte = '$anio'";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, seg_horas_ex_trab.id_he,codigo, desc_he, factor, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, val_liq, factor
            FROM
                seg_horas_ex_trab
            INNER JOIN seg_tipo_horaex 
                ON (seg_horas_ex_trab.id_he = seg_tipo_horaex.id_he)
            INNER JOIN seg_liq_horex 
                ON (seg_liq_horex.id_he_lab = seg_horas_ex_trab.id_he_trab)
            WHERE mes_he = '$mes' AND anio_he = '$anio'
            ORDER BY id_he";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_viaticos, id_emplead, SUM(valor)AS tot_viat, rango
            FROM   
                (SELECT *
                    FROM 
                        (SELECT seg_detalle_viaticos.id_viaticos, id_emplead, concepto, valor, SUBSTRING(fviatico,1,7) AS rango
                        FROM
                            seg_detalle_viaticos
                        INNER JOIN seg_viaticos 
                            ON (seg_detalle_viaticos.id_viaticos = seg_viaticos.id_viaticos))AS t
                WHERE rango = '$anio-$mes')AS t_res
            GROUP BY id_emplead";
    $rs = $cmd->query($sql);
    $viaticos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_rte_fte, id_empleado, val_ret, mes, anio
            FROM
                seg_retencion_fte
            WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $retfte = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                id_empleado
            FROM
                seg_soporte_ne
            WHERE mes = '$mes' AND anio = '$anio'";
    $rs = $cmd->query($sql);
    $electronica = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `val_liq`
            FROM
                `seg_liq_salario`
            WHERE `mes` = '$mes' AND `anio` = '$anio'";
    $rs = $cmd->query($sql);
    $neto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_envio = 'prod';
$tipo_ref = 'NE';
$response = [];
$errores = '';
if ($mes) {
    $jParams = [
        'sEmail' => 'hospitaljhu.financiera@gmail.com',
        'sPass' => 'Clave2105!'
    ];

    $jApi = [
        'sMethod' => 'classTaxxa.fjTokenGenerate',
        'jParams' => $jParams
    ];

    $url_taxxa = $empresa['endpoint'];
    $token = ['jApi' => $jApi];
    $datatoken = json_encode($token);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url_taxxa);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datatoken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $restoken = curl_exec($ch);
    $rst = json_decode($restoken);
    $tokenApi = $rst->jret->stoken;
    $hoy = date('Y-m-d');
    $ahora = (new DateTime('now', new DateTimeZone('America/Bogota')))->format('H:i:s');
    $nomindempl = '';
    $c = 1;
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $iduser = $_SESSION['id_user'];
    $procesado = 0;
    $incorrectos = 0;
    foreach ($empleados as $o) {
        $id = $o['id_empleado'];
        $keyelec = array_search($id, array_column($electronica, 'id_empleado'));
        $keyneto = array_search($id, array_column($neto, 'id_empleado'));
        $pagado = $neto[$keyneto]['val_liq'];
        if ($keyelec === false && $pagado > 0) {
            $idempleado = $o['no_documento'] . '_' . $o['nombre1'] . '_' . $o['apellido1'] . '_NE_' . $id;
            $key = array_search($id, array_column($bancaria, 'id_empleado'));
            if (false !== $key) {
                $sPaymentForm = $bancaria[$key]['forma_pago'];
                $sPaymentMethod = $bancaria[$key]['codigo'];
                $sBankName = $bancaria[$key]['nom_banco'];
                $sBankAccountType = $bancaria[$key]['tipo_cta'];
                $sBankAccountNo = $bancaria[$key]['cuenta_bancaria'];
                $lPaymentDates = $fec_liq;
            } else {
                $sPaymentForm = $sPaymentMethod = $sBankName = $sBankAccountType = $sBankAccountNo = $lPaymentDates = $lPaymentDates = null;
            }
            $key = array_search($id, array_column($liqdialab, 'id_empleado'));
            if (false !== $key) {
                $nDaysWorked = intval($liqdialab[$key]['dias_liq']);
                $nAuxilioTransporte = floatval($liqdialab[$key]['val_liq_auxt']);
                $salMensual = floatval($liqdialab[$key]['val_liq_dias']);
                $nAuxilioAlimenta = floatval($liqdialab[$key]['aux_alim']);
            } else {
                $nDaysWorked = $nAuxilioTransporte = $salMensual = null;
            }
            $key = array_search($id, array_column($presoc, 'id_empleado'));
            if (false !== $key) {
                $valcesant = floatval($presoc[$key]['val_cesantia']);
                $porcentaje = 12;
                $nPagoIntereses = floatval($presoc[$key]['val_interes_cesantia']);
                $valprimames = floatval($presoc[$key]['val_prima']);
                $key = array_search($id, array_column($diaslaborados, 'id_empleado'));
                if (false !== $key) {
                    $diasprimames = intval($diaslaborados[$key]['cant_dias']);
                }
            } else {
                $valcesant = $porcentaje = $nPagoIntereses = $valprimames = $diasprimames = null;
            }
            $key = array_search($id, array_column($viaticos, 'id_emplead'));
            $nViaticoManuAlojNS = false !== $key ? floatval($viaticos[$key]['tot_viat']) : null;
            $key = array_search($id, array_column($incap, 'id_empleado'));
            if (false !== $key) {
                $valincap = floatval($incap[$key]['pago_empresa'] + $incap[$key]['pago_eps'] + $incap[$key]['pago_arl']);
                $tipoincap = intval($incap[$key]['id_tipo']);
                $inincap =  $incap[$key]['fec_inicio'];
                $diaincap =  intval($incap[$key]['dias_liq']);
                $finincap =  $incap[$key]['fec_fin'];
            } else {
                $valincap = $tipoincap = $inincap = $finincap = $diaincap = null;
            }
            $key = array_search($id, array_column($lic, 'id_empleado'));
            if (false !== $key) {
                $vallic = floatval($lic[$key]['val_liq']);
                $inlic =  $lic[$key]['fec_inicio'];
                $dialic =  intval($lic[$key]['dias_liqs']);
                $finlic =  $lic[$key]['fec_fin'];
            } else {
                $vallic = $inlic = $dialic = $finlic = null;
            }
            $key = array_search($id, array_column($emb, 'id_empleado'));
            $valEmbargo = false !== $key ? floatval($emb[$key]['val_mes_embargo']) : null;
            $key = array_search($id, array_column($sind, 'id_empleado'));
            if (false !== $key) {
                $valSind = floatval($sind[$key]['val_aporte']);
                $porcSind =  null;
            } else {
                $valSind = $porcSind = null;
            }
            $key = array_search($id, array_column($segsoc, 'id_empleado'));
            if (false !== $key) {
                $salud = floatval($segsoc[$key]['aporte_salud_emp']);
                $pension =  floatval($segsoc[$key]['aporte_pension_emp']);
                $psolid = intval($segsoc[$key]['aporte_solidaridad_pensional']) > 0 ? floatval($segsoc[$key]['aporte_solidaridad_pensional']) : null;
                $pPS = intval($psolid) > 0 ? $segsoc[$key]['porcentaje_ps'] : null;
                if ($psolid > 0) {
                    $psolida =  ($psolid * 0.5) / $pPS;
                    $pPSa = 0.50;
                    $psolidb = $psolid - $psolida;
                    $pPSb = $pPS - 0.50;
                    $pPSa = $pPSa . '0';
                    $pPSb = $pPSb . '0';
                } else {
                    $psolida =  null;
                    $pPSa = null;
                    $psolidb = null;
                    $pPSb = null;
                }
            } else {
                $salud = $pension = $psolid =  $pPS  = null;
            }
            $key = array_search($id, array_column($prima, 'id_empleado'));
            if (false !== $key) {
                $valprima = floatval($prima[$key]['val_liq_ps']);
                $diasprima =  intval($prima[$key]['cant_dias']);
            } else {
                $valprima = $diasprima = null;
            }
            $key = array_search($id, array_column($lib, 'id_empleado'));
            if (false !== $key) {
                $descripLib = $lib[$key]['descripcion_lib'];
                $valLib =  floatval($lib[$key]['val_mes_lib']);
            } else {
                $descripLib = $valLib = null;
            }
            $key = array_search($id, array_column($vac, 'id_empleado'));
            if (false !== $key) {
                $valvac = floatval($vac[$key]['val_liq']);
                $diavac =  intval($vac[$key]['dias_liqs']);
            } else {
                $valvac = $diavac = null;
            }
            $listhoex = [];
            $valHoEx = 0;
            foreach ($hoex as $he) {
                if ($he['id_empleado'] === $o['id_empleado']) {
                    switch (intval($he['codigo'])) {
                        case 1:
                            $tiphe = 'HED';
                            break;
                        case 2:
                            $tiphe =  'HEN';
                            break;
                        case 3:
                            $tiphe = 'HRN';
                            break;
                        case 4:
                            $tiphe = 'HEDDF';
                            break;
                        case 5:
                            $tiphe = 'HRDDF';
                            break;
                        case 6:
                            $tiphe =  'HENDF';
                            break;
                        case 7:
                            $tiphe = 'HRNDF';
                            break;
                    }
                    $listhoex[] = ['wWorktimeCode' => $tiphe, 'nquantity' => $he['cantidad_he'], 'nPaid' => floatval($he['val_liq']), 'nRateDelta' => floatval($he['factor']), 'tSince' =>  $he['fec_inicio'] . 'T' . $he['hora_inicio'], 'tUntil' => $he['fec_fin'] . 'T' . $he['hora_fin']];
                    $valHoEx = $valHoEx +  $he['val_liq'];
                }
            }
            $devengado = floatval($salMensual  + $nAuxilioTransporte + $nViaticoManuAlojNS + $nAuxilioAlimenta + $valincap + $vallic + $valprima + $valvac + $valHoEx);
            $ccesantia = $valcesant > 0 ? ['wIncomeCode' => 'Cesantias', 'nAmount' => $valcesant, 'nPagoIntereses' => $nPagoIntereses, 'nPercentage' => $porcentaje] : null;
            $cprima = $valprimames > 0 ? ['wIncomeCode' => 'Primas', 'nAmount' => $valprimames, 'nPagoNS' => 0, 'nPagoS' => $valprimames, 'nQuantity' => $diasprimames] : null;
            $ctransp = $nAuxilioTransporte > 0 ?  ['wIncomeCode' => 'Transporte', 'nAuxilioTransporte' =>  $nAuxilioTransporte, 'nViaticoManuAlojS' =>  null, 'nViaticoManuAlojNS' =>  $nViaticoManuAlojNS] : null;
            $cAlim = $nAuxilioAlimenta > 0 ?  ['wIncomeCode' => 'Auxilio', 'nAuxilioS' => $nAuxilioAlimenta, 'nAuxilioNS' =>  null] : null;
            $valincap = $valincap > 0 ? ['wIncomeCode' => 'Incapacidad', 'nAmount' =>   $valincap, 'sTipo' =>  $tipoincap, 'nQuantity' =>  $diaincap, 'tSince' =>  $inincap, 'tUntil' => $finincap] : null;
            $vallic = $vallic > 0 ? ['wIncomeCode' => 'LicenciaMP', 'tSince' => $inlic, 'tUntil' => $finlic, 'nAmount' =>  $vallic, 'nQuantity' => $dialic] : null;
            $bsp = ['wIncomeCode' => 'Bonificacion', 'nBonificacionS' =>  null, 'nBonificacionNS' =>  null];
            $aIncomes = [];
            if ($cprima !== null) {
                $aIncomes[] = $cprima;
            }
            if ($ccesantia !== null) {
                $aIncomes[] = $ccesantia;
            }
            if ($ctransp !== null) {
                $aIncomes[] = $ctransp;
            }
            if ($cAlim !== null) {
                $aIncomes[] = $cAlim;
            }
            if ($valincap !== null) {
                $aIncomes[] = $valincap;
            }
            if ($vallic !== null) {
                $aIncomes[] = $vallic;
            }
            if ($bsp !== null) {
                $aIncomes[] = $bsp;
            }
            /*$aIncomes = [
            //['wIncomeCode' => 'Teletrabajo', 'nAmount' => null],
            //['wIncomeCode' => 'ApoyoSost', 'nAmount' => null],
            //['wIncomeCode' => 'BonifRetiro', 'nAmount' => null],
            //['wIncomeCode' => 'Dotacion', 'nAmount' => null],
            //['wIncomeCode' => 'Indemnizacion', 'nAmount' => null],
            //['wIncomeCode' => 'Reintegro', 'nAmount' => null],
            //['wIncomeCode' => 'Comision', 'nAmount' => null],
            //['wIncomeCode' => 'PagoTercero', 'nAmount' => null],
            //['wIncomeCode' => 'Anticipo', 'nAmount' => null],
            //['wIncomeCode' => 'Comision', 'nAmount' => null],
            //['wIncomeCode' => 'Auxilio', 'nAuxilioS' => null, 'nAuxilioNS' =>  null],
            //['wIncomeCode' => 'Compensacion', 'nCompensacionO' =>  null, 'nCompensacionE' =>  null],
            //['wIncomeCode' => 'Bonificacion', 'nBonificacionS' =>  null, 'nBonificacionNS' =>  null],
            //['wIncomeCode' => 'BonoEPCTV', 'nPagoS' =>  null, 'nPagoNS' =>  null, 'nPagoAlimentacionS' =>  null, 'nPagoAlimentacionNS' =>  null],
            //['wIncomeCode' => 'LicenciaR', 'tSince' => null, 'tUntil' => null, 'nAmount' => null, 'nQuantity' =>  null],
            //['wIncomeCode' => 'LicenciaNR', 'tSince' => null, 'tUntil' => null, 'nQuantity' => null],
            //['wIncomeCode' => 'VacacionesComunes', 'nAmount' => null, 'nQuantity' => null, 'tSince' => null, 'tUntil' => null],
            // ['wIncomeCode' => 'VacacionesCompensadas', 'nAmount' => $valvac, 'nQuantity' => $diavac],
            //['wIncomeCode' => 'HuelgaLegal', 'nQuantity' => null, 'tSince' => null, 'tUntil' => null],
            //['wIncomeCode' => 'OtroConcepto', 'nConceptoS' => null, 'nConceptoNS' => null, 'sDescription' => null, 'xDescription' => null]
        ];*/
            $aContract = [
                [
                    'nsalarybase' => floatval($o['salario_basico']),
                    'wcontracttype' => mb_strtoupper($o['codigo_netc']),
                    'tcontractsince' => $o['fech_inicio'],
                    'tcontractuntil' => $o['fec_retiro'],
                    'wpayrollperiod' => '5',
                    'wdianemployeetype' => $o['tip_emp'],
                    'wdianemployeesubtype' => $o['subt_emp'],
                    'bAltoRiesgoPension' => ($o['alto_riesgo_pension'] == '1' ? true : false),
                    'bSalarioIntegral' => ($o['salario_integral'] == '1' ? true : false)
                ]
            ];
            $cemba = $valEmbargo > 0 ? ["wDeductionCode" => "EmbargoFiscal", "nAmount" => $valEmbargo] : null;
            $csind = $valSind > 0 ? ["wDeductionCode" => "Sindicato", "nAmount" =>  $valSind, "nPercentage" => $porcSind] : null;
            $cpsolidaria = $psolid > 0 ? ["wDeductionCode" => "FondoSP", "nPercentage" => $pPSa, "nDeduccionsp" => $psolida, "nDeduccionSub" => $psolidb, "nPorcentajeSub" => $pPSb] : null;
            $clib = $valLib > 0 ? ["wDeductionCode" => "Libranza", "nAmount" => $valLib, "sDescription" => $descripLib, "xDescription" => $descripLib == '' ? null : base64_encode($descripLib)] : null;
            $aDeductions = [];
            if ($cemba !== null) {
                $aDeductions[] = $cemba;
            }
            if ($csind !== null) {
                $aDeductions[] = $csind;
            }
            if ($cpsolidaria !== null) {
                $aDeductions[] = $cpsolidaria;
            }
            if ($clib !== null) {
                $aDeductions[] = $clib;
            }
            $aDeductions[] = ["wDeductionCode" => "Salud", "nAmount" => $salud, "nPercentage" => 4];
            $aDeductions[] = ["wDeductionCode" => "FondoPension", "nAmount" => $pension, "nPercentage" => 4];

            $key = array_search($id, array_column($retfte, 'id_empleado'));
            if (false !== $key) {
                $rtefte = floatval($retfte[$key]['val_ret']);
                if (!(intval($rtefte) === 0)) {
                    $aDeductions[] = ["wDeductionCode" => "RetencionFuente", "nAmount" => $rtefte];
                } else {
                    $rtefte = 0;
                }
            }
            $deducciones =  floatval($valEmbargo + $valSind + $salud + $pension + $psolid + $valLib + $rtefte);
            /*$aDeductions = [
            //["wDeductionCode" => "Educacion", "nAmount" => null],
            //["wDeductionCode" => "Reintegro", "nAmount" => null],
            //["wDeductionCode" => "Anticipo", "nAmount" => null],
            //["wDeductionCode" => "PagoTercero", "nAmount" => null],
            //["wDeductionCode" => "OtraDeduccion", "nAmount" => null],
            //["wDeductionCode" => "Deuda", "nAmount" => null],
            //["wDeductionCode" => "Cooperativa", "nAmount" => null],
            //["wDeductionCode" => "AFC", "nAmount" => null],
            //["wDeductionCode" => "PensionVoluntaria", "nAmount" => null],
            //["wDeductionCode" => "PlanComplementarios", "nAmount" => null],            
            //["wDeductionCode" => "Sancion", "nAmount" => null, "nSancionPriv" => null, "nSancionPublic" => null]
        ];*/
            $aWorkTimeDetails = $listhoex;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $sql = "SELECT consecutivo FROM seg_consecutivo LIMIT 1";
                $rs = $cmd->query($sql);
                $cons = $rs->fetch();
                $consecutivo = $cons['consecutivo'];
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $numero = $anio . $mes . str_pad($consecutivo, 3, "0", STR_PAD_LEFT);
            $idne = $tipo_ref . '-' . $numero;
            $indicene = strtolower($tipo_ref) . $numero;
            $empleado = [];
            $empleado[$idempleado] =
                [
                    'wdoctype' =>  $o['codigo_ne'],
                    'sDocId' => $o['no_documento'],
                    'sworkercode' => $o['id_empleado'],
                    'spersonnamefirst' => $o['nombre1'],
                    'lpersonnamesothers' => $o['nombre2']  == '' ? '-' : $o['nombre2'],
                    'spersonsurname' => $o['apellido1'],
                    'lpersonsurnameothers' => $o['apellido2'],
                    'jcontact' => [
                        "semail" => $o['correo'],
                        'jaddress' => [
                            'wCountrycode' => $o['codigo_pais'],
                            'sStateCode' => $o['codigo_dpto'],
                            'sCityCode' => $o['codigo_municipio'],
                            'sstreet' => $o['direccion'],
                        ]
                    ],
                    'apayrollinfo' => [
                        'NE-' . $c => [
                            'xnotes' => base64_encode('Comentarios'),
                            'sreference' => $idne,
                            "sprefix" => $tipo_ref,
                            "ssuffix" => $numero,
                            'ndaysworked' => $nDaysWorked,
                            'ntotalincomes' => $devengado,
                            'ntotaldeductions' =>  $deducciones,
                            'nperiodbasesalary' => floatval($o['salario_basico']),
                            'npayable' => $devengado - $deducciones,
                            'aIncomes' => $aIncomes,
                            'aDeductions' => $aDeductions,
                            'aWorkTimeDetails' => $aWorkTimeDetails,
                        ]

                    ],
                    'aContract' => $aContract,
                    'aPaymentInfo' => [
                        [
                            'spaymentform' => $sPaymentForm,
                            'spaymentmethod' => $sPaymentMethod,
                            'sbankname' => $sBankName,
                            'sbankaccounttype' => $sBankAccountType,
                            'sbankaccountno' => $sBankAccountNo,
                            'lpaymentdates' => $lPaymentDates
                        ]
                    ],
                ];
            $c++;
            $jPayroll = [
                "wEnvironment" => $tipo_envio,
                'tcalculatedsince' => $fec_i,
                'tcalculateduntil' => $fec_f,
                'tissued' => $hoy,
                'jemployer' => [
                    'sbusinessname' => $empresa['nombre'],
                    'spersonnamefirst' => $empresa['nombre'],
                    'spersonnamesothers' => '',
                    'spersonsurname' => $empresa['nombre'],
                    'spersonsurnameothers' => '',
                    'wdoctype' => 'NIT',
                    'sDocID' => $empresa['nit'],
                    'jcontact' => [
                        'jAddress' => [
                            'wCountrycode' => $empresa['codigo_pais'],
                            'sStateCode' => $empresa['codigo_dpto'],
                            'sCityCode' => $empresa['codigo_dpto'] . $empresa['codigo_municipio'],
                            'sStreet' => $empresa['direccion'],
                        ]
                    ]
                ],
                'aWorkers' => $empleado
            ];
            $jParams = [
                'bAsync' => false,
                'jPayroll' => $jPayroll,
            ];

            $jApi = [
                'sMethod' => "classTaxxa.fjPayrollAdd",
                'jParams' => $jParams
            ];

            //echo json_encode($empjson);
            $nomina = [
                'sToken' => $tokenApi,
                //'iNonce' => $iNonce,
                'jApi' => $jApi
            ];
            $json_string = json_encode($nomina);
            $file = 'empleados' . $c . '.json';
            file_put_contents($file, $json_string);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $empresa['endpoint']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nomina));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resnom = json_decode(curl_exec($ch), true);
            if ($resnom['rerror'] == 0) {
                $shash = $resnom['aresult'][$indicene]['shash'];
                $sreference = $resnom['aresult'][$indicene]['sreference'];
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO seg_soporte_ne (id_empleado, shash, referencia, mes, anio, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $shash, PDO::PARAM_STR);
                    $sql->bindParam(3, $sreference, PDO::PARAM_STR);
                    $sql->bindParam(4, $mes, PDO::PARAM_STR);
                    $sql->bindParam(5, $anio, PDO::PARAM_STR);
                    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $consUp = $consecutivo + 1;
                        $sql = "UPDATE seg_consecutivo SET consecutivo = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $consUp, PDO::PARAM_INT);
                        $sql->execute();
                        $procesado++;
                    } else {
                        echo json_encode($sql->errorInfo()[2]);
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
                }
            } else {
                $incorrectos++;
                $mnj = '<ul>';
                foreach ($resnom['smessage'] as $m => $value) {
                    $mnj .= '<li>' . $value;
                }
                $mnj .= '</ul>';
                $errores .= 'Error:' . $resnom['rerror'] . '<br>Mensaje: ' . $mnj . '----------<br>';
            }
        }
    }
}
$file = 'loglastsend.txt';
file_put_contents($file, $resnom);
$response = [
    'msg' => 'ok',
    'procesados' => "Se ha procesado <b>" . $procesado . "</b> soporte(s) para nómina electrónica",
    'error' => $errores,
    'incorrec' => $incorrectos,
];
echo json_encode($response);
