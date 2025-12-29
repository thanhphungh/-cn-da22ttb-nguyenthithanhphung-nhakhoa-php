<?php
// Thư viện JWT đơn giản (không phụ thuộc ngoài)
// Lưu ý: Đây là bản rút gọn, chỉ dùng cho demo. Khi triển khai thực tế nên dùng thư viện chuẩn như firebase/php-jwt.

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Khóa bí mật để ký JWT (bạn có thể đổi thành chuỗi mạnh hơn)
const JWT_SECRET = "phongnha_secret_key";

// Hàm tạo JWT
function jwt_sign(array $payload, $exp = 3600) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $exp;

    $header_encoded  = base64url_encode(json_encode($header));
    $payload_encoded = base64url_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET, true);
    $signature_encoded = base64url_encode($signature);

    return "$header_encoded.$payload_encoded.$signature_encoded";
}

// Hàm kiểm tra JWT
function jwt_verify($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

    $signature = base64url_encode(
        hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET, true)
    );

    if (!hash_equals($signature, $signature_encoded)) {
        return false;
    }

    $payload = json_decode(base64url_decode($payload_encoded), true);
    if (!$payload) return false;

    if (isset($payload['exp']) && time() > $payload['exp']) {
        return false; // token hết hạn
    }

    return $payload;
}
?>
