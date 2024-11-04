<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="PuremedPharmacy2024" />

    <!-- Main Container with Background Styling -->
    <div class="relative flex items-center justify-center h-screen bg-dots-darker bg-center" style="background-color: #FFF9E3;">
        
        <!-- Login and Register Links in the Upper Right Corner -->
        <div class="absolute top-6 right-6 flex space-x-4">
            <template v-if="!$page.props.auth.user">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="font-semibold text-gray-800 hover:text-green bg-[#B5C99A] hover:bg-[#F0E8E2] focus:outline focus:outline-2 focus:rounded-sm focus:outline-green-100 px-4 py-2 rounded-lg shadow-md transition-transform transform hover:scale-105 glowing-effect"
                >
                    Log in
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="font-semibold text-gray-800 hover:text-green bg-[#B5C99A] hover:bg-[#F0E8E2] focus:outline focus:outline-2 focus:rounded-sm focus:outline-green-100 px-4 py-2 rounded-lg shadow-md transition-transform transform hover:scale-105 glowing-effect"
                >
                    Register
                </Link>
            </template>
        </div>

        <!-- Centered Floating Container with Welcome Message and Social Media Icons -->
        <div class="flex flex-col items-center justify-center bg-[#FFF9E3] rounded-lg shadow-lg p-8 max-w-2xl floating-container glowing-effect">
            <div class="text-center text-2xl font-semibold mb-4">
                Welcome to Puremed Pharmacy! 💊✨
            </div>
            <div class="text-sm text-gray-600 mb-4 text-justify">
                With the pouring grace of God, Puremed Pharmacy is finally open and ready to serve you! We offer a wide range of prescription drugs, over-the-counter medications, and other healthcare needs. You may also send us a message if you have questions regarding your medications.
            </div>
            <div class="text-center text-lg font-medium text-gray-700 mb-4">
                📍 NAVARRO STREET, SURIGAO CITY (near Crispy King beside Bridgestone)
            </div>
            <div class="text-center text-lg font-medium text-gray-700 mb-4">
                🕰️ Operating Hours: 8AM-10PM DAILY
            </div>
            <div class="text-center text-xl font-semibold text-red-500 mb-6">
                THANK YOU! ❤️
            </div>

            <!-- Social Media Icons with 3D Glow Effect and Floating Effect -->
            <div class="flex space-x-4">
                <a href="https://www.facebook.com/profile.php?id=61556461702879&mibextid=ZbWKwL" target="_blank" class="bg-blue-700 text-white rounded-full p-4 hover:bg-blue-800 transition-transform transform hover:scale-105 glowing-effect">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com" target="_blank" class="bg-blue-400 text-white rounded-full p-4 hover:bg-blue-500 transition-transform transform hover:scale-105 glowing-effect">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://google.com" target="_blank" class="bg-red-600 text-white rounded-full p-4 hover:bg-red-700 transition-transform transform hover:scale-105 glowing-effect">
                    <i class="fab fa-google"></i>
                </a>
                
                <a href="https://youtube.com" target="_blank" class="bg-red-700 text-white rounded-full p-4 hover:bg-red-800 transition-transform transform hover:scale-105 glowing-effect">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>

    </div>
</template>

<style>
@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
.floating-container {
    animation: float 4s ease-in-out infinite;
    padding: 2rem;
    background-color: #FFF9E3;
    border-radius: 1rem;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.glowing-effect {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 20px rgba(0, 255, 0, 0.2);
}
.floating-container:hover, .glowing-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 255, 0, 0.3);
}
</style>
