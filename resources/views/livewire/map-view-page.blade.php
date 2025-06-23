<div wire:ignore>
    <div
        id="map"
        data-lat="{{ $lat }}"
        data-lng="{{ $lng }}"
        class="leaflet-container"></div>

    @push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Fix for Tailwind + Leaflet */
        #map {
            height: 100% !important;
            min-height: 500px;
            z-index: 0;
        }

        .leaflet-container {
            height: 100% !important;
            width: 100% !important;
            z-index: 0;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let map, userMarker, userCircle;

            if (navigator.geolocation) {
                // 📍 Define the bounding box for Sorsogon
                const sorsogonBounds = L.latLngBounds(
                    L.latLng(12.4, 123.6), // Southwest corner
                    L.latLng(13.3, 124.5) // Northeast corner
                );
                navigator.geolocation.watchPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    if (!map) {
                        map = L.map('map', {
                            maxBounds: sorsogonBounds,
                            maxBoundsViscosity: 1.0,
                            maxZoom: 15,
                            minZoom: 12,
                        }).setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(map);
                    }


                    // Remove old marker & circle
                    if (userMarker) map.removeLayer(userMarker);
                    if (userCircle) map.removeLayer(userCircle);

                    // Add new marker
                    userMarker = L.marker([lat, lng]).addTo(map).bindPopup('You are here').openPopup();

                    // Add circle
                    userCircle = L.circle([lat, lng], {
                        radius: 50,
                        color: 'blue',
                        fillOpacity: 0.1
                    }).addTo(map);

                    // 💾 Send to Livewire
                    Livewire.dispatch('locationUpdated', {
                        lat,
                        lng
                    });

                }, (error) => {
                    console.error("Geolocation error:", error);
                    alert("Location access denied.");
                });
            } else {
                alert("Geolocation not supported.");
            }
        });
    </script>

    @endpush

</div>