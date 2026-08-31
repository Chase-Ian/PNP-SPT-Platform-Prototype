
<script setup>
defineProps({ certificate: Object });
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div v-if="certificate" class="bg-white rounded-xl border p-6">
                <h1 class="font-semibold text-lg text-green-700">✅ Certificate Verified</h1>
                <div class="mt-4 text-sm space-y-1">
                    <p><span class="text-gray-500">Holder:</span> {{ certificate.holder_name }}</p>
                    <p><span class="text-gray-500">Course:</span> {{ certificate.title }}</p>
                    <p><span class="text-gray-500">Issued:</span> {{ certificate.issued_at }}</p>
                    <p><span class="text-gray-500">Serial ID:</span> {{ certificate.serial_id }}</p>
                </div>
            </div>
            <div v-else class="bg-white rounded-xl border p-6 text-red-600 font-medium">
                ❌ No certificate found with that serial number.
            </div>
        </div>
    </div>
</template>