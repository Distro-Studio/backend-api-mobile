<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h4 {
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f8f8;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .section-row {
            display: flex;
            justify-content: space-between;
        }
        .section-row div {
            width: 48%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Slip Gaji</h2>

        <div class="section">
            <h4>Data Karyawan</h4>
            <p>Nama: John Doe</p>
            <p>Jabatan: Staff IT</p>
            <p>Bulan: September 2024</p>
        </div>

        <div class="section">
            <h4>Rincian Gaji</h4>
            <div class="section-row">
                <div>
                    <h5>Pendapatan</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th>Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gaji Pokok</td>
                                <td>5,000,000</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Transportasi</td>
                                <td>500,000</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Makan</td>
                                <td>300,000</td>
                            </tr>
                            <tr>
                                <td>Bonus</td>
                                <td>1,000,000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <h5>Potongan</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th>Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Potongan Pajak</td>
                                <td>(400,000)</td>
                            </tr>
                            <tr>
                                <td>Potongan BPJS</td>
                                <td>(200,000)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="section">
            <p class="total">Total Gaji Diterima: Rp 6,200,000</p>
        </div>

        <div class="section">
            <p>Tanggal Cetak: 10 September 2024</p>
            <p>PT. Contoh Perusahaan</p>
        </div>
    </div>
</body>
</html>
