<?php


namespace LARAVEL\Controllers\Admin;
use Illuminate\Http\Request;
use LARAVEL\Models\CounterModel;
class chatGPTController
{
    public function chat(Request $request)
    {
        // chat.php

        header('Content-Type: application/json');

        // Chỉ cho phép phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Chỉ hỗ trợ phương thức POST']);
            exit;
        }

        // Lấy dữ liệu JSON từ yêu cầu
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu tham số "message"']);
            exit;
        }

        $userMessage = $input['message'];

        // API Key của bạn
        $apiKey = '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'; // Thay thế bằng API Key thực tế

        // Dữ liệu gửi đến OpenAI
        $data = [
            'model' => 'GPT-3.5',
            'messages' => [
                ['role' => 'user', 'content' => $userMessage]
            ]
        ];

        // Khởi tạo cURL
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Thực hiện yêu cầu
        $response = curl_exec($ch);

        // Kiểm tra lỗi
        if (curl_errno($ch)) {
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi kết nối đến OpenAI API']);
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        // Giải mã phản hồi
        $responseData = json_decode($response, true);
        var_dump($responseData); die();
        if (isset($responseData['choices'][0]['message']['content'])) {
            $reply = $responseData['choices'][0]['message']['content'];
            echo json_encode(['reply' => $reply]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi xử lý phản hồi từ OpenAI API']);
        }
    }
}
