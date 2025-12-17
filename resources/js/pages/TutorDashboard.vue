<template>
  <main class="w-full">

    <!-- HERO / DASHBOARD HEADER -->
    <section class="text-black rounded-b-3xl p-6 shadow-lg w-full bg-cover bg-center
               bg-[url('https://image2url.com/images/1765773528174-f33a81da-0108-401a-a459-3371ca54cae8.png')]
               md:bg-[url('https://image2url.com/images/1765773881397-02e1b232-0a58-4304-b4ce-36e93cf9caee.png')]">
    
        <div class="max-w-7xl mx-auto px-4 py-14 md:py-20 text-white relative z-10">
            <h1 class="text-3xl md:text-5xl font-bold">Teacher Dashboard</h1>
            <p class="mt-3 text-lg md:text-xl opacity-90">
                Welcome back, <span class="font-semibold">{{ user?.name || 'To Your Profile!' }}</span>
            </p>
        </div>
    </section>

    <!-- DASHBOARD CARDS -->
    <section class="max-w-7xl mx-auto px-4 mt-6 md:-mt-6 pb-20">

        <!-- DESKTOP -->
        <div class="hidden md:grid grid-cols-2 gap-8">

            <!-- Upload Photo -->
            <router-link to="/tutor/profile/photo"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=60001&format=png" alt="Upload Photo" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Upload Photo</h3>
            </router-link>
            
            <!-- Verify Mobile -->
            <router-link to="/tutor/profile/phone"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=9ievMrCpvQ2c&format=png" alt="Verify Mobile" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Verify Mobile</h3>
            </router-link>
            
            <!-- Introduction Video -->
            <router-link to="/tutor/profile/video"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=48&id=111366&format=png" alt="Introduction Video" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Introduction Video</h3>
            </router-link>
            
            <!-- My Profile -->
            <router-link to="/tutor/profile/view"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=NjOjDSZRU0Ma&format=png" alt="My Profile" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">My Profile</h3>
            </router-link>

            <!-- Personal Details -->
            <router-link to="/tutor/profile/basic-details"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=48&id=88096&format=png" alt="Personal Details" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Personal Details</h3>
            </router-link>

            <!-- Subjects -->
            <router-link to="/tutor/profile/subjects"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=160&id=LQs1yRPJKdeX&format=png" alt="Subjects" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Subjects</h3>
            </router-link>

            <!-- Address -->
            <router-link to="/tutor/profile/address"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=61845&format=png" alt="Address" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Address</h3>
            </router-link>

            <!-- Education -->
            <router-link to="/tutor/profile/education"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=128&id=tP3OXTLBOXKl&format=png" alt="Education" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Education</h3>
            </router-link>

            <!-- Experience -->
            <router-link to="/tutor/profile/experience"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=58171&format=png" alt="Experience" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Experience</h3>
            </router-link>

            <!-- Teaching Details -->
            <router-link to="/tutor/profile/teaching-details"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=vKo2l3w3TPSJ&format=png" alt="Teaching Details" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Teaching Details</h3>
            </router-link>

            <!-- Profile Description -->
            <router-link to="/tutor/profile/description"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=HAwVrqHslq0F&format=png" alt="Profile Description" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Profile Description</h3>
            </router-link>

            <!-- Show Contact Setting -->
            <router-link to="/tutor/profile/contact-settings"
                class="flex items-center gap-6 bg-white rounded-2xl p-6 shadow-xl hover:scale-[1.03] transition">
                <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=39600&format=png" alt="Contact Settings" />
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Show Contact Setting</h3>
            </router-link>

        </div>

        <!-- MOBILE -->
        <div class="md:hidden space-y-6">

            <router-link to="/tutor/profile/photo" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=60001&format=png" alt="Upload Photo" />
                </div>
                <p class="text-lg font-semibold">Upload Photo</p>
            </router-link>
            
            <router-link to="/tutor/profile/phone" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=9ievMrCpvQ2c&format=png" alt="Verify Mobile" />
                </div>
                <p class="text-lg font-semibold">Verify Mobile</p>
            </router-link>
            
            <router-link to="/tutor/profile/video" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=48&id=111366&format=png" alt="Introduction Video" />
                </div>
                <p class="text-lg font-semibold">Introduction Video</p>
            </router-link>
            
            <router-link to="/tutor/profile/view" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=NjOjDSZRU0Ma&format=png" alt="My Profile" />
                </div>
                <p class="text-lg font-semibold">My Profile</p>
            </router-link>

            <!-- Personal Details -->
            <router-link to="/tutor/profile/basic-details" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=48&id=88096&format=png" alt="Personal Details" />
                </div>
                <p class="text-lg font-semibold">Personal Details</p>
            </router-link>
            
            <!-- Subjects -->
            <router-link to="/tutor/profile/subjects" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=160&id=LQs1yRPJKdeX&format=png" alt="Subjects" />
                </div>
                <p class="text-lg font-semibold">Subjects</p>
            </router-link>
            
            <!-- Address -->
            <router-link to="/tutor/profile/address" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=60&id=61845&format=png" alt="Address" />
                </div>
                <p class="text-lg font-semibold">Address</p>
            </router-link>
            
            <!-- Education -->
            <router-link to="/tutor/profile/education" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=128&id=tP3OXTLBOXKl&format=png" alt="Education" />
                </div>
                <p class="text-lg font-semibold">Education</p>
            </router-link>
            
            <!-- Experience -->
            <router-link to="/tutor/profile/experience" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=58171&format=png" alt="Experience" />
                </div>
                <p class="text-lg font-semibold">Experience</p>
            </router-link>
            
            <!-- Teaching Details -->
            <router-link to="/tutor/profile/teaching-details" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=vKo2l3w3TPSJ&format=png" alt="Teaching Details" />
                </div>
                <p class="text-lg font-semibold">Teaching Details</p>
            </router-link>
            
            <!-- Profile Description -->
            <router-link to="/tutor/profile/description" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=HAwVrqHslq0F&format=png" alt="Profile Description" />
                </div>
                <p class="text-lg font-semibold">Profile Description</p>
            </router-link>
            
            <!-- Show Contact Setting -->
            <router-link to="/tutor/profile/contact-settings" class="flex items-center gap-4 bg-white rounded-2xl p-4 shadow-lg">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center">
                    <img src="https://img.icons8.com/?size=100&id=39600&format=png" alt="Contact Settings" />
                </div>
                <p class="text-lg font-semibold">Show Contact Setting</p>
            </router-link>

        </div>

    </section>
  </main>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useUserStore } from '../store';

export default {
  name: 'TutorDashboard',
  setup() {
    const userStore = useUserStore();
    const user = computed(() => userStore.user);

    onMounted(async () => {
      if (!userStore.user) {
        await userStore.fetchUser();
      }
    });

    return { user };
  }
}
</script>

<style scoped>
</style>
