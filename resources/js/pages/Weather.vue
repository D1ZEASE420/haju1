<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const city = ref('')

const search = () => {
    if (!city.value) return
    router.get('/weather/search', { city: city.value }, { replace: true })
}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white p-6">

    <h1 class="text-3xl font-bold mb-6">🌤 Weather</h1>

    <div class="flex gap-2 mb-4">
      <input 
        v-model="city" 
        placeholder="Enter city" 
        class="px-4 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <button 
        @click="search" 
        class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600"
      >
        Search
      </button>
    </div>

    <div v-if="$page.props.error" class="text-red-500 mb-4">
      {{ $page.props.error }}
    </div>

    <div v-if="$page.props.weather?.main" class="mt-4 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg text-center w-full max-w-md">

      <h2 class="text-2xl font-semibold mb-2">
        {{ $page.props.weather?.name }}
      </h2>

      <p class="text-4xl font-bold mb-2">
        {{ Math.round($page.props.weather?.main?.temp) }}°C
      </p>

      <p class="capitalize mb-4">
        {{ $page.props.weather?.weather?.[0]?.description }}
      </p>

      <img 
        :src="`http://openweathermap.org/img/wn/${$page.props.weather?.weather?.[0]?.icon}@2x.png`" 
        class="mx-auto"
      />

      <div class="flex justify-center gap-4 mt-4 text-gray-600 dark:text-gray-300">
        <p>Humidity: {{ $page.props.weather?.main?.humidity }}%</p>
        <p>Wind: {{ $page.props.weather?.wind?.speed }} m/s</p>
      </div>

    </div>
  </div>
</template>