<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/../../autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Função para enviar e-mails com suporte a anexos.
 * 
 * @param string $subject Assunto do e-mail.
 * @param string $dstmail E-mail do destinatário.
 * @param string $body Corpo do e-mail (em HTML ou texto).
 * @param string $srcname Nome do remetente.
 * @param array $attachments Array de anexos (caminhos completos dos arquivos ou array com 'path' e 'name').
 * 
 * @return bool Retorna `true` se o e-mail for enviado com sucesso, caso contrário, `false`.
 */
function sendMail($subject, $dstmail, $body, $srcname = "", $attachments = [])
{
    // Acessa as variáveis globais
    global $smtp;
    extract($smtp);
    
    $srcname = $srcname ?? $smtp["name"];

    $mail = new PHPMailer(true);
    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Desativa o debug (0 para desativar, 2 para ativar)
        $mail->Host = $mailHost;
        $mail->Port = $mailPort;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailUserPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        // Configurações do e-mail
        $mail->CharSet = 'UTF-8'; // Define o charset para UTF-8
        $mail->setFrom($srcmail, $srcname); // E-mail e nome de origem
        $mail->addAddress($dstmail); // E-mail do destinatário
        $mail->Subject = $subject; // Assunto do e-mail

        // Adiciona anexos
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    // Se for um array, assume que tem 'path' e opcionalmente 'name'
                    $filePath = $attachment['path'];
                    $fileName = $attachment['name'] ?? basename($filePath);
                    $mail->addAttachment($filePath, $fileName);
                } else {
                    // Se for string, assume que é apenas o caminho do arquivo
                    $mail->addAttachment($attachment);
                }
            }
        }

        // Corpo do e-mail
        $mail->isHTML(true); // Define o corpo do e-mail como HTML
        $mail->Body = $body; // Conteúdo do e-mail

        // Envia o e-mail
        $mail->send();
        return true; // E-mail enviado com sucesso
    } catch (Exception $e) {
        // Em caso de erro, retorna false
        return false;
    }
}
