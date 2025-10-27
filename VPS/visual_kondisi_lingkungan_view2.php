<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="font-weight-bold">Laporan Kondisi Lingkungan (Layer)</h1>
        </div>
    </section>

    <section class="content">
        <div class="card shadow-sm">
            <div class="card-body">
    <form action="<?= base_url('Dashboard_new/visual_kondisi_lingkungan') ?>" method="post">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="tahun">Tahun:</label>
                    <select name="tahun" id="tahun" class="form-control">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?= $i ?>" <?= ($selected_year == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="col-md-2 align-self-end">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
            
            <div class="col-md-12 mt-3">
                <div class="form-group">
                    <label>Filter Pakan:</label>
                    <div class="p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                        <div class="row">
                            <?php if (empty($all_pakan_options)): ?>
                                <div class="col-12">
                                    <span class="text-muted">Tidak ada opsi pakan ditemukan.</span>
                                </div>
                            <?php else: ?>
                                <?php foreach ($all_pakan_options as $pakan_name): ?>
                                    <?php
                                        // $selected_pakan adalah array yg kita kirim dari controller
                                        $is_checked = in_array($pakan_name, $selected_pakan);
                                    ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="pakan[]" value="<?= htmlspecialchars($pakan_name) ?>" 
                                                   id="pakan_<?= md5($pakan_name) ?>"
                                                   <?= $is_checked ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label" for="pakan_<?= md5($pakan_name) ?>">
                                                <?= htmlspecialchars($pakan_name) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div> </form>
</div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Kondisi Lalat per Bulan (Tahun <?= $selected_year ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="chartLalat"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Kondisi Kotoran per Bulan (Tahun <?= $selected_year ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="chartKotoran"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Helper function untuk membuat chart 100% STACKED
        function create100PercentStackedChart(canvasId, chartLabels, chartDatasets) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            
            if (window[canvasId + 'Chart']) {
                window[canvasId + 'Chart'].destroy();
            }

            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets 
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }

                                    // Ambil persentasenya
                                    let percentage = context.parsed.y;
                                    
                                    // Ambil jumlah mentahnya dari array 'raw_counts' yang kita buat
                                    let raw_count = context.dataset.raw_counts[context.dataIndex]; 

                                    if (percentage !== null) {
                                        // Gabungkan keduanya
                                        label += `${percentage.toFixed(2)}% (${raw_count} data)`; 
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true, 
                            grid: { display: false }
                        },
                        y: {
                            stacked: true, 
                            min: 0,
                            max: 100, // Paksa 100%
                            ticks: {
                                callback: function(value) {
                                    return value + "%";
                                }
                            },
                            title: {
                                display: true,
                                text: 'Persentase Bulanan'
                            }
                        }
                    }
                }
            });
        }

        // 1. Render Chart Lalat
        // PENTING: json_encode ada DI SINI (di View)
        var lalatLabels = <?= json_encode($chart_lalat_data['labels']) ?>;
        var lalatDatasets = <?= json_encode($chart_lalat_data['datasets']) ?>;
        
        if (lalatLabels && lalatLabels.length > 0) {
            create100PercentStackedChart('chartLalat', lalatLabels, lalatDatasets);
        } else {
            document.getElementById('chartLalat').parentElement.innerHTML = '<p class="text-center text-muted m-5">Tidak ada data untuk periode ini.</p>';
        }

        // 2. Render Chart Kotoran
        // PENTING: json_encode ada DI SINI (di View)
        var kotoranLabels = <?= json_encode($chart_kotoran_data['labels']) ?>;
        var kotoranDatasets = <?= json_encode($chart_kotoran_data['datasets']) ?>;

        if (kotoranLabels && kotoranLabels.length > 0) {
            create100PercentStackedChart('chartKotoran', kotoranLabels, kotoranDatasets);
        } else {
            document.getElementById('chartKotoran').parentElement.innerHTML = '<p class="text-center text-muted m-5">Tidak ada data untuk periode ini.</p>';
        }
    });
</script>
