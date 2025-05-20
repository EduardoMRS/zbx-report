<?php
function returnError($output = true)
{
    $error = error_get_last();

    if ($error !== null && $error['type'] === E_ERROR) {
        $errorResponse = [
            'status' => 'error',
            'message' => 'Erro interno no servidor',
            'error_details' => [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]
        ];

        if ($output) {
            header('Content-Type: application/json');
            echo json_encode($errorResponse);
        }

        return $errorResponse;
    }

    return null;
}
