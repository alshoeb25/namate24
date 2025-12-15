import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../store';
import Home from '../pages/Home.vue';
import Search from '../pages/Search.vue';
import TutorProfile from '../pages/TutorProfile.vue';
import Login from '../pages/Login.vue';
import Register from '../pages/Register.vue';
import VerifyEmail from '../pages/VerifyEmail.vue';
import Conversations from '../pages/Conversations.vue';
import ConversationMessages from '../pages/ConversationMessages.vue';
import BookingForm from '../pages/BookingForm.vue';
import BookingCalendar from '../pages/BookingCalendar.vue';
import TutorDashboard from '../pages/TutorDashboard.vue';
import TutorProfileLayout from '../pages/TutorProfileLayout.vue';

// Tutor Profile Components
import PersonalDetails from '../components/tutor/profile/PersonalDetails.vue';
import Photo from '../components/tutor/profile/Photo.vue';
import Video from '../components/tutor/profile/Video.vue';
import Subjects from '../components/tutor/profile/Subjects.vue';
import Address from '../components/tutor/profile/Address.vue';
import Education from '../components/tutor/profile/Education.vue';
import Experience from '../components/tutor/profile/Experience.vue';
import TeachingDetails from '../components/tutor/profile/TeachingDetails.vue';
import Description from '../components/tutor/profile/Description.vue';
import PhoneOtp from '../components/tutor/profile/PhoneOtp.vue';
import Courses from '../components/tutor/profile/Courses.vue';
import Settings from '../components/tutor/profile/Settings.vue';

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/search', name: 'search', component: Search },
  { path: '/tutor/:id', name: 'tutor.show', component: TutorProfile, props: true },
  
  // Tutor Profile Routes
  {
    path: '/tutor/profile',
    component: TutorProfileLayout,
    children: [
      { path: '', name: 'tutor.profile.dashboard', component: TutorDashboard },
      { path: 'personal-details', name: 'tutor.profile.personal-details', component: PersonalDetails },
      { path: 'photo', name: 'tutor.profile.photo', component: Photo },
      { path: 'video', name: 'tutor.profile.video', component: Video },
      { path: 'subjects', name: 'tutor.profile.subjects', component: Subjects },
      { path: 'address', name: 'tutor.profile.address', component: Address },
      { path: 'education', name: 'tutor.profile.education', component: Education },
      { path: 'experience', name: 'tutor.profile.experience', component: Experience },
      { path: 'teaching-details', name: 'tutor.profile.teaching-details', component: TeachingDetails },
      { path: 'description', name: 'tutor.profile.description', component: Description },
      { path: 'phone', name: 'tutor.profile.phone', component: PhoneOtp },
      { path: 'courses', name: 'tutor.profile.courses', component: Courses },
      { path: 'settings', name: 'tutor.profile.settings', component: Settings },
    ]
  },
  
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/verify-email', name: 'verify-email', component: VerifyEmail },

  // messaging
  { path: '/conversations', name: 'conversations.index', component: Conversations },
  { path: '/conversations/:id', name: 'conversations.show', component: ConversationMessages },

  // bookings
  // { path: '/bookings/new', name: 'bookings.create', component: BookingForm },
  // { path: '/bookings', name: 'bookings.calendar', component: BookingCalendar },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard: hydrate user on refresh and redirect tutors to their dashboard
router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();

  // If we have a token but no user loaded (refresh), fetch user
  if (userStore.token && !userStore.user) {
    try {
      await userStore.fetchUser();
    } catch (e) {
      // token invalid — clear and send to login
      userStore.logout();
      if (to.path !== '/login') {
        return next('/login');
      }
    }
  }

  const user = userStore.user;

  // Tutors visiting home should go to their profile dashboard
  if (user && user.role === 'tutor' && to.path === '/') {
    return next('/tutor/profile');
  }

  return next();
});

export default router;