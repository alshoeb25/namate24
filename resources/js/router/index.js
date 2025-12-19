import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../store';
import Home from '../pages/Home.vue';
import Search from '../pages/Search.vue';
import SearchResults from '../pages/SearchResults.vue';
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

// Student Components
import StudentLayout from '../components/layout/StudentLayout.vue';
import StudentDashboard from '../pages/StudentDashboard.vue';
import RequestTutor from '../pages/RequestTutor.vue';
import RequirementsList from '../pages/RequirementsList.vue';
import StudentReviews from '../pages/StudentReviews.vue';
import StudentWallet from '../pages/StudentWallet.vue';
import StudentSettings from '../pages/StudentSettings.vue';

// Profile Management
import ProfileManagement from '../pages/ProfileManagement.vue';

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/search', name: 'search', component: SearchResults },
  { path: '/tutors', name: 'tutors', component: SearchResults },
  { path: '/tutor-jobs', name: 'tutor-jobs', component: SearchResults },
  
  // Dynamic SEO-friendly routes: /{subject}-tutors-in-{city}
  { path: '/:subject-tutors-in-:city', name: 'tutors.subject.city', component: SearchResults },
  { path: '/:subject-tutors', name: 'tutors.subject', component: SearchResults },
  { path: '/tutors-in-:city', name: 'tutors.city', component: SearchResults },
  
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
      { path: 'view', name: 'tutor.profile.view', component: TutorProfile },
    ]
  },
  
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/verify-email', name: 'verify-email', component: VerifyEmail },

  // Tutor Wallet Routes
  { path: '/tutor/wallet', name: 'tutor.wallet', component: () => import('../pages/TutorWallet.vue') },
  { path: '/tutor/wallet/payment-history', name: 'tutor.payment-history', component: () => import('../pages/PaymentHistory.vue') },
  { path: '/tutor/wallet/referrals', name: 'tutor.referrals', component: () => import('../pages/ReferralPage.vue') },

  // Profile Management
  { path: '/profile', name: 'profile', component: ProfileManagement },

  // Student Routes
  {
    path: '/student',
    component: StudentLayout,
    children: [
      { path: 'dashboard', name: 'student.dashboard', component: StudentDashboard },
      { path: 'request-tutor', name: 'student.request-tutor', component: RequestTutor },
      { path: 'requirements', name: 'student.requirements', component: RequirementsList },
      { path: 'requirements/:id/edit', name: 'student.requirement-edit', component: RequestTutor },
      { path: 'wallet', name: 'student.wallet', component: () => import('../pages/StudentWallet.vue') },
      { path: 'wallet/payment-history', name: 'student.payment-history', component: () => import('../pages/PaymentHistory.vue') },
      { path: 'wallet/referrals', name: 'student.referrals', component: () => import('../pages/ReferralPage.vue') },
      { path: 'reviews', name: 'student.reviews', component: StudentReviews },
      { path: 'settings', name: 'student.settings', component: () => import('../pages/StudentSettings.vue') },
    ]
  },

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

// Navigation guard: hydrate user on refresh and handle role-based redirects
router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();

  // If we have a token but no user loaded (refresh), fetch user
  if (userStore.token && !userStore.user) {
    try {
      await userStore.fetchUser();
    } catch (e) {
      // token invalid — clear and send to login
      userStore.logout();
      if (to.path !== '/login' && to.path !== '/register') {
        return next('/login');
      }
    }
  }

  const user = userStore.user;

  // After login, redirect based on available roles
  if (user && to.path === '/') {
    // If user has tutor role, go to tutor dashboard
    if (user.tutor) {
      return next('/tutor/profile');
    }
    // If user has student role, go to student dashboard
    if (user.student) {
      return next('/student/dashboard');
    }
  }

  // Protect tutor routes - require tutor record
  if (to.path.startsWith('/tutor/') && user && !user.tutor) {
    return next('/');
  }

  // Protect student routes - require student record
  if (to.path.startsWith('/student/') && user && !user.student) {
    return next('/');
  }

  return next();
});

export default router;