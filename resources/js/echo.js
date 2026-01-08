import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo = null;

export function initEcho(authToken = null) {
  const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
  if (!pusherKey) {
    console.warn('Real-time notifications disabled: missing VITE_PUSHER_APP_KEY');
    return null;
  }

  const token = authToken || localStorage.getItem('api_token');

  // Clean up previous instance if any (important when token changes)
  if (echo) {
    try {
      echo.disconnect();
    } catch (err) {
      console.warn('Echo disconnect warning:', err.message);
    }
  }

  window.Pusher = Pusher;

  const isSelfHosted = !!import.meta.env.VITE_PUSHER_HOST;
  const baseConfig = {
    broadcaster: 'pusher',
    key: pusherKey,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: !!import.meta.env.VITE_PUSHER_FORCE_TLS,
    disableStats: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    },
    // Add connection timeout to prevent hanging
    activityTimeout: 30000,
    pongTimeout: 10000,
  };

  // Self-hosted (Laravel WebSockets): provide host/port and transports
  const selfHostedConfig = isSelfHosted
    ? {
        wsHost: import.meta.env.VITE_PUSHER_HOST,
        wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
        enabledTransports: ['ws', 'wss'],
      }
    : {};

  try {
    echo = new Echo({ ...baseConfig, ...selfHostedConfig });
    
    // Handle connection errors gracefully
    if (echo.connector && echo.connector.pusher) {
      echo.connector.pusher.connection.bind('error', (err) => {
        console.warn('Pusher connection error:', err);
      });
      
      echo.connector.pusher.connection.bind('unavailable', () => {
        console.warn('Pusher connection unavailable');
      });
    }
  } catch (err) {
    console.error('Failed to initialize Echo:', err);
    return null;
  }

  window.Echo = echo;
  return echo;
}

export function getEcho() {
  return echo;
}

// Boot once on initial load
initEcho();

// Re-init whenever auth token changes
window.addEventListener('auth:token-set', () => initEcho());
