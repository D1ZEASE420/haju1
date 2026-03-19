<script setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import axios from 'axios';

const map = ref(null);
const markers = ref([]);
const leafletMarkers = ref({});

onMounted(async () => {
    map.value = L.map('map').setView([59.437, 24.7535], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value);

    // Load markers from backend
    const res = await axios.get('/map/markers');
    markers.value = res.data;
    markers.value.forEach(marker => addMarkerToMap(marker));

    // Add new marker on map click
    map.value.on('click', openAddMarkerPopup);
});

function addMarkerToMap(marker) {
    const m = L.marker([marker.latitude, marker.longitude]).addTo(map.value);
    leafletMarkers.value[marker.id] = m;

    m.bindPopup(`
        <div>
            <b>${marker.name}</b><br>
            ${marker.description ?? ''}<br>
            <button class="edit-marker-btn" data-id="${marker.id}">Edit</button>
            <button class="delete-marker-btn" data-id="${marker.id}">Delete</button>
        </div>
    `);

    m.on('popupopen', () => {
        const editBtn = document.querySelector(`.edit-marker-btn[data-id="${marker.id}"]`);
        if (editBtn) editBtn.addEventListener('click', () => openEditMarkerPopup(marker));

        const deleteBtn = document.querySelector(`.delete-marker-btn[data-id="${marker.id}"]`);
        if (deleteBtn) deleteBtn.addEventListener('click', () => deleteMarker(marker.id));
    });
}

function openAddMarkerPopup(e) {
    const popupContent = document.createElement('div');

    const nameInput = document.createElement('input');
    nameInput.placeholder = 'Name';
    nameInput.className = 'border p-1 mb-1 w-full';
    popupContent.appendChild(nameInput);

    const descInput = document.createElement('textarea');
    descInput.placeholder = 'Description';
    descInput.className = 'border p-1 w-full';
    popupContent.appendChild(descInput);

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Add Marker';
    saveBtn.className = 'mt-1 px-2 py-1 bg-blue-500 text-white rounded';
    popupContent.appendChild(saveBtn);

    const popup = L.popup()
        .setLatLng(e.latlng)
        .setContent(popupContent)
        .openOn(map.value);

    saveBtn.addEventListener('click', async () => {
        const name = nameInput.value.trim();
        const description = descInput.value.trim();
        if (!name) return alert('Name is required');

        try {
            const res = await axios.post('/map/marker', {
                name,
                description,
                latitude: e.latlng.lat,
                longitude: e.latlng.lng
            });
            const newMarker = res.data;
            markers.value.push(newMarker);
            addMarkerToMap(newMarker);
            map.value.closePopup();
        } catch (err) {
            console.error('Failed to save marker', err);
            alert('Failed to save marker');
        }
    });
}

function openEditMarkerPopup(marker) {
    const popupContent = document.createElement('div');

    const nameInput = document.createElement('input');
    nameInput.value = marker.name;
    nameInput.className = 'border p-1 mb-1 w-full';
    popupContent.appendChild(nameInput);

    const descInput = document.createElement('textarea');
    descInput.value = marker.description ?? '';
    descInput.className = 'border p-1 w-full';
    popupContent.appendChild(descInput);

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save Changes';
    saveBtn.className = 'mt-1 px-2 py-1 bg-green-500 text-white rounded';
    popupContent.appendChild(saveBtn);

    const popup = L.popup()
        .setLatLng([marker.latitude, marker.longitude])
        .setContent(popupContent)
        .openOn(map.value);

    saveBtn.addEventListener('click', async () => {
        const newName = nameInput.value.trim();
        const newDesc = descInput.value.trim();
        if (!newName) return alert('Name is required');

        try {
            const res = await axios.patch(`/map/marker/${marker.id}`, {
                name: newName,
                description: newDesc
            });

            const updatedMarker = res.data;
            const index = markers.value.findIndex(m => m.id === updatedMarker.id);
            if (index !== -1) markers.value[index] = updatedMarker;

            leafletMarkers.value[updatedMarker.id].remove();
            addMarkerToMap(updatedMarker);

            map.value.closePopup();
        } catch (err) {
            console.error('Failed to update marker', err);
            alert('Failed to update marker');
        }
    });
}

async function deleteMarker(id) {
    if (!confirm('Are you sure you want to delete this marker?')) return;
    try {
        await axios.delete(`/map/marker/${id}`);
        markers.value = markers.value.filter(m => m.id !== id);
        leafletMarkers.value[id].remove();
        delete leafletMarkers.value[id];
    } catch (err) {
        console.error('Failed to delete marker', err);
    }
}
</script>

<template>
    <div id="map" class="w-full h-full"></div>
</template>