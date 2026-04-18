import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = String(import.meta.env.VITE_REVERB_APP_KEY || '').trim();
const reverbHost = String(import.meta.env.VITE_REVERB_HOST || 'localhost').trim();
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const reverbScheme = String(import.meta.env.VITE_REVERB_SCHEME ?? 'http').trim().toLowerCase();

function createNoopEcho() {
  const noopChannel = {
    listen() {
      return noopChannel;
    },
  };

  return {
    private() {
      return noopChannel;
    },
    leave() {},
  };
}

const echo = reverbKey
  ? new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token') ?? ''}`,
        'X-Requested-With': 'XMLHttpRequest',
      },
    },
  })
  : createNoopEcho();

if (!reverbKey) {
  console.warn('[Echo] VITE_REVERB_APP_KEY is missing. Realtime notifications are disabled.');
}

export default echo;
