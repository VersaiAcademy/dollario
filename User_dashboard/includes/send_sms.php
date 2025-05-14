<?php
function sendOTPviaSMS($mobile, $otp) {
    $apiKey = "YOUR_FAST2SMS_API_KEY";
    $message = "Your OTP is $otp. Do not share it.";
    $sender = "FSTSMS";

    $data = [
        "sender_id" => $sender,
        "message" => $message,
        "language" => "english",
        "route" => "p",
        "numbers" => $mobile,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "authorization: $apiKey",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
?>
