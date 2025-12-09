document.addEventListener("DOMContentLoaded", () => {
    // Elements
    const statusDot = document.getElementById("status-indicator"); // Opsional
    const tempValue = document.querySelector(".temp-val"); // Sesuaikan class/id di blade kamu
    const gasValue = document.querySelector(".gas-val"); // Sesuaikan class/id di blade kamu

    // Tombol Kontrol
    const btnFeeder = document.getElementById("btn-feeder"); // Pastikan ID ini ada di Blade
    const btnWater = document.getElementById("btn-water"); // Toggle Minum
    const btnLamp = document.getElementById("btn-lamp"); // Toggle Lampu
    const btnMode = document.getElementById("btn-mode"); // Toggle Mode Auto/Manual
    const modeLabel = document.getElementById("mode-label");

    let isAuto = true;

    // --- FUNGSI 1: KIRIM PERINTAH KE DATABASE ---
    async function sendCommand(data) {
        try {
            await fetch("/api/control/update", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
            console.log("Perintah dikirim:", data);
        } catch (err) {
            console.error("Gagal update database:", err);
        }
    }

    // --- EVENT LISTENER TOMBOL ---

    // 1. Mode Switch (Auto/Manual)
    if (btnMode) {
        btnMode.addEventListener("change", (e) => {
            isAuto = e.target.checked;
            modeLabel.textContent = isAuto ? "AUTO" : "MANUAL";
            updateDisabledState(); // Matikan tombol jika Auto

            // Kirim ke DB
            sendCommand({ mode: isAuto ? "AUTO" : "MANUAL" });
        });
    }

    // 2. Lampu & Minum (Toggle)
    const setupToggle = (element, dbField) => {
        if (!element) return;
        element.addEventListener("change", (e) => {
            const val = e.target.checked ? 1 : 0;
            sendCommand({ [dbField]: val }); // Kirim {lampu: 1} atau {minum: 0}
        });
    };

    setupToggle(btnLamp, "lampu");
    setupToggle(btnWater, "minum");

    // 3. Pakan (Servo) - Cuma aktif sebentar
    if (btnFeeder) {
        btnFeeder.addEventListener("click", () => {
            // Pakai click bukan change untuk tombol tekan
            console.log("Memberi pakan...");
            sendCommand({ pakan: 1 });

            // Visual feedback (tombol ditekan)
            btnFeeder.disabled = true;
            setTimeout(() => {
                btnFeeder.disabled = false;
            }, 2000);
        });
    }

    // --- FUNGSI 2: DISABLE TOMBOL KALAU AUTO ---
    function updateDisabledState() {
        const controls = [btnWater, btnLamp, btnFeeder];
        controls.forEach((btn) => {
            if (btn) btn.disabled = isAuto; // Kalau Auto, tombol mati
        });
    }

    // --- FUNGSI 3: AMBIL DATA SENSOR & STATUS DARI DB ---
    async function fetchData() {
        try {
            // Kita pakai endpoint sensor yang lama untuk data suhu
            // Dan endpoint control baru untuk status tombol (sinkronisasi)

            // 1. Ambil Data Sensor Terakhir (dari tabel sensor_data)
            // (Asumsi kamu punya route ini dari codingan sebelumnya)
            // const resSensor = await fetch('/');
            // Kita skip dulu update suhu via JS, biar blade yang handle refresh (meta refresh)
            // ATAU kalau mau AJAX: fetch('/api/sensor/latest')...

            // 2. Ambil Status Kontrol (Supaya kalau direfresh tombol gak reset)
            const resControl = await fetch("/api/control/status");
            const ctrl = await resControl.json();

            if (ctrl) {
                // Sinkronisasi posisi tombol dengan Database
                if (btnMode) {
                    const dbAuto = ctrl.mode === "AUTO";
                    if (isAuto !== dbAuto) {
                        // Update hanya jika beda
                        isAuto = dbAuto;
                        btnMode.checked = isAuto;
                        modeLabel.textContent = isAuto ? "AUTO" : "MANUAL";
                        updateDisabledState();
                    }
                }

                // Update tombol hanya jika Mode Manual (biar user liat status asli)
                // Atau update terus agar user tau apa yang terjadi di Auto
                if (btnLamp) btnLamp.checked = ctrl.lampu == 1;
                if (btnWater) btnWater.checked = ctrl.minum == 1;
            }
        } catch (err) {
            console.error("Gagal sync data:", err);
        }
    }

    // Polling setiap 2 detik untuk sync status
    setInterval(fetchData, 2000);
});
