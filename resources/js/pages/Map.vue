<script setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css'; 
import axios from 'axios';

const props = defineProps({
    markers: {
        type: Array,
        default: () => []
    }
});

const mapContainerId = 'map';
const map = ref(null);

onMounted(() => {
    // Algvaade Tallinna ümbruses
    map.value = L.map(mapContainerId).setView([59.437, 24.7535], 12);

    // OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value);

    // olemasolevad markerid propsist
    props.markers.forEach(marker => {
        L.marker([marker.latitude, marker.longitude])
            .addTo(map.value)
            .bindPopup(`<b>${marker.name}</b><br>${marker.description ?? ''}`);
    });

    // Kaardil klikkides lisa marker
    map.value.on('click', function(e) {
        const name = prompt('Marker name:');
        const description = prompt('Description:');

        if (!name) return;

        axios.post('/map/marker', {
            name,
            description,
            latitude: e.latlng.lat,
            longitude: e.latlng.lng
        }).then(res => {
            L.marker([res.data.latitude, res.data.longitude])
                .addTo(map.value)
                .bindPopup(`<b>${res.data.name}</b><br>${res.data.description ?? ''}`);
        }).catch(err => {
            console.error('Failed to save marker', err);
        });
    });
});
</script>

<template>
    <div id="map" class="w-full h-full"></div>
</template>