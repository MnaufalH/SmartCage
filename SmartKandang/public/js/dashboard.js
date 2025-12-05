document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const espIpInput = document.getElementById('esp-ip');
    const connectBtn = document.getElementById('connect-btn');
    const statusDot = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');

    const tempValue = document.getElementById('temp-value');
    const gasValue = document.getElementById('gas-value');
    const timeValue = document.getElementById('time-value');

    const btnFeeder = document.getElementById('btn-feeder');
    const btnWater = document.getElementById('btn-water');
    const btnLamp = document.getElementById('btn-lamp');
    const btnMode = document.getElementById('btn-mode');
    const modeLabel = document.getElementById('mode-label');
    const scheduleSelect = document.getElementById('schedule-select');

    let espIp = localStorage.getItem('esp_ip') || '192.168.1.100';
    if (espIpInput) espIpInput.value = espIp;

    let isAuto = true;
    let fetchInterval = null;

    // Helper to log
    const log = (msg) => console.log(`[SmartCage] ${msg}`);

    // Update Time
    setInterval(() => {
        const now = new Date();
        timeValue.textContent = now.toLocaleTimeString('en-US', { hour12: true });
    }, 1000);

    // Save IP
    if (connectBtn) {
        connectBtn.addEventListener('click', () => {
            espIp = espIpInput.value;
            localStorage.setItem('esp_ip', espIp);
            startPolling();
        });
    }

    // Toggle Mode
    if (btnMode) {
        btnMode.addEventListener('change', async (e) => {
            isAuto = e.target.checked;
            modeLabel.textContent = isAuto ? 'AUTO' : 'MANUAL';
            updateControlState();

            try {
                await fetch(`http://${espIp}/control?mode=${isAuto ? 'auto' : 'manual'}`, { method: 'POST' });
            } catch (err) {
                log('Error setting mode: ' + err);
            }
        });
    }

    // Manual Controls
    const setupControl = (element, deviceName) => {
        if (!element) return;
        element.addEventListener('change', async (e) => {
            if (isAuto && deviceName !== 'feeder') {
                // disallowed in auto
            }

            const state = e.target.checked ? 'on' : 'off';
            try {
                if (deviceName === 'feeder') {
                    setTimeout(() => { element.checked = false; }, 2500);
                }

                await fetch(`http://${espIp}/control?device=${deviceName}&state=${state}`, { method: 'POST' });
            } catch (err) {
                log(`Error controlling ${deviceName}: ` + err);
            }
        });
    };

    setupControl(btnFeeder, 'feeder');
    setupControl(btnLamp, 'lampu');
    setupControl(btnWater, 'dinamo');

    function updateControlState() {
        const controls = [btnFeeder, btnWater, btnLamp];
        if (!btnFeeder) return;

        if (isAuto) {
            controls.forEach(c => c.parentElement.parentElement.classList.add('disabled'));
        } else {
            controls.forEach(c => c.parentElement.parentElement.classList.remove('disabled'));
        }
    }

    async function fetchData() {
        try {
            const res = await fetch(`http://${espIp}/data`, { signal: AbortSignal.timeout(2000) });
            if (!res.ok) throw new Error('Failed');
            const data = await res.json();

            // Update Status
            if (statusDot) {
                statusDot.classList.add('connected');
                statusDot.classList.remove('disconnected');
                statusText.textContent = 'Connected';
            }

            // Update Values
            if (tempValue) tempValue.textContent = data.temperature.toFixed(1);
            if (gasValue) gasValue.textContent = data.gas;

            if (isAuto) {
                if (btnLamp) btnLamp.checked = data.relay_lampu;
                if (btnWater) btnWater.checked = data.relay_dinamo;
            }

        } catch (err) {
            if (statusDot) {
                statusDot.classList.remove('connected');
                statusDot.classList.add('disconnected');
                statusText.textContent = 'Disconnected';
            }
        }
    }

    function startPolling() {
        if (fetchInterval) clearInterval(fetchInterval);
        fetchData(); // Immediate
        fetchInterval = setInterval(fetchData, 2000);
    }

    // Initialize
    updateControlState();
    startPolling();
});
