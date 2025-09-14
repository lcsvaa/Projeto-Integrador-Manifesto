<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $novId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if ($novId <= 0) {
        throw new Exception('ID da novidade ausente', 422);
    }

    $stmt = $pdo->prepare('SELECT titulo, conteudo, imagemNovidade FROM tb_novidades WHERE idNovidade = :id');
    $stmt->execute(array(':id' => $novId));
    $novidade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$novidade) {
        throw new Exception('Novidade não encontrada', 404);
    }

    $titulo     = $novidade['titulo'];
    $conteudo   = $novidade['conteudo'];
    $imagemNome = $novidade['imagemNovidade'];
    $imagemPath = dirname(__FILE__).'/../uploads/novidades/'.$imagemNome;

    $emails = $pdo->query('SELECT emailNews FROM tb_newsletter')->fetchAll(PDO::FETCH_COLUMN, 0);
    if (empty($emails)) {
        throw new Exception('Nenhum assinante para enviar', 404);
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER') ? getenv('SMTP_USER') : 'ryanhanada12@gmail.com'; 
    $mail->Password   = getenv('SMTP_PASS') ? getenv('SMTP_PASS') : 'rexi wtyh nkoe fkvl'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom($mail->Username, 'Manifesto');
    $mail->SMTPDebug = 0;

    foreach ($emails as $e) {
        $mail->addBCC($e);
    }

    $cid = 'nov'.$novId;
    if (file_exists($imagemPath)) {
        $mail->addEmbeddedImage($imagemPath, $cid);
    }

    $link = "http://localhost/manifesto/novidades.php";
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Manifesto News!!!';
    
    $body = '<!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8">
    <style>
    body{
        margin:0;
        padding:0;
        background:#fff;
        font-family:Arial,Helvetica,sans-serif;
    }

    .wrapper{
        max-width:500px;         
        margin:0 auto;          
        background:#fff;
    }

    .header{
        background:#c01850;
        border-radius:10px;
        padding:24px 16px;
        text-align:center;
        color:#ffffff;
        margin: 0 0 16px auto;
    }

    img{
        border:1px solid #c01850;
        border-radius:10px;
        width:100%;             
        max-width:99%;
        height:auto;
        display:block;
        margin:0 auto;
    }

    .content{
        margin:16px auto;
        padding:20px 18px 10px;
        background-color: #fff;
        font-size:16px;
        line-height:1.5;
        border:2px solid #c01850;
        border-radius:10px;
    }

    .btn{
        display:block;        
        width:max-content;    
        margin:0 auto;          
        
        background:#e91e63;
        text-decoration:none;
        color:#ffffff;
        
        padding:12px 22px;
        border-radius:4px;
        font-weight:bold;
    }

    .footer{
        padding:10px;
        font-size:12px;
        color:#e91e63;
        text-align:center;
    }
    </style>
    </head>
    <body>
    <div class="wrapper">

        <div class="header"><h1>'.$titulo.'</h1></div>

        <img src="cid:'.$cid.'" alt="'.$titulo.'">

        <div class="content">
            <p>'.$conteudo.'</p>
            <p><a style="color:#ffffff;" href="'.$link.'" class="btn">Ler no site</a></p>
        </div>

        <div class="footer">
        Você recebeu este e-mail porque se inscreveu em nossa newsletter.   
        <br><a>remover cadastro</a>   
        </div>
        
    </div>
    </body>
    </html>';

    $mail->AltBody = $titulo."\n\n".$conteudo."\n\nLeia em: ".$link;
    $mail->msgHTML($body);
    $mail->send();

    echo json_encode(array('success' => true, 'sent' => true));
} catch (Exception $e) {
    $code = $e->getCode() ? $e->getCode() : 500;
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        header("HTTP/1.1 $code");
    }
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}