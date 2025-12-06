<h1>Monitoring Suhu</h1>
<meta http-equiv="refresh" content="5">

@if($data)
    <h2>Suhu Saat Ini: {{ $data->suhu }} °C</h2>
    <h2>Kelembaban: {{ $data->kelembaban }} %</h2>
    <p>Update: {{ $data->created_at }}</p>
@else
    <h2>Menunggu data dari ESP32...</h2>
@endif
