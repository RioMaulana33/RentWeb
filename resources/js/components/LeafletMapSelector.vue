<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { OpenStreetMapProvider } from 'leaflet-geosearch';
import 'leaflet/dist/leaflet.css';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  latitude: {
    type: Number,
    default: 0.0000 
  },
  longitude: {
    type: Number,
    default: 109.3333
  }
});

const emit = defineEmits(['update:modelValue', 'update:latitude', 'update:longitude']);

const mapElement = ref<HTMLElement | null>(null);
const searchInput = ref('');
const map = ref<any>(null);
const marker = ref<any>(null);
const searchResults = ref([]);
const isSearching = ref(false);
const isGettingLocation = ref(false);
const currentAddress = ref(props.modelValue);
const locationPermissionStatus = ref<PermissionState | null>(null);

const checkLocationPermission = async () => {
  try {
    // Check if the browser supports the permissions API
    if ('permissions' in navigator) {
      const permission = await navigator.permissions.query({ name: 'geolocation' });
      locationPermissionStatus.value = permission.state;

      // Listen for permission changes
      permission.addEventListener('change', () => {
        locationPermissionStatus.value = permission.state;
      });

      return permission.state;
    }
    return null;
  } catch (error) {
    console.error('Error checking location permission:', error);
    return null;
  }
};

const requestLocationPermission = async () => {
  try {
    const result = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
      });
    });

    // If we get here, permission was granted
    locationPermissionStatus.value = 'granted';
    return result;
  } catch (error) {
    if (error instanceof GeolocationPositionError) {
      if (error.code === error.PERMISSION_DENIED) {
        locationPermissionStatus.value = 'denied';
      }
    }
    throw error;
  }
};

const getCurrentLocation = async () => {
  if (!navigator.geolocation) {
    alert('Geolocation tidak didukung oleh browser Anda');
    return;
  }

  isGettingLocation.value = true;

  try {
    // Check current permission status
    const permissionStatus = await checkLocationPermission();

    if (permissionStatus === 'denied') {
      alert('Akses lokasi telah ditolak. Silakan ubah pengaturan izin lokasi di browser Anda.');
      return;
    }

    // If permission is prompt or we're not sure, request permission
    if (permissionStatus !== 'granted') {
      try {
        const position = await requestLocationPermission();
        const { latitude, longitude } = position.coords;
        map.value.setView([latitude, longitude], 16);
        updateMarkerPosition(latitude, longitude);
      } catch (error) {
        if (error instanceof GeolocationPositionError) {
          switch (error.code) {
            case error.PERMISSION_DENIED:
              alert('Untuk menggunakan fitur ini, Anda perlu mengizinkan akses lokasi di browser Anda');
              break;
            case error.POSITION_UNAVAILABLE:
              alert('Informasi lokasi tidak tersedia saat ini');
              break;
            case error.TIMEOUT:
              alert('Waktu permintaan lokasi habis');
              break;
            default:
              alert('Terjadi kesalahan saat mengambil lokasi');
          }
        }
      }
    } else {
      // Permission already granted, get location directly
      const position = await new Promise<GeolocationPosition>((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
          enableHighAccuracy: true,
          timeout: 5000,
          maximumAge: 0
        });
      });

      const { latitude, longitude } = position.coords;
      map.value.setView([latitude, longitude], 16);
      updateMarkerPosition(latitude, longitude);
    }
  } catch (error) {
    console.error('Error getting location:', error);
    alert('Terjadi kesalahan saat mengambil lokasi');
  } finally {
    isGettingLocation.value = false;
  }
};

const initializeMap = async () => {
  const L = (await import('leaflet')).default;
  await import('@geoman-io/leaflet-geoman-free');

  map.value = L.map(mapElement.value!).setView([props.latitude, props.longitude], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map.value);

  marker.value = L.marker([props.latitude, props.longitude], {
    draggable: true
  }).addTo(map.value);

  if (props.latitude && props.longitude) {
    updateMarkerPosition(props.latitude, props.longitude);
  }

  map.value.on('click', (e: any) => {
    const { lat, lng } = e.latlng;
    updateMarkerPosition(lat, lng);
  });

  marker.value.on('dragend', () => {
    const position = marker.value.getLatLng();
    updateMarkerPosition(position.lat, position.lng);
  });

  // Check initial location permission status
  await checkLocationPermission();
};

const provider = new OpenStreetMapProvider();

const searchLocation = async () => {
  if (!searchInput.value.trim()) return;

  isSearching.value = true;
  try {
    const results = await provider.search({ query: searchInput.value });
    searchResults.value = results;
  } catch (error) {
    console.error('Search failed:', error);
  } finally {
    isSearching.value = false;
  }
};

const handleKeyPress = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    searchLocation();
  }
};

const selectLocation = async (result: any) => {
  const { y: lat, x: lng, label } = result;

  map.value.setView([lat, lng], 16);
  updateMarkerPosition(lat, lng);
  searchResults.value = [];
  searchInput.value = label;
  currentAddress.value = label;
  emit('update:modelValue', label);
};

const updateMarkerPosition = async (lat: number, lng: number) => {
  const position = { lat, lng };
  marker.value.setLatLng(position);

  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`
    );
    const data = await response.json();

    const address = data.display_name;
    currentAddress.value = address;
    emit('update:modelValue', address);
    emit('update:latitude', lat);
    emit('update:longitude', lng);
    searchInput.value = address;
  } catch (error) {
    console.error('Reverse geocoding failed:', error);
  }
};

const isDarkMode = ref(false);

// Function to check and update dark mode status
const checkDarkMode = () => {
  // Check if the user prefers dark mode or if the body has a dark class
  isDarkMode.value =
    window.matchMedia('(prefers-color-scheme: dark)').matches ||
    document.body.classList.contains('dark-mode') ||
    document.documentElement.classList.contains('dark');
};

// Watch for theme changes
onMounted(() => {
  checkDarkMode();

  // Watch for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)')
    .addEventListener('change', checkDarkMode);

  // Create a MutationObserver to watch for class changes
  const observer = new MutationObserver(checkDarkMode);
  observer.observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
  });
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
});

watch(() => props.modelValue, (newValue) => {
  if (newValue !== currentAddress.value) {
    currentAddress.value = newValue;
  }
});

onMounted(async () => {
    await initializeMap();
    
    if (props.latitude && props.longitude) {
        map.value.setView([props.latitude, props.longitude], 13);
        marker.value.setLatLng([props.latitude, props.longitude]);
        await updateMarkerPosition(props.latitude, props.longitude);
    }
});

watch(
    [() => props.latitude, () => props.longitude],
    async ([newLat, newLng], [oldLat, oldLng]) => {
        if (newLat && newLng && (newLat !== oldLat || newLng !== oldLng)) {
            if (map.value && marker.value) {
                map.value.setView([newLat, newLng], 13);
                marker.value.setLatLng([newLat, newLng]);
                await updateMarkerPosition(newLat, newLng);
            }
        }
    },
    { immediate: true }
);
</script>

<template>
  <div class="map-selector">
    <div class="search-container mb-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Search input with attached search button -->
        <div class="input-group">
          <input v-model="searchInput" @keypress="handleKeyPress" type="text" class="form-control form-control-solid"
            placeholder="Cari lokasi..." style="width: 400px" />
          <button class="btn btn-primary" type="button" @click="searchLocation" :disabled="isSearching">
            <span v-if="!isSearching">
              <i class="la la-search"></i> Cari
            </span>
            <span v-else>
              <i class="la la-spinner la-spin"></i> Mencari...
            </span>
          </button>
        </div>

        <!-- Separated location button with auto width -->
        <div>
          <button class="btn btn-success btn-md" type="button" @click="getCurrentLocation" :disabled="isGettingLocation"
            style="width: 157px">
            <span v-if="!isGettingLocation">
              <i class="fa-solid fa-location-dot" style="margin-right: 2px"></i>
              {{ locationPermissionStatus === 'granted' ? 'Lokasi Saat Ini' : 'Izinkan Lokasi' }}
            </span>
            <span v-else>
              <i class="la la-spinner la-spin"></i> Mengambil...
            </span>
          </button>
        </div>
      </div>

      <!-- Theme-aware Search Results -->
      <div v-if="searchResults.length" class="search-results" :class="{ 'dark': isDarkMode }">
        <div v-for="result in searchResults" :key="result.x + result.y" class="search-result-item"
          @click="selectLocation(result)">
          <i class="la la-map-marker text-primary me-2"></i>
          {{ result.label }}
        </div>
      </div>
    </div>

    <div ref="mapElement" class="map-container rounded-lg border border-gray-200"></div>

    <div class="selected-address mt-2 p-3 bg-light rounded">
      <i class="la la-info-circle text-primary me-2"></i>
      <strong>Alamat terpilih:</strong> {{ currentAddress }}
    </div>
  </div>
</template>

<style scoped>
.map-selector {
  width: 100%;
}

.map-container {
  width: 100%;
  height: 400px;
  z-index: 1;
}

.search-container {
  position: relative;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--kt-body-bg, #ffffff);
  border: 1px solid var(--kt-border-color, #dee2e6);
  border-radius: 0.475rem;
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, .05), 0 5px 25px rgba(0, 0, 0, .15);
  margin-top: 4px;
  color: var(--kt-text-color, #181c32);
}

.search-results.dark {
  background: var(--kt-body-bg-dark, #1e1e2d);
  border-color: var(--kt-border-color-dark, #2b2b40);
  color: var(--kt-text-gray-700-dark, #cdcdde);
}

.search-result-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid var(--kt-border-color, #dee2e6);
  display: flex;
  align-items: center;
  transition: background-color 0.2s ease;
}

.search-results.dark .search-result-item {
  border-bottom-color: var(--kt-border-color-dark, #2b2b40);
}

.search-result-item:hover {
  background-color: var(--kt-component-hover-bg, #f8f9fa);
}

.search-results.dark .search-result-item:hover {
  background-color: var(--kt-component-hover-bg-dark, #2b2b40);
}

.search-result-item:last-child {
  border-bottom: none;
}

/* Scrollbar styling */
.search-results::-webkit-scrollbar {
  width: 0.5rem;
}

.search-results::-webkit-scrollbar-track {
  background: transparent;
}

.search-results::-webkit-scrollbar-thumb {
  background: var(--kt-scrollbar-color, #d1d5db);
  border-radius: 0.25rem;
}

.search-results.dark::-webkit-scrollbar-thumb {
  background: var(--kt-scrollbar-color-dark, #4b5563);
}
</style>