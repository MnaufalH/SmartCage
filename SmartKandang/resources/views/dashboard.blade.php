<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="refresh" content="5"> -->
    <title>Smart Cage Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <div class="background-globes">
        <div class="globe globe-1"></div>
        <div class="globe globe-2"></div>
    </div>

    <div class="container">
        <!-- Header -->
        <header class="glass-panel">
            <div class="header-content centered-header">
                <h1>SMART CAGE</h1>
                <p class="subtitle">(Suhu - Gas - Waktu - Kontrol)</p>
            </div>
            <div class="connection-status">
                <span id="status-indicator" class="status-dot disconnected"></span>
                <span id="status-text">Disconnected</span>
                <input type="text" id="esp-ip" placeholder="ESP32 IP Address" value="192.168.1.100">
                <button id="connect-btn">Connect</button>
            </div>
        </header>

        <div class="main-grid">
            <!-- Left Column: Sensor Status -->
            <section class="panel-section">
                <!-- ... existing sensor content ... -->
                <div class="section-header">
                    <div class="line"></div>
                    <h2>SENSOR STATUS</h2>
                    <div class="line"></div>
                </div>

                <div class="cards-grid">
                    <!-- Temperature Card -->
                    <div class="card glass-panel">
                        <h3>Suhu</h3>
                        <div class="value-display">
                            <span id="temp-value">{{ $data->suhu ?? 0 }}</span>
                            <span class="unit">°C</span>
                        </div>
                    </div>

                    <!-- Gas Level Card -->
                    <div class="card glass-panel">
                        <h3>Gas Check</h3>
                        <div class="value-display">
                            <span id="gas-value">{{ $data->gas ?? 0 }}</span>
                            <span class="unit">PPM</span>
                        </div>
                    </div>

                    <!-- Time Card -->
                    <div class="card glass-panel full-width">
                        <h3>Waktu</h3>
                        <div class="value-display time-display">
                            <span id="time-value">-- : -- : --</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right Column: Manual Control -->
            <section class="panel-section">
                <div class="section-header with-controls">
                    <!-- Mode Switch Repositioned Here -->
                    <div class="mode-switch-wrapper">
                        <span class="mode-text">MANUAL</span>
                        <label class="switch">
                            <input type="checkbox" id="btn-mode" checked>
                            <span class="slider round"></span>
                        </label>
                        <span class="mode-text">AUTO</span>
                    </div>
                    
                    <div class="line"></div>
                    <h2>MANUAL CONTROL</h2>
                    <div class="line"></div>
                </div>

                <div class="controls-grid">
                    <!-- Pakan Control -->
                    <div class="control-card glass-panel" id="card-pakan">
                        <div class="card-header-row">
                            <h3>Pakan</h3>
                            <button class="settings-trigger" onclick="openModal('pakan')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            </button>
                        </div>
                        <div class="control-row centered">
                            <label class="switch">
                                <input type="checkbox" id="btn-feeder">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Minum Control -->
                    <div class="control-card glass-panel" id="card-minum">
                        <div class="card-header-row">
                            <h3>Minum</h3>
                            <button class="settings-trigger" onclick="openModal('minum')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            </button>
                        </div>
                        <div class="control-row centered">
                            <label class="switch">
                                <input type="checkbox" id="btn-water">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Lampu Control -->
                    <div class="control-card glass-panel" id="card-lampu">
                        <div class="card-header-row">
                            <h3>Lampu</h3>
                            <button class="settings-trigger" onclick="openModal('lampu')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            </button>
                        </div>
                        <div class="control-row centered">
                            <label class="switch">
                                <input type="checkbox" id="btn-lamp">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- MODAL -->
        <div id="settings-modal" class="modal-overlay hidden">
            <div class="modal-content glass-panel">
                <div class="modal-header">
                    <h3 id="modal-title">Settings</h3>
                    <button class="close-modal" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <!-- Generic Template, filled by JS or simple hidden sections -->
                    
                    <!-- Pakan Settings Form -->
                    <div id="modal-form-pakan" class="modal-form hidden">
                         <div class="setting-grid">
                            <div class="setting-col">
                                <label class="sub-label">Every</label>
                                <div class="input-integrated">
                                    <input type="number" id="pakan-interval-val" placeholder="0" min="1">
                                    <select id="pakan-interval-unit" class="unit-select">
                                        <option value="minute">Min</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                    </select>
                                </div>
                            </div>
                            <div class="setting-col">
                                <label class="sub-label">Duration</label>
                                <div class="input-integrated">
                                    <input type="number" id="pakan-duration" placeholder="1" min="1">
                                    <select id="pakan-duration-unit" class="unit-select">
                                        <option value="second">Sec</option>
                                        <option value="minute">Min</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="save-btn full-width-btn" onclick="saveSettings('pakan')">Save Pakan</button>
                    </div>

                    <!-- Minum Settings Form -->
                    <div id="modal-form-minum" class="modal-form hidden">
                         <div class="setting-grid">
                            <div class="setting-col">
                                <label class="sub-label">Every</label>
                                <div class="input-integrated">
                                    <input type="number" id="minum-interval-val" placeholder="0" min="1">
                                    <select id="minum-interval-unit" class="unit-select">
                                        <option value="minute">Min</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                    </select>
                                </div>
                            </div>
                            <div class="setting-col">
                                <label class="sub-label">Duration</label>
                                <div class="input-integrated">
                                    <input type="number" id="minum-duration" placeholder="1" min="1">
                                    <select id="minum-duration-unit" class="unit-select">
                                        <option value="second">Sec</option>
                                        <option value="minute">Min</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="save-btn full-width-btn" onclick="saveSettings('minum')">Save Minum</button>
                    </div>

                    <!-- Lampu Settings Form -->
                    <div id="modal-form-lampu" class="modal-form hidden">
                         <div class="setting-grid">
                            <div class="setting-col">
                                <label class="sub-label">Every</label>
                                <div class="input-integrated">
                                    <input type="number" id="lampu-interval-val" placeholder="0" min="1">
                                    <select id="lampu-interval-unit" class="unit-select">
                                        <option value="minute">Min</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                    </select>
                                </div>
                            </div>
                            <div class="setting-col">
                                <label class="sub-label">Duration</label>
                                <div class="input-integrated">
                                    <input type="number" id="lampu-duration" placeholder="1" min="1">
                                    <select id="lampu-duration-unit" class="unit-select">
                                        <option value="second">Sec</option>
                                        <option value="minute">Min</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="save-btn full-width-btn" onclick="saveSettings('lampu')">Save Lampu</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
