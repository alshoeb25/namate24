<template>
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">

        <!-- Desktop Menu -->
        <nav class="hidden md:flex items-center gap-8">
          <router-link 
            to="/tutor/profile"
            class="nav-item"
            :class="{ active: isActive('/tutor/profile') }"
          >
            Dashboard
          </router-link>

          <router-link
            to="/tutor/profile/my-learners"
            class="nav-item"
            :class="{ active: isActive('/tutor/profile/my-learners') }"
          >
            My Learners
          </router-link>

          <!-- Edit Profile dropdown in nav -->
          <div class="relative" ref="editRef">
            <button @click="toggleEdit" class="nav-item flex items-center gap-2">
              Edit Profile
              <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.12 1L10.53 13l-5.3-5.77a.75.75 0 01.0-1.02z" clip-rule="evenodd"/></svg>
            </button>

            <div v-if="editOpen" class="absolute right-4 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
              <router-link to="/tutor/profile/personal-details" class="dropdown-item">Personal Details</router-link>
              <router-link to="/tutor/profile/photo" class="dropdown-item">Photo</router-link>
              <router-link to="/tutor/profile/video" class="dropdown-item">Introduction Video</router-link>
              <router-link to="/tutor/profile/subjects" class="dropdown-item">Subjects</router-link>
              <router-link to="/tutor/profile/address" class="dropdown-item">Address</router-link>
              <router-link to="/tutor/profile/education" class="dropdown-item">Education</router-link>
              <router-link to="/tutor/profile/experience" class="dropdown-item">Experience</router-link>
              <router-link to="/tutor/profile/teaching-details" class="dropdown-item">Teaching Details</router-link>
              <router-link to="/tutor/profile/description" class="dropdown-item">Profile Description</router-link>
              <router-link to="/tutor/profile/phone" class="dropdown-item">Phone</router-link>
              <router-link to="/tutor/profile/courses" class="dropdown-item">Courses</router-link>
              <router-link to="/tutor/profile/settings" class="dropdown-item">Settings</router-link>
              <router-link to="/tutor/documents" class="dropdown-item">Documents</router-link>
            </div>
          </div>

          <router-link 
            to="/wallet"
            class="nav-item"
            :class="{ active: isActive('/wallet') }"
          >
            Wallet
          </router-link>
        </nav>

        
      </div>

    </div>
  </header>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";

export default {
  name: "TutorHeader",

  setup() {
    const router = useRouter();
    const user = ref(null);
    const dropdownOpen = ref(false);
    const mobileMenuOpen = ref(false);
    const mobileEditOpen = ref(false);
    const dropdownRef = ref(null);
    const editRef = ref(null);
    const editOpen = ref(false);
    const isMobile = ref(false);

    const checkMobile = () => {
      isMobile.value = window.innerWidth < 768;
    };

    onMounted(() => {
      if (window.NAMATE24?.user) {
        user.value = window.NAMATE24.user;
      }
      checkMobile();
      window.addEventListener("resize", checkMobile);
      document.addEventListener("click", handleClickOutside);
    });

    onBeforeUnmount(() => {
      window.removeEventListener("resize", checkMobile);
      document.removeEventListener("click", handleClickOutside);
    });

    const handleClickOutside = (event) => {
      if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdownOpen.value = false;
      }
      if (editRef.value && !editRef.value.contains(event.target)) {
        editOpen.value = false;
      }
    };

    const isActive = (path) => router.currentRoute.value.path.startsWith(path);

    const toggleDropdown = () => {
      dropdownOpen.value = !dropdownOpen.value;
    };

    const toggleEdit = () => {
      editOpen.value = !editOpen.value;
    };

    const logout = async () => {
      try {
        await axios.post("/api/logout");
      } catch (e) {}

      localStorage.removeItem("token");
      router.push("/login");
    };

    return {
      user,
      dropdownOpen,
      mobileMenuOpen,
      mobileEditOpen,
      dropdownRef,
      editRef,
      editOpen,
      isMobile,
      isActive,
      toggleDropdown,
      toggleEdit,
      logout,
    };
  },
};
</script>

<style scoped>
/* Desktop nav item */
.nav-item {
  color: #555;
  font-weight: 500;
  transition: 0.3s;
}

.nav-item:hover {
  color: #6366f1;
}

.nav-item.active {
  color: #6366f1;
  border-bottom: 2px solid #6366f1;
  padding-bottom: 3px;
}

/* Dropdown items */
.dropdown-item {
  display: block;
  padding: 8px 16px;
  font-size: 14px;
  color: #555;
  transition: 0.3s;
}

.dropdown-item:hover {
  background: #eef2ff;
  color: #6366f1;
}

/* Mobile menu items */
.mobile-item {
  padding: 10px 16px;
  display: block;
  color: #555;
  transition: 0.3s;
}

.mobile-item:hover {
  background: #eef2ff;
  color: #6366f1;
}

.mobile-item.mobile-active {
  background: #eef2ff;
  color: #6366f1;
  font-weight: 600;
}
</style>