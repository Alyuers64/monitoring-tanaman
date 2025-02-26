@extends('layout.admin')

@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <button id="toggleRelay" class="btn btn-sm btn-danger shadow-sm">
                <i class="fas fa-power-off fa-sm text-white-50"></i> Toggle Relay
            </button>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Suhu -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Suhu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="suhuValue">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thermometer-half fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kelembapan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kelembapan</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="kelembapanValue">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tint fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik -->
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Suhu dan Kelembapan</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="suhuKelembapanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
            var ctx = document.getElementById("suhuKelembapanChart").getContext("2d");

            var suhuKelembapanChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: "Suhu",
                            borderColor: "#4e73df",
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            data: []
                        },
                        {
                            label: "Kelembapan",
                            borderColor: "#1cc88a",
                            backgroundColor: "rgba(28, 200, 138, 0.05)",
                            data: []
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true }
                    }
                }
            });

            function updateData() {
                $.ajax({
                    url: '/api/sensor-data',
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                    success: function (data) {
                        suhuKelembapanChart.data.labels = data.labels;
                        suhuKelembapanChart.data.datasets[0].data = data.suhuData;
                        suhuKelembapanChart.data.datasets[1].data = data.kelembapanData;
                        suhuKelembapanChart.update();

                        $("#suhuValue").html(data.suhuData[data.suhuData.length - 1] + " °C");
                        $("#kelembapanValue").html(data.kelembapanData[data.kelembapanData.length - 1] + " %");
                    },
                    error: function () {
                        console.log("Gagal mendapatkan data sensor!");
                    }
                });
            }

            function updateRelayStatus() {
                $.ajax({
                    url: "/api/relay-status",
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        let relayOn = data.status === 0;
                        $("#toggleRelay")
                            .toggleClass("btn-success", relayOn)
                            .toggleClass("btn-danger", !relayOn)
                            .html(`<i class="fas fa-power-off fa-sm text-white-50"></i> ${relayOn ? 'Matikan' : 'Nyalakan'} Relay`);
                    },
                    error: function () {
                        console.log("Gagal mendapatkan status relay!");
                    }
                });
            }
            $("#toggleRelay").click(function () {
                $.ajax({
                    url: "/api/relay-status",  // Ganti dari "/relay-status" ke "/api/relay-status"
                    type: "POST",
                    data: {
                        status: $("#toggleRelay").hasClass("btn-danger") ? 0 : 1 // Toggle status
                    },
                    dataType: "json",
                    success: function (response) {
                        alert(response.message);
                        updateRelayStatus();
                    },
                    error: function (xhr, status, error) {
                        alert("Gagal menghubungi server: " + xhr.responseText);
                    }
                });
            });




            setInterval(updateData, 5000);
            setInterval(updateRelayStatus, 5000);
            updateData();
            updateRelayStatus();
        });
    </script>
@endsection