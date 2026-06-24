const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');
const axios = require('axios');
const app = express();
app.use(express.json());

// URL du webhook Laravel (utilisez 127.0.0.1 pour IPv4)
const LARAVEL_WEBHOOK = 'http://127.0.0.1:8000/api/whatsapp/webhook';

// ── Client WhatsApp ──
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: './session' }),
    puppeteer: { headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] }
});

client.on('qr', qr => {
    console.log('🔑 Scanne ce QR code avec WhatsApp :');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp connecté !');
});

// ── Réception des messages ──
client.on('message', async message => {
    if (message.from === 'status@broadcast') return;

    console.log(`📩 Message de ${message.from}: ${message.body}`);

    try {
        // On envoie le from complet (avec suffixe @c.us, @lid, etc.)
        await axios.post(LARAVEL_WEBHOOK, {
            from: message.from,
            body: message.body,
            type: message.type,
        });
        console.log('➡️ Transmis à Laravel');
    } catch (err) {
        console.error('❌ Webhook error:', err.message);
    }
});

// ── Endpoint pour envoyer un message (appelé par Laravel) ──
app.post('/send', async (req, res) => {
    console.log('📨 Requête /send reçue:', req.body);

    const { to, text } = req.body;
    if (!to || !text) {
        return res.status(400).json({ error: 'Missing to or text' });
    }

    try {
        // to doit être un chatId valide (ex: 23750085583@c.us, 22918029418737@lid)
        await client.sendMessage(to, text);
        console.log(`✅ Message envoyé à ${to}`);
        res.json({ success: true });
    } catch (err) {
        console.error('❌ Erreur sendMessage:', err.message);
        res.status(500).json({ error: err.message });
    }
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`🚀 Serveur WhatsApp en écoute sur http://localhost:${PORT}`);
});

client.initialize();