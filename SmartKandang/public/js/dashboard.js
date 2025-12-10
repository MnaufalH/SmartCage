document.addEventListener("DOMContentLoaded", () => {
    // Elements
    const statusDot = document.getElementById("status-indicator");
    const tempValue = document.querySelector(".temp-val");
    const gasValue = document.querySelector(".gas-val");

    // Tombol Kontrol
    const btnFeeder = document.getElementById("btn-feeder");
    const btnWater = document.getElementById("btn-water");
    const btnLamp = document.getElementById("btn-lamp");

    // Mode Switch (Now in Header or separate container)
    const btnMode = document.getElementById("btn-mode");

    // Settings Container
    const settingsCard = document.getElementById("card-settings");

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
            updateDisabledState();

            // Kirim ke DB
            sendCommand({ mode: isAuto ? "AUTO" : "MANUAL" });
        });
    }

    // 2. Lampu & Minum (Toggle)
    const setupToggle = (element, dbField) => {
        if (!element) return;
        element.addEventListener("change", (e) => {
            const val = e.target.checked ? 1 : 0;
            sendCommand({ [dbField]: val });
        });
    };

    setupToggle(btnLamp, "lampu");
    setupToggle(btnWater, "minum");

    // 3. Pakan (Servo) - Trigger Momentary
    if (btnFeeder) {
        btnFeeder.addEventListener("click", () => {
            console.log("Memberi pakan...");
            sendCommand({ pakan: 1 });

            // Visual feedback
            btnFeeder.disabled = true;
            setTimeout(() => {
                btnFeeder.disabled = false;
            }, 2000);
        });
    }

    // --- FUNGSI 2: DISABLE TOMBOL KALAU AUTO ---
    function updateDisabledState() {
        // Toggle Manual controls disabled when Auto
        const controls = [btnWater, btnLamp, btnFeeder];
        controls.forEach((btn) => {
            if (btn) btn.disabled = isAuto;
        });

        // Settings card should be visible/active always or maybe only in Auto?
        // User didn't specify, but usually you configure schedule for Auto mode.
        // Let's keep it enabled always.
    }

    // --- FUNGSI 3: AMBIL DATA SENSOR & STATUS DARI DB ---
    async function fetchData() {
        try {
            const resControl = await fetch("/api/control/status");
            const ctrl = await resControl.json();

            if (ctrl) {
                // Sync Mode
                if (btnMode) {
                    const dbAuto = ctrl.mode === "AUTO";
                    if (isAuto !== dbAuto) {
                        isAuto = dbAuto;
                        btnMode.checked = isAuto;
                        updateDisabledState();
                    }
                }

                // Sync Toggle State
                if (btnLamp) btnLamp.checked = ctrl.lampu == 1;
                if (btnWater) btnWater.checked = ctrl.minum == 1;

                // Sync Schedule Inputs (Pakan, Minum, Lampu)
                syncInput('pakan', ctrl);
                syncInput('minum', ctrl);
                syncInput('lampu', ctrl);
            }
        } catch (err) {
            console.error("Gagal sync data:", err);
        }
    }

    // Helper: Show/Hide Elements
    const show = (el) => el.classList.remove('hidden');
    const hide = (el) => el.classList.add('hidden');

    // MODAL LOGIC
    const modalOverlay = document.getElementById('settings-modal');
    const modalTitle = document.getElementById('modal-title');
    const forms = {
        pakan: document.getElementById('modal-form-pakan'),
        minum: document.getElementById('modal-form-minum'),
        lampu: document.getElementById('modal-form-lampu'),
    };

    window.openModal = function (type) {
        if (!modalOverlay) return;

        // Update Title
        modalTitle.textContent = type.charAt(0).toUpperCase() + type.slice(1) + ' Automation';

        // Show Overlay
        show(modalOverlay);

        // Show specific form, hide others
        Object.keys(forms).forEach(k => {
            if (k === type) show(forms[k]);
            else hide(forms[k]);
        });
    };

    window.closeModal = function () {
        if (!modalOverlay) return;
        hide(modalOverlay);
    };

    // Close on outside click
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) window.closeModal();
        });
    }

    window.saveSettings = function (type) {
        const val = document.getElementById(`${type}-interval-val`).value;
        const unit = document.getElementById(`${type}-interval-unit`).value;
        const dur = document.getElementById(`${type}-duration`).value;
        const durUnit = document.getElementById(`${type}-duration-unit`).value;

        let data = {
            [`${type}_schedule_val`]: val,
            [`${type}_schedule_unit`]: unit,
            [`${type}_duration`]: dur,
            [`${type}_duration_unit`]: durUnit
        };

        window.sendCommand(data);

        // SweetAlert2 Popup
        Swal.fire({
            title: 'Success!',
            text: `${type.charAt(0).toUpperCase() + type.slice(1)} settings saved successfully!`,
            icon: 'success',
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#3b82f6',
            timer: 2000,
            showConfirmButton: false
        });

        window.closeModal();
    };

    function syncInput(type, data) {
        const val = document.getElementById(`${type}-interval-val`);
        const unit = document.getElementById(`${type}-interval-unit`);
        const dur = document.getElementById(`${type}-duration`);
        const durUnit = document.getElementById(`${type}-duration-unit`);

        // Helper to check active element so we don't overwrite user typing
        const isFocused = (el) => document.activeElement === el;

        if (val && !isFocused(val)) val.value = data[`${type}_schedule_val`] || '';
        if (unit && !isFocused(unit)) unit.value = data[`${type}_schedule_unit`] || 'minute';
        if (dur && !isFocused(dur)) dur.value = data[`${type}_duration`] || 1;
        if (durUnit && !isFocused(durUnit)) durUnit.value = data[`${type}_duration_unit`] || 'minute';
    }

    // Polling 
    setInterval(fetchData, 2000);

    // --- FUNGSI 4: JAM REALTIME ---
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        const timeEl = document.getElementById('time-value');
        if (timeEl) timeEl.textContent = timeString;
    }

    // Update jam setiap detik
    setInterval(updateTime, 1000);
    updateTime(); // Run immediately

    // Expose functions global
    window.sendCommand = sendCommand;
});

