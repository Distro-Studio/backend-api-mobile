<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Slip Gaji</title>

    <link
      href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />

    <style>
      :root {
        --p50: #e9fff6;
        --p100: #cffbe9;
        --p200: #a0f7dc;
        --p300: #6ee8cd;
        --p400: #47d1bf;
        --p500: #16b3ac;
        --p600: #109399;
        --p700: #0b7180;
        --p800: #075267;
        --p900: #043c55;
        --drawer: #005234aa;

        --p500a0: rgba(22, 179, 171, 0.6);
        --p500a: rgba(22, 179, 171, 0.5);
        --p500a1: rgba(22, 179, 171, 0.45);
        --p500a2: rgba(22, 179, 171, 0.35);
        --p500a3: rgba(22, 179, 171, 0.25);
        --p500a4: rgba(22, 179, 171, 0.15);
        --p500a5: rgba(22, 179, 171, 0.1);

        --ba: #050505a6;
        --bt: #333333;
        --wt: #eeeeee;

        --divider: #8d8d8d10;
        --divider2: #8d8d8d16;
        --divider3: #8d8d8d25;
        --divider-text: #8d8d8dc1;

        --dark: #191919;
      }

      * {
        padding: 0;
        margin: 0;
        font-family: inter;
      }
      p,
      td,
      th {
        font-size: 12px;
        font-weight: normal;
      }
      body {
        padding: 16px;
        border: 1px solid red;
        width: 100%;
        height: 842px;
        max-width: 760px;
        /* max-height: 842px; */
      }
      .mb2 {
        margin-bottom: 8px;
      }
      .mb4 {
        margin-bottom: 12px;
      }
      .mb8 {
        margin-bottom: 20px;
      }
      .container {
        position: relative;
      }
      .label {
        width: 140px;
        opacity: 0.6;
      }
      .ib {
        display: inline-block;
      }
      .header {
        font-size: 24px;
      }
      .kop {
        position: relative;
      }
      .logo {
        width: 120px;
        position: absolute;
        top: 0;
        right: 0;
      }
      .bp {
        color: var(--p500);
        font-weight: bold;
      }
      .ikc {
        padding: 16px;
        background: var(--p500a5);
        border-radius: 8px;
      }
      .nama {
        font-size: 16px;
        font-weight: bold;
      }
      td {
        padding-right: 24px;
        padding-bottom: 8px;
      }
      .trans {
        opacity: 0.6;
      }
      .td1 {
        padding-right: 32px;
      }
      .gh {
        background: var(--p500);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 8px 16px;
        padding-top: 4px;
        position: relative;
      }
      .hr {
        position: absolute;
        top: 8px;
        right: 16px;
      }
      .brl {
        border: 1px solid var(--divider3);
      }
      .wg {
        width: calc(50% - 39px);
      }
      .tal {
        text-align: left;
        padding: 0;
      }
      .tar {
        text-align: right;
        padding: 0;
      }

      .gaji-header {
        background: #16b3ac;
        color: white;
      }
      .gaji-header th,
      .gaji-body td,
      .gaji-foot td {
        padding: 8px 16px;
      }
      /* .gb-l {
        border-left: 1px solid var(--divider3);
      }
      .gb-r {
        border-right: 1px solid var(--divider3);
      } */
      .gh-l {
        border-radius: 8px 0 0 0;
        font-weight: 500;
      }
      .gh-r {
        border-radius: 0 8px 0 0;
        font-weight: 500;
      }
      table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
      }
      .gaji-foot {
        background: var(--p500a5);
        color: var(--p500);
      }
      .gf-l {
        font-weight: 500;
        border-radius: 0 0 0 8px;
      }
      .gf-r {
        font-weight: 500;
        border-radius: 0 0 8px 0;
      }
      .table-container {
        border-radius: 8px;
        border: 1px solid var(--divider3);
        transform: translateY(50);
      }
    </style>
  </head>
  <body>
    <div class="container">
      <!-- Kop -->
      <div class="kop mb8">
        <h1 class="header mb4">Slip Gaji</h1>

        <div class="mb2">
          <p class="ib label">Nama Perusahaan</p>
          <p class="ib">RS. Kasih Ibu Surakarta</p>
        </div>

        <div class="mb2">
          <p class="ib label">Telepon</p>
          <p class="ib">08928374829</p>
        </div>

        <div class="mb2">
          <p class="ib label">Email</p>
          <p class="ib">admin@rski.com</p>
        </div>

        <div class="mb2">
          <p class="ib label">Tanggal Gaji</p>
          <p class="ib">1 Januari 2024</p>
        </div>

        <div class="mb2">
          <p class="ib label">Periode</p>
          <p class="ib">Januari 2024</p>
        </div>

        <img src="{{public_path('style/logo.png')}}" class="logo" />
      </div>

      <!-- Info Karyawan -->
      <div class="ikc mb8">
        <p class="bp mb4">Informasi Karyawan</p>
        <p class="nama mb4">{{$user->nama}}</p>

        <table>
          <tr>
            <td class="trans">Nomor Induk Karyawan</td>
            <td class="td1">{{$data->nik}}</td>
            <td class="trans">Unit Kerja</td>
            <td>{{$data->unitkerja->nama_unit}}</td>
          </tr>

          <tr>
            <td class="trans">Kelompok Gaji</td>
            <td class="td1">{{$data->kelompok_gaji->nama_kelompok}}</td>
            <td class="trans">Jabatan</td>
            <td>{{$data->jabatan->nama_jabatan}}</td>
          </tr>

          <tr>
            <td class="trans">Status Karyawan</td>
            <td class="td1">{{$data->statusKaryawan->label}}</td>
            <td class="trans">Kompetensi Profesi</td>
            <td>Farmasi</td>
          </tr>

          <tr>
            <td class="trans">Telepon</td>
            <td class="td1">0867672354</td>
          </tr>
        </table>
      </div>

      <!-- Gaji -->
      <div class="mb8">
        <div
          class="ib table-container"
          style="margin-right: 8px; width: calc(50% - 8.7px)"
        >
          <table>
            <thead>
              <tr class="gaji-header">
                <th class="tal gh-l">Pendapatan</th>
                <th class="tar gh-r">Total</th>
              </tr>
            </thead>
            <!-- Loop tr nya -->
            <tbody>
                @php
                    $totalpendapatan = 0;
                @endphp
                @foreach ($gaji->detail_gajis as $d)
                    @if ($d->kategori_gaji_id == 1 || $d->kategori_gaji_id == 2)
                        <tr class="gaji-body">
                            <td class="tal gb-l">{{$d->nama_detail}}</td>
                            <td class="tar gb-r">{{ 'Rp. ' . number_format($d->besaran, 0, ',', '.') }}</td>
                        </tr>
                    @php
                        $totalpendapatan += $d->besaran;
                    @endphp
                    @endif

                @endforeach
            </tbody>
            <tfoot>
              <tr class="gaji-foot">
                <td class="tal gf-l">Total Penghasilan</td>
                <td class="tar gf-r">{{ 'Rp. ' . number_format($totalpendapatan, 0, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="ib table-container" style="width: calc(50% - 8.7px)">
          <table>
            <thead>
              <tr class="gaji-header">
                <th class="tal gh-l">Potongan</th>
                <th class="tar gh-r">Total</th>
              </tr>
            </thead>
            <!-- Loop tr nya -->
            <tbody>
                @php
                    $totalpotongan = 0;
                @endphp
                @foreach ($gaji->detail_gajis as $d)
                    @if ($d->kategori_gaji_id == 3)
                        <tr class="gaji-body">
                            <td class="tal gb-l">{{$d->nama_detail}}</td>
                            <td class="tar gb-r">{{ 'Rp. ' . number_format($d->besaran, 0, ',', '.') }}</td>
                        </tr>
                    @php
                        $totalpotongan += $d->besaran;
                    @endphp
                    @endif

                @endforeach

            </tbody>
            <tfoot>
              <tr class="gaji-foot">
                <td class="tal gf-l">Total Potongann</td>
                <td class="tar gf-r">{{ 'Rp. ' . number_format($totalpotongan, 0, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- TTD & take home pay -->
      <div style="margin-bottom: 48px">
        <div class="ib">
          <p style="margin-bottom: 48px">Diketahui oleh</p>
          <p style="font-weight: 600">HRD</p>
          <p>Personalia RS. Kasih Ibu Surakarta</p>
        </div>

        <div
          class="ib"
          style="
            background: var(--p500a5);
            border-radius: 8px;
            padding: 16px;
            float: right;
          "
        >
          <p class="tar mb2" style="font-weight: 500">Take Home Pay</p>
          <p
            class="tar"
            style="font-size: 24px; font-weight: 600; color: var(--p500)"
          >
          {{ 'Rp. ' . number_format($gaji->take_home_pay, 0, ',', '.') }}
          </p>
        </div>
      </div>

      <div>
        <div
          class="ib"
          style="opacity: 0.6; max-width: 400px; margin-top: 60px"
        >
          <p>Notes:</p>
          <p>
            Slip gaji ini telah terverifikasi secara elektronik dan tidak
            memerlukan tanda tangan basah.
          </p>
        </div>

        <div class="ib" style="float: right">
          <p style="margin-left: 16px">Scan Disini</p>
        <img src="{{public_path('style/qr.png')}}" style="width: 100px" />
        </div>
      </div>
    </div>
  </body>
</html>