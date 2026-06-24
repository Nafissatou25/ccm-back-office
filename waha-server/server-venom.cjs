const venom = require('venom-bot');
const express = require('express');
const axios = require('axios');

const app = express();
app.use(express.json());

const LARAVEL_WEBHOOK = 'http://127.0.0.1:8000/api/whatsapp/webhook';

// ── Fonction pour démarrer le serveur Express ──
function startServer(client) {
  // ── Écouteur de messages ──
  console.log('👂 Attachement de l\'écouteur de messages...');

  // Méthode 1 : onMessage (standard)
  client.onMessage(async (message) => {
    console.log('📩 [onMessage] Message reçu brut:', JSON.stringify(message).slice(0, 200));

    try {
      if (message.isGroupMsg) {
        console.log('⏭️ Message de groupe ignoré');
        return;
      }

      const from = message.from || message.sender?.id;
      const body = message.body || '';

      if (!from) {
        console.warn('⚠️ Message sans expéditeur');
        return;
      }

      console.log(`📩 Message de ${from}: ${body}`);

      await axios.post(LARAVEL_WEBHOOK, {
        from,
        body,
        type: 'text',
      });

      console.log('➡️ Transmis à Laravel');
    } catch (err) {
      console.error('❌ Webhook error:', err.response?.data || err.message);
    }
  });

  // Méthode 2 : onAnyMessage (fallback pour certains cas)
  if (client.onAnyMessage) {
    client.onAnyMessage(async (message) => {
      console.log('📩 [onAnyMessage] Fallback:', message.body);
    });
  }

  console.log('✅ Écouteur de messages attaché');

  // ── Endpoint /send ──
  app.post('/send', async (req, res) => {
    console.log('📨 Requête /send reçue:', req.body);

    const { to, text } = req.body;

    if (!to || !text) {
      return res.status(400).json({ error: 'Missing to or text' });
    }

    try {
      let chatId = to.trim();

      if (!chatId.includes('@c.us')) {
        chatId = chatId.replace(/\D/g, '') + '@c.us';
      }

      await client.sendText(chatId, text);
      console.log(`✅ Message envoyé à ${chatId}`);

      res.json({ success: true });
    } catch (err) {
      console.error('❌ Erreur sendText:', err);
      res.status(500).json({
        error: err.message || 'Erreur inconnue',
      });
    }
  });

  const PORT = 3000;
  app.listen(PORT, () => {
    console.log(`🚀 Serveur WhatsApp en écoute sur http://localhost:${PORT}`);
  });
}

// ── Connexion Venom ──
venom
  .create(
    'ccm-session',
    (base64Qr, asciiQR, attempts, urlCode) => {
      console.log('🔑 QR reçu, scanne-le avec WhatsApp');
      console.log(asciiQR);
    },
    (statusSession, session) => {
      console.log('📌 Status session:', statusSession);
    },
    {
      multidevice: true,
      headless: false,
      useChrome: true,
      debug: false,
      logQR: false,
      browserArgs: ['--no-sandbox', '--disable-setuid-sandbox'],
    }
  )
  .then((client) => {
    console.log('✅ WhatsApp connecté !');
    startServer(client);
  })
  .catch((err) => {
    console.error('❌ Erreur Venom:', err);
  });