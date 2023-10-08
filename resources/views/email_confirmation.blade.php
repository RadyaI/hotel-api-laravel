<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        h5 {
            color: #777;
        }

        p {
            color: #555;
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #000;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Royal Hotel</h1>
    <h3>Halo {{ $nama_tamu }}, </h3>
    <h1 align="center">Pesanan kamu sudah di konfirmasi yaa</h1>
    <h4 align="center">untuk mengecek kamu bisa pergi ke halaman CHECK BOOKING dan masukkan id kamu yaitu {{ $id_transaksi }}</h4>
</body>

</html>