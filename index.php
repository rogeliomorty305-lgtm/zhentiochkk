<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ZHENTIO CHECKER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a0a0a;
            --card-bg: #111111;
            --border: #222;
            --text-main: #eee;
            --text-dim: #666;
            --accent: #fff;
            --success: #48bb78;
            --error: #f56565;
            --warning: #ed8936;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); padding: 20px; }

        .page { max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }

        .card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 24px; 
        }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { font-size: 1.2rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }

        .controls { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 20px; }
        .btn { 
            background: #1a1a1a; color: #fff; border: 1px solid var(--border); 
            padding: 10px 18px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;
            cursor: pointer; transition: 0.2s; text-transform: uppercase;
        }
        .btn:hover { background: #222; border-color: #444; }
        .btn#start-btn { background: #fff; color: #000; border: none; }
        .btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .status { font-size: 0.8rem; color: var(--text-dim); display: flex; align-items: center; gap: 8px; margin-left: auto; }
        .rec { width: 8px; height: 8px; background: var(--text-dim); border-radius: 50%; display: inline-block; }
        .rec.active { background: var(--error); box-shadow: 0 0 8px var(--error); animation: blink 1s infinite; }

        @keyframes blink { 50% { opacity: 0; } }

        .iconbar { display: flex; gap: 15px; border-top: 1px solid var(--border); padding-top: 20px; }
        .icon { 
            background: none; border: none; color: var(--text-dim); 
            font-size: 0.75rem; font-weight: 700; cursor: pointer; 
            padding: 5px 10px; text-transform: uppercase; transition: 0.2s;
        }
        .icon.active { color: var(--accent); border-bottom: 2px solid var(--accent); }

        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; }
        
        select, textarea { 
            width: 100%; background: #000; border: 1px solid var(--border); 
            color: #fff; padding: 12px; border-radius: 8px; font-size: 0.85rem;
            font-family: 'Courier New', monospace;
        }
        textarea { height: 100px; resize: vertical; }

        .results h2 { font-size: 0.9rem; margin-bottom: 15px; color: var(--text-dim); text-transform: uppercase; }
        .log-container { 
            display: flex; flex-direction: column; gap: 8px; 
            min-height: 100px; max-height: 400px; overflow-y: auto;
        }
        
        .log-entry { 
            font-size: 0.85rem; padding: 8px 0; border-bottom: 1px solid #1a1a1a; 
            display: flex; align-items: center; 
        }
        .badge { 
            padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; 
            margin-right: 15px; min-width: 55px; text-align: center;
        }
        .badge-viva { background: rgba(72, 187, 120, 0.1); color: var(--success); border: 1px solid var(--success); }
        .badge-muerta { background: rgba(245, 101, 101, 0.1); color: var(--error); border: 1px solid var(--error); }
        .badge-error { background: rgba(237, 137, 54, 0.1); color: var(--warning); border: 1px solid var(--warning); }
        
        .cc-val { color: #fff; font-family: 'Courier New', monospace; }
        .time-val { color: var(--text-dim); font-size: 0.75rem; margin-left: auto; }
    </style>
</head>
<body>

<div class="page">
    <section class="card main">
        <div class="header">
            <h1>ZHENTIO CHECKER</h1>
        </div>

        <div class="controls">
            <button class="btn" id="start-btn">▶ INICIAR</button>
            <button class="btn" id="stop-btn" disabled> PARAR</button>
            <button class="btn" id="clear-btn"> LIMPIAR</button>

            <div class="status" id="checker-status">
                <span class="rec" id="rec-dot"></span> <span id="status-text">Detenido.</span>
            </div>
        </div>

        <div class="iconbar">
            <button class="icon active" onclick="filterLogs('all')">Todas (<span id="total_count">0</span>)</button>
            <button class="icon" onclick="filterLogs('VIVA')">Aprobadas (<span id="live_count">0</span>)</button>
            <button class="icon" onclick="filterLogs('MUERTA')">Rechazadas (<span id="die_count">0</span>)</button>
            <button class="icon" onclick="filterLogs('ERROR')">Errores (<span id="error_count">0</span>)</button>
        </div>
    </section>

    <section class="card form" id="config-section">
        <div class="field">
            <label>Gateway</label>
            <select id="gateway" style="margin-bottom: 15px;">
                <option value="us">AMAZON (US)</option>
            </select>
            
            <label for="cookie-area">Cookies</label>
            <textarea id="cookie-area" placeholder="session-id=..."></textarea>
        </div>
        <div class="field">
            <label for="cc-list-area">Lista de tarjetas:</label>
            <textarea id="cc-list-area" placeholder=""></textarea>
        </div>
    </section>

    <section class="card results" id="results-section" style="display: none;">
        <h2 id="log-filter-title">Resultados (Todas)</h2>
        <div class="log-container" id="log-container"></div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let isRunning = false;
    let cardQueue = [];
    let total = 0, lives = 0, dies = 0, errors = 0;
    let currentAjax = null;

    function filterLogs(type) {
        $('.icon').removeClass('active');
        $(`button[onclick="filterLogs('${type}')"]`).addClass('active');
        
        let title = type === 'all' ? 'Todas' : 
                    type === 'VIVA' ? 'Aprobadas' : 
                    type === 'MUERTA' ? 'Rechazadas' : 'Errores';
        
        $('#log-filter-title').text('Resultados (' + title + ')');

        if (type === 'all') {
            $('#config-section').fadeIn(200);
            $('#results-section').hide();
            $('.log-entry').show();
        } else {
            $('#config-section').hide();
            $('#results-section').show();
            $('.log-entry').hide();
            $(`.log-entry[data-type="${type}"]`).show();
        }
    }

    $('#start-btn').click(function() {
        const list = $('#cc-list-area').val().trim();
        const cookies = $('#cookie-area').val().trim();
        if (!list || !cookies) return;

        cardQueue = list.split('\n').filter(l => l.trim() !== '');
        total = cardQueue.length;
        lives = 0; dies = 0; errors = 0;
        
        $('#total_count').text(total);
        updateCounters();

        isRunning = true;
        $('#start-btn').prop('disabled', true);
        $('#stop-btn').prop('disabled', false);
        $('#rec-dot').addClass('active');
        $('#status-text').text('Ejecutando...');
        
        processQueue();
    });

    $('#stop-btn').click(function() {
        isRunning = false;
        if (currentAjax) currentAjax.abort();
        $('#start-btn').prop('disabled', false);
        $('#stop-btn').prop('disabled', true);
        $('#rec-dot').removeClass('active');
        $('#status-text').text('Detenido.');
    });

    $('#clear-btn').click(function() {
        $('#log-container').html('');
        $('#cc-list-area').val('');
        lives = 0; dies = 0; errors = 0; total = 0;
        updateCounters();
        $('#total_count').text('0');
    });

    function updateCounters() {
        $('#live_count').text(lives);
        $('#die_count').text(dies);
        $('#error_count').text(errors);
    }

    function processQueue() {
        if (!isRunning || cardQueue.length === 0) {
            $('#stop-btn').click();
            return;
        }

        const cc = cardQueue.shift().trim();
        const startTime = Date.now();

        currentAjax = $.ajax({
            url: $('#gateway').val() + '.php',
            type: 'POST',
            data: { lista: cc, cookies: $('#cookie-area').val() },
            success: function(res) {
                const time = ((Date.now() - startTime) / 1000).toFixed(1);
                let type = '', badge = '', ccDisplay = cc;

                if (res.includes("Aprobada") || res.includes("success")) {
                    lives++; type = 'VIVA'; badge = 'badge-viva';
                } else if (res.includes("Rechazada")) {
                    dies++; type = 'MUERTA'; badge = 'badge-muerta';
                } else {
                    errors++; type = 'ERROR'; badge = 'badge-error';
                    ccDisplay += " ➔ " + res; 
                }

                const html = `
                    <div class="log-entry" data-type="${type}">
                        <span class="badge ${badge}">${type}</span>
                        <span class="cc-val">${ccDisplay}</span>
                        <span class="time-val">${time}s</span>
                    </div>`;
                
                $('#log-container').prepend(html);
                
                const activeFilter = $('.icon.active').attr('onclick').match(/'([^']+)'/)[1];
                if (activeFilter !== 'all' && activeFilter !== type) {
                    $('#log-container .log-entry').first().hide();
                }

                updateCounters();
                setTimeout(processQueue, 500);
            },
            error: function() { processQueue(); }
        });
    }
</script>
</body>
</html>