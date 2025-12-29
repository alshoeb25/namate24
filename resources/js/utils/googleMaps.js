let googleMapsScriptLoading = null;

export const loadGoogleMaps = () => {
  // Check if Places library is fully loaded
  if (window.google?.maps?.places?.Autocomplete) {
    return Promise.resolve();
  }

  if (googleMapsScriptLoading) {
    return googleMapsScriptLoading;
  }

  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
  if (!apiKey) {
    return Promise.reject(new Error('Missing Google Maps API key (VITE_GOOGLE_MAPS_API_KEY)'));
  }

  googleMapsScriptLoading = new Promise((resolve, reject) => {
    const existingScript = document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]');

    if (existingScript) {
      // Wait for the script to load properly
      const checkLoaded = () => {
        if (window.google?.maps?.places?.Autocomplete) {
          resolve();
        } else {
          setTimeout(checkLoaded, 100);
        }
      };
      checkLoaded();
      return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&loading=async`;
    script.async = true;
    script.defer = true;
    
    script.onload = () => {
      // Poll to ensure Places library is ready
      const checkPlacesReady = () => {
        if (window.google?.maps?.places?.Autocomplete) {
          resolve();
        } else {
          setTimeout(checkPlacesReady, 100);
        }
      };
      checkPlacesReady();
    };
    
    script.onerror = () => reject(new Error('Failed to load Google Maps script'));
    document.head.appendChild(script);
  });

  return googleMapsScriptLoading;
};
