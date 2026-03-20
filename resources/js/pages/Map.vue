<script setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import axios from 'axios';

const map = ref(null);
const markers = ref([]);
const leafletMarkers = ref({});

onMounted(async () => {
    map.value = L.map('map', {
        zoomControl: false,
    }).setView([59.437, 24.7535], 12);

    // Add zoom control to bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map.value);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map.value);

    const res = await axios.get('/map/markers');
    markers.value = res.data;
    markers.value.forEach(marker => addMarkerToMap(marker));

    map.value.on('click', openAddMarkerPopup);
});

// Custom pin icon
function makeIcon(color = '#3b82f6') {
    return L.divIcon({
        html: `<div style="
            width:28px;height:28px;border-radius:50% 50% 50% 0;
            background:${color};border:2px solid white;
            box-shadow:0 2px 8px rgba(0,0,0,0.4);
            transform:rotate(-45deg);
        "></div>`,
        className: '',
        iconSize: [28, 28],
        iconAnchor: [14, 28],
        popupAnchor: [0, -30],
    });
}

const popupStyles = `
    font-family:'DM Sans',sans-serif;
    min-width:200px;
`;

function addMarkerToMap(marker) {
    const m = L.marker([marker.latitude, marker.longitude], { icon: makeIcon() }).addTo(map.value);
    leafletMarkers.value[marker.id] = m;

    m.bindPopup(`
        <div style="${popupStyles}">
            <div style="font-weight:600;font-size:14px;margin-bottom:4px;color:#f1f5f9">${marker.name}</div>
            ${marker.description ? `<div style="font-size:12px;color:#94a3b8;margin-bottom:10px">${marker.description}</div>` : '<div style="margin-bottom:10px"></div>'}
            <div style="display:flex;gap:6px">
                <button class="edit-marker-btn" data-id="${marker.id}" style="flex:1;padding:5px 8px;background:rgba(59,130,246,0.2);color:#93c5fd;border:1px solid rgba(59,130,246,0.3);border-radius:6px;font-size:11px;cursor:pointer;font-family:inherit">Edit</button>
                <button class="delete-marker-btn" data-id="${marker.id}" style="flex:1;padding:5px 8px;background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.25);border-radius:6px;font-size:11px;cursor:pointer;font-family:inherit">Delete</button>
            </div>
        </div>
    `, {
        className: 'custom-popup',
        maxWidth: 260,
    });

    m.on('popupopen', () => {
        document.querySelector(`.edit-marker-btn[data-id="${marker.id}"]`)
            ?.addEventListener('click', () => openEditMarkerPopup(marker));
        document.querySelector(`.delete-marker-btn[data-id="${marker.id}"]`)
            ?.addEventListener('click', () => deleteMarker(marker.id));
    });
}

function inputStyle(val = '') {
    return `width:100%;padding:7px 10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:8px;color:#f1f5f9;font-size:13px;font-family:'DM Sans',sans-serif;box-sizing:border-box;outline:none;value="${val}"`;
}

function btnStyle(bg, color, border) {
    return `padding:8px 14px;background:${bg};color:${color};border:1px solid ${border};border-radius:8px;font-size:12px;cursor:pointer;font-family:inherit;font-weight:500`;
}

function openAddMarkerPopup(e) {
    const wrap = document.createElement('div');
    wrap.style.cssText = popupStyles;
    wrap.innerHTML = `
        <div style="font-weight:600;font-size:13px;color:#f1f5f9;margin-bottom:10px">Add Marker</div>
        <input id="pm-name" placeholder="Name" style="${inputStyle()}" />
        <textarea id="pm-desc" placeholder="Description (optional)" rows="2" style="${inputStyle()};margin-top:6px;resize:none"></textarea>
        <div style="display:flex;gap:6px;margin-top:10px">
            <button id="pm-save" style="${btnStyle('rgba(59,130,246,0.85)','white','transparent')}">Add</button>
            <button id="pm-cancel" style="${btnStyle('rgba(255,255,255,0.06)','#94a3b8','rgba(255,255,255,0.1)')}">Cancel</button>
        </div>
    `;

    const popup = L.popup({ className: 'custom-popup', maxWidth: 280 })
        .setLatLng(e.latlng).setContent(wrap).openOn(map.value);

    wrap.querySelector('#pm-cancel').addEventListener('click', () => map.value.closePopup());
    wrap.querySelector('#pm-save').addEventListener('click', async () => {
        const name = wrap.querySelector('#pm-name').value.trim();
        const description = wrap.querySelector('#pm-desc').value.trim();
        if (!name) { wrap.querySelector('#pm-name').style.borderColor = 'rgba(239,68,68,0.6)'; return; }
        try {
            const res = await axios.post('/map/marker', { name, description, latitude: e.latlng.lat, longitude: e.latlng.lng });
            markers.value.push(res.data);
            addMarkerToMap(res.data);
            map.value.closePopup();
        } catch { alert('Failed to save marker'); }
    });
}

function openEditMarkerPopup(marker) {
    const wrap = document.createElement('div');
    wrap.style.cssText = popupStyles;
    wrap.innerHTML = `
        <div style="font-weight:600;font-size:13px;color:#f1f5f9;margin-bottom:10px">Edit Marker</div>
        <input id="em-name" value="${marker.name}" style="${inputStyle(marker.name)}" />
        <textarea id="em-desc" rows="2" style="${inputStyle()};margin-top:6px;resize:none">${marker.description ?? ''}</textarea>
        <div style="display:flex;gap:6px;margin-top:10px">
            <button id="em-save" style="${btnStyle('rgba(34,197,94,0.8)','white','transparent')}">Save</button>
            <button id="em-cancel" style="${btnStyle('rgba(255,255,255,0.06)','#94a3b8','rgba(255,255,255,0.1)')}">Cancel</button>
        </div>
    `;

    const popup = L.popup({ className: 'custom-popup', maxWidth: 280 })
        .setLatLng([marker.latitude, marker.longitude]).setContent(wrap).openOn(map.value);

    wrap.querySelector('#em-cancel').addEventListener('click', () => map.value.closePopup());
    wrap.querySelector('#em-save').addEventListener('click', async () => {
        const newName = wrap.querySelector('#em-name').value.trim();
        const newDesc = wrap.querySelector('#em-desc').value.trim();
        if (!newName) { wrap.querySelector('#em-name').style.borderColor = 'rgba(239,68,68,0.6)'; return; }
        try {
            const res = await axios.patch(`/map/marker/${marker.id}`, { name: newName, description: newDesc });
            const idx = markers.value.findIndex(m => m.id === res.data.id);
            if (idx !== -1) markers.value[idx] = res.data;
            leafletMarkers.value[res.data.id].remove();
            addMarkerToMap(res.data);
            map.value.closePopup();
        } catch { alert('Failed to update marker'); }
    });
}

async function deleteMarker(id) {
    if (!confirm('Delete this marker?')) return;
    try {
        await axios.delete(`/map/marker/${id}`);
        markers.value = markers.value.filter(m => m.id !== id);
        leafletMarkers.value[id].remove();
        delete leafletMarkers.value[id];
    } catch { alert('Failed to delete marker'); }
}
</script>

<template>
    <div id="map" class="w-full h-full min-h-[400px]"></div>
</template>

<style>
/* Override Leaflet popup styling */
.custom-popup .leaflet-popup-content-wrapper {
    background: #0f1117;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    padding: 0;
    color: #f1f5f9;
}
.custom-popup .leaflet-popup-content {
    margin: 14px 16px;
    font-size: 13px;
}
.custom-popup .leaflet-popup-tip-container {
    display: none;
}
.custom-popup .leaflet-popup-close-button {
    color: #64748b !important;
    font-size: 18px !important;
    top: 6px !important;
    right: 8px !important;
}
.custom-popup .leaflet-popup-close-button:hover {
    color: #f1f5f9 !important;
}
.leaflet-control-zoom {
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 10px !important;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}
.leaflet-control-zoom a {
    background: #0f1117 !important;
    color: #94a3b8 !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}
.leaflet-control-zoom a:hover {
    background: rgba(59,130,246,0.2) !important;
    color: #93c5fd !important;
}
</style>
