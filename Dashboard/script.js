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
    const btnWater = document.getElementById('btn-water'); // Not in wireframe logic but in UI
    const btnLamp = document.getElementById('btn-lamp');
    const btnMode = document.getElementById('btn-mode');
    const modeLabel = document.getElementById('mode-label');
    const scheduleSelect = document.getElementById('schedule-select');

    let espIp = localStorage.getItem('esp_ip') || '192.168.1.100';
    espIpInput.value = espIp;

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
    connectBtn.addEventListener('click', () => {
        espIp = espIpInput.value;
        localStorage.setItem('esp_ip', espIp);
        startPolling();
    });

    // Toggle Mode
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

    // Manual Controls
    const setupControl = (element, deviceName) => {
        element.addEventListener('change', async (e) => {
            if (isAuto && deviceName !== 'feeder') { // Feeder might be allowed in auto? No, usually not.
                // Revert if in auto mode and disallowed
                // e.target.checked = !e.target.checked;
                // return; 
                // Actually the backend blocks it, but let's send request anyway.
            }

            const state = e.target.checked ? 'on' : 'off';
            try {
                // If it's feeder, it might just pulse, so we uncheck it after a delay
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
    setupControl(btnWater, 'dinamo'); // Mapping 'water' UI to 'dinamo' relay

    function updateControlState() {
        const controls = [btnFeeder, btnWater, btnLamp];
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
            statusDot.classList.add('connected');
            statusDot.classList.remove('disconnected');
            statusText.textContent = 'Connected';

            // Update Values
            tempValue.textContent = data.temperature.toFixed(1);
            gasValue.textContent = data.gas;

            // Sync State if changed externally (e.g. by auto logic)
            // Note: If we are dragging a slider, this might interrupt. 
            // For simple switch, it's okay.
            if (isAuto) {
                // In auto mode, reflect what the device is doing
                btnLamp.checked = data.relay_lampu;
                btnWater.checked = data.relay_dinamo;
            }

            // Sync Mode
            // if (data.auto_mode !== isAuto) {
            //    isAuto = data.auto_mode;
            //    btnMode.checked = isAuto;
            //    modeLabel.textContent = isAuto ? 'AUTO' : 'MANUAL';
            //    updateControlState();
            // }

        } catch (err) {
            statusDot.classList.remove('connected');
            statusDot.classList.add('disconnected');
            statusText.textContent = 'Disconnected';
            // log('Fetch error: ' + err);
        }
    }

    function startPolling() {
        if (fetchInterval) clearInterval(fetchInterval);
        fetchData(); // Immediate
        fetchInterval = setInterval(fetchData, 2000);
    }

    // Initialize
    updateControlState();
    startPolling(); // Try immediately
});
